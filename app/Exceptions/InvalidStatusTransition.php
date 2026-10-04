<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\PublicationStatus;
use DomainException;

class InvalidStatusTransition extends DomainException
{
    public static function between(PublicationStatus $from, PublicationStatus $to): self
    {
        return new self(__('messages.status.invalid_transition', [
            'from' => mb_strtolower($from->label()),
            'to' => mb_strtolower($to->label()),
        ]));
    }
}
