<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Policies\ConversationPolicy;
use App\Policies\MessagePolicy;
use Tests\TestCase;

class MessagingAuthorizationTest extends TestCase
{
    public function test_only_participants_can_view_a_conversation(): void
    {
        $participant = (new User())->forceFill(['id' => 1]);
        $otherParticipant = (new User())->forceFill(['id' => 2]);
        $outsider = (new User())->forceFill(['id' => 3]);
        $conversation = (new Conversation())->setRelation('users', collect([$participant, $otherParticipant]));

        $policy = new ConversationPolicy();

        $this->assertTrue($policy->view($participant, $conversation));
        $this->assertFalse($policy->view($outsider, $conversation));
    }

    public function test_only_the_sender_can_delete_a_recent_message(): void
    {
        $sender = (new User())->forceFill(['id' => 1]);
        $recipient = (new User())->forceFill(['id' => 2]);
        $message = (new Message())->forceFill([
            'user_id' => $sender->id,
            'body' => 'Mensaje de prueba',
            'created_at' => now(),
        ]);

        $policy = new MessagePolicy();

        $this->assertTrue($policy->delete($sender, $message));
        $this->assertFalse($policy->delete($recipient, $message));
    }
}
