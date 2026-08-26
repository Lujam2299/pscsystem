<?php

namespace Tests\Unit;

use App\Enums\RequestStatus;
use DomainException;
use PHPUnit\Framework\TestCase;

class RequestStatusTest extends TestCase
{
    public function test_pending_request_can_be_accepted_rejected_or_cancelled(): void
    {
        foreach ([RequestStatus::ACCEPTED, RequestStatus::REJECTED, RequestStatus::CANCELLED] as $target) {
            $this->assertSame($target, RequestStatus::transition('En Proceso', $target));
        }
    }

    public function test_final_status_cannot_be_changed_again(): void
    {
        $this->expectException(DomainException::class);

        RequestStatus::transition('Aceptada', RequestStatus::REJECTED);
    }
}
