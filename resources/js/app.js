// resources/js/app.js
import './bootstrap';
import './echo';

const MOVEMENT_INACTIVITY_MINUTES = 30;
const NOTIFICATION_ROLES = new Set([
    'CUSTODIOS',
    'AUXILIAR MONITORISTA',
    'ADMIN',
    'ADMINISTRADOR',
    'JEFE',
]);
const notifiedMovementStarts = new Set();
const lastRealtimePositionByUser = new Map();
const globalGeofenceAssignments = new Map();
const globalGeofenceStates = new Map();
let globalGeofencesReady = false;
const receivedRealtimeToastKeys = new Set();
const NOTIFICATION_SOUND_STORAGE_KEY = 'erp-notification-sounds-enabled';
let notificationAudioContext = null;
let notificationSoundsEnabled = readNotificationSoundPreference();

function canReceiveCustodianNotifications() {
    return NOTIFICATION_ROLES.has(window.userRoleUpper || '');
}

function readNotificationSoundPreference() {
    try {
        return window.localStorage.getItem(NOTIFICATION_SOUND_STORAGE_KEY) === 'true';
    } catch (error) {
        return false;
    }
}

function saveNotificationSoundPreference() {
    try {
        window.localStorage.setItem(
            NOTIFICATION_SOUND_STORAGE_KEY,
            notificationSoundsEnabled ? 'true' : 'false',
        );
    } catch (error) {
        // El sonido continúa funcionando aunque el navegador bloquee localStorage.
    }
}

function getNotificationAudioContext() {
    if (!notificationAudioContext) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return null;
        notificationAudioContext = new AudioContextClass();
    }

    return notificationAudioContext;
}

async function unlockNotificationAudio() {
    if (!notificationSoundsEnabled) return;

    const context = getNotificationAudioContext();
    if (context?.state === 'suspended') {
        try {
            await context.resume();
        } catch (error) {
            // El navegador permitirá reintentarlo con la siguiente interacción.
        }
    }
}

function emitNotificationTone(context, icon) {
    const frequencies = {
        success: [659.25, 880],
        warning: [523.25, 659.25],
        error: [392, 329.63],
        info: [587.33, 783.99],
        question: [523.25, 698.46],
        panic: [880, 659.25, 880, 659.25],
    };
    const notes = frequencies[icon] || frequencies.info;
    const startTime = context.currentTime;

    notes.forEach((frequency, index) => {
        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const noteStart = startTime + (index * 0.12);
        const noteEnd = noteStart + 0.16;

        oscillator.type = icon === 'panic' ? 'square' : 'sine';
        oscillator.frequency.setValueAtTime(frequency, noteStart);
        gain.gain.setValueAtTime(0.0001, noteStart);
        gain.gain.exponentialRampToValueAtTime(0.16, noteStart + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, noteEnd);
        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.start(noteStart);
        oscillator.stop(noteEnd);
    });
}

async function playNotificationSound(icon = 'info') {
    if (!notificationSoundsEnabled) return;

    const context = getNotificationAudioContext();
    if (!context) return;

    if (context.state === 'suspended') {
        try {
            await context.resume();
        } catch (error) {
            return;
        }
    }

    if (context.state === 'running') {
        emitNotificationTone(context, icon);
    }
}

function updateNotificationSoundButton() {
    const button = document.getElementById('notification-sound-toggle');
    const icon = document.getElementById('notification-sound-icon');
    if (!button || !icon) return;

    button.setAttribute('aria-pressed', notificationSoundsEnabled ? 'true' : 'false');
    button.title = notificationSoundsEnabled
        ? 'Sonidos de notificación activados'
        : 'Sonidos de notificación silenciados';
    button.classList.toggle('bg-indigo-600', notificationSoundsEnabled);
    button.classList.toggle('hover:bg-indigo-700', notificationSoundsEnabled);
    button.classList.toggle('bg-gray-500', !notificationSoundsEnabled);
    button.classList.toggle('hover:bg-gray-600', !notificationSoundsEnabled);
    icon.className = notificationSoundsEnabled ? 'ti ti-volume' : 'ti ti-volume-off';
}

async function toggleNotificationSounds() {
    notificationSoundsEnabled = !notificationSoundsEnabled;
    saveNotificationSoundPreference();
    updateNotificationSoundButton();

    if (notificationSoundsEnabled) {
        await unlockNotificationAudio();
        await playNotificationSound('success');
    }
}

function showPrivateRealtimeToast(notification) {
    if (!notification || typeof window.Swal === 'undefined') return;

    const notificationKey = notification.key || null;
    if (notificationKey && receivedRealtimeToastKeys.has(notificationKey)) return;
    if (notificationKey) receivedRealtimeToastKeys.add(notificationKey);

    const allowedIcons = new Set(['success', 'error', 'warning', 'info', 'question']);
    const icon = allowedIcons.has(notification.icon) ? notification.icon : 'info';

    const isUrgent = notification.urgent === true;
    const sound = notification.sound === 'panic' ? 'panic' : icon;

    playNotificationSound(sound);
    window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title: notification.title || 'Nueva notificación',
        text: notification.text || '',
        showConfirmButton: false,
        timer: isUrgent ? 10000 : 6000,
        timerProgressBar: true,
        didOpen: toast => {
            if (isUrgent) {
                toast.style.border = '2px solid #dc2626';
                toast.style.borderLeftWidth = '8px';
                toast.style.background = '#fff1f2';
                toast.style.color = '#7f1d1d';
                toast.style.boxShadow = '0 12px 30px rgba(185, 28, 28, 0.35)';

                toast.animate?.([
                    { transform: 'translateX(0)' },
                    { transform: 'translateX(-7px)' },
                    { transform: 'translateX(7px)' },
                    { transform: 'translateX(0)' },
                ], {
                    duration: 320,
                    iterations: 2,
                });
            }

            if (!notification.url) return;
            toast.style.cursor = 'pointer';
            toast.addEventListener('click', () => {
                window.location.href = notification.url;
            });
        },
    });
}

function showPanicAlert(event) {
    const alert = event?.alert;
    if (!alert?.id) return;

    showPrivateRealtimeToast({
        icon: 'error',
        sound: 'panic',
        urgent: true,
        title: '🚨 ALERTA DE PÁNICO',
        text: `${alert.user_name || 'Un usuario'} activó el botón de pánico. Haz clic para abrir el mapa.`,
        url: '/mapa',
        key: `panic:${alert.id}`,
    });
}

function geofenceDistanceMeters(lat1, lng1, lat2, lng2) {
    const earthRadius = 6371000;
    const latitude1 = lat1 * Math.PI / 180;
    const latitude2 = lat2 * Math.PI / 180;
    const latitudeDelta = (lat2 - lat1) * Math.PI / 180;
    const longitudeDelta = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(latitudeDelta / 2) ** 2
        + Math.cos(latitude1) * Math.cos(latitude2) * Math.sin(longitudeDelta / 2) ** 2;

    return earthRadius * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function formatGeofenceName(fullName) {
    if (typeof fullName !== 'string' || !fullName.trim()) {
        return 'Geocerca de la misión';
    }

    const parts = fullName.split(',').map(part => part.trim()).filter(Boolean);
    const placeName = parts[0];
    const municipality = parts.find(part => part.toUpperCase().startsWith('MUNICIPIO DE '));
    const city = municipality?.replace(/^MUNICIPIO DE\s+/i, '').trim();

    return city ? `${placeName}, ${city}` : placeName;
}

function geofenceStateKey(userId, assignment) {
    return `${userId}:${assignment.mission_id}:${assignment.geofence.id}`;
}

function evaluateGlobalGeofences(position, allowNotification) {
    if (!globalGeofencesReady || !position?.user_id) return;

    const userId = Number(position.user_id);
    const latitude = parseFloat(position.latitude);
    const longitude = parseFloat(position.longitude);
    const assignments = globalGeofenceAssignments.get(userId) || [];
    if (!Number.isFinite(latitude) || !Number.isFinite(longitude) || assignments.length === 0) return;

    assignments.forEach(assignment => {
        const centerLatitude = parseFloat(assignment.geofence.centro?.lat);
        const centerLongitude = parseFloat(assignment.geofence.centro?.lng);
        const radiusMeters = parseFloat(assignment.geofence.radio_km) * 1000;
        if (!Number.isFinite(centerLatitude) || !Number.isFinite(centerLongitude) || !Number.isFinite(radiusMeters)) {
            return;
        }

        const distance = geofenceDistanceMeters(
            latitude,
            longitude,
            centerLatitude,
            centerLongitude,
        );
        const stateKey = geofenceStateKey(userId, assignment);
        const previousState = globalGeofenceStates.get(stateKey);
        const isInside = distance <= radiusMeters;
        const exitMargin = Math.min(200, Math.max(50, radiusMeters * 0.05));

        if (previousState === undefined) {
            globalGeofenceStates.set(stateKey, isInside);
            return;
        }

        if (!previousState && isInside) {
            globalGeofenceStates.set(stateKey, true);
            if (allowNotification && typeof window.Swal !== 'undefined') {
                const notificationKey = `geofence-entry:${stateKey}:${position.recorded_at || ''}`;
                if (sessionStorage.getItem(notificationKey)) return;
                sessionStorage.setItem(notificationKey, '1');

                playNotificationSound('success');
                window.Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `${position.user?.name || 'Un agente'} ingresó a ${formatGeofenceName(assignment.geofence.nombre_referencia)}`,
                    text: assignment.mission_name,
                    showConfirmButton: false,
                    timer: 6000,
                    timerProgressBar: true,
                });
            }
        } else if (previousState && distance > radiusMeters + exitMargin) {
            globalGeofenceStates.set(stateKey, false);
        }
    });
}

async function initializeGlobalGeofences() {
    try {
        const response = await fetch('/api/custodios/geocercas-activas', {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) return;

        const data = await response.json();
        globalGeofenceAssignments.clear();
        globalGeofenceStates.clear();

        (data.misiones || []).forEach(mission => {
            (mission.agentes_id || []).forEach(agentId => {
                const numericAgentId = Number(agentId);
                const assignments = globalGeofenceAssignments.get(numericAgentId) || [];
                (mission.geocercas || []).forEach(geofence => {
                    assignments.push({
                        mission_id: mission.id,
                        mission_name: mission.nombre,
                        geofence,
                    });
                });
                globalGeofenceAssignments.set(numericAgentId, assignments);
            });
        });

        globalGeofencesReady = true;
        (data.posiciones || []).forEach(position => evaluateGlobalGeofences(position, false));
    } catch (error) {
        console.error('No fue posible cargar las geocercas activas:', error);
    }
}

function showMovementToast(position, notificationKey) {
    if (notifiedMovementStarts.has(notificationKey)) {
        return;
    }

    notifiedMovementStarts.add(notificationKey);

    if (typeof window.Swal !== 'undefined') {
        playNotificationSound('info');
        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: `${position.user?.name || 'Un custodio'} está en movimiento`,
            text: 'Ha iniciado el seguimiento de ubicación.',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
        });
    }
}

async function notifyMovementStart(event) {
    if (!canReceiveCustodianNotifications()) {
        return;
    }

    const position = event?.position;

    if (!position?.user_id || !position?.recorded_at) {
        return;
    }

    const role = position.user?.rol?.toLowerCase() || '';
    if (!role.includes('escolta')) {
        return;
    }

    const notificationKey = `${position.user_id}:${position.recorded_at}`;
    if (notifiedMovementStarts.has(notificationKey)) {
        return;
    }

    const eventTime = new Date(position.recorded_at).getTime();
    if (Number.isNaN(eventTime)) {
        return;
    }

    const previousEventTime = lastRealtimePositionByUser.get(position.user_id);
    lastRealtimePositionByUser.set(position.user_id, eventTime);

    if (previousEventTime !== undefined) {
        const inactivityMinutes = (eventTime - previousEventTime) / 60000;
        if (inactivityMinutes > MOVEMENT_INACTIVITY_MINUTES) {
            showMovementToast(position, notificationKey);
        }
        return;
    }

    try {
        const response = await fetch(`/api/realtime-position/user/${encodeURIComponent(position.user_id)}/recent?limit=2`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const positions = data.positions || [];
        const currentPosition = positions[0];
        const previousPosition = positions[1];

        if (!currentPosition) {
            return;
        }

        const currentTime = new Date(currentPosition.recorded_at).getTime();
        const previousTime = previousPosition
            ? new Date(previousPosition.recorded_at).getTime()
            : null;

        const inactivityMinutes = previousTime === null
            ? Infinity
            : (currentTime - previousTime) / 60000;

        if (inactivityMinutes <= MOVEMENT_INACTIVITY_MINUTES) {
            return;
        }

        notifiedMovementStarts.add(notificationKey);

        if (typeof window.Swal !== 'undefined') {
            playNotificationSound('info');
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `${position.user?.name || 'Un custodio'} está en movimiento`,
                text: 'Ha iniciado el seguimiento de ubicación.',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
            });
        }
    } catch (error) {
        console.error('No fue posible verificar el inicio de movimiento:', error);
    }
}

// Inicializar listeners de chat si estamos en una vista de conversación
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationSoundButton();
    document.getElementById('notification-sound-toggle')
        ?.addEventListener('click', toggleNotificationSounds);
    document.addEventListener('pointerdown', unlockNotificationAudio, {
        once: true,
        capture: true,
    });

    if (window.userId && typeof window.Echo !== 'undefined') {
        window.Echo.private(`App.Models.User.${window.userId}`)
            .listen('.ToastNotification', showPrivateRealtimeToast);
    }

    // Buscar ID de conversación en múltiples lugares posibles
    if (canReceiveCustodianNotifications() && typeof window.Echo !== 'undefined') {
        initializeGlobalGeofences();
        window.Echo.channel('realtime-positions.all')
            .listen('.NuevaUbicacionRealtime', event => {
                notifyMovementStart(event);
                evaluateGlobalGeofences(event?.position, true);
            });

        window.Echo.channel('panic-alerts.all')
            .listen('.NuevaAlertaPanico', showPanicAlert);
    }

    const conversationElement = document.querySelector('[data-conversation-id]') ||
                              document.querySelector('#conversation-id') ||
                              document.querySelector('[data-chat]');

    const conversationId = conversationElement?.dataset.conversationId ||
                          conversationElement?.dataset.chat ||
                          document.querySelector('#conversation-id')?.textContent?.trim();

    // Si encontramos un ID de conversación, iniciamos la escucha
    if (conversationId && typeof window.setupChatListeners === 'function') {
        // Convertir a número si es necesario
        const numericId = parseInt(conversationId, 10);
        if (!isNaN(numericId)) {
            window.setupChatListeners(numericId);
        }
    }
});

// Opcional: Si necesitas acceder globalmente
window.LivewireEcho = {
    setup: (conversationId, componentId) => {
        if (window.setupChatListeners) {
            window.setupChatListeners(conversationId, componentId);
        }
    }
};
