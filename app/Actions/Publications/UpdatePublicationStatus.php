<?php

declare(strict_types=1);

namespace App\Actions\Publications;

use App\Enums\PublicationStatus;
use App\Exceptions\InvalidStatusTransition;
use App\Models\Publication;

class UpdatePublicationStatus
{
    /**
     * @throws InvalidStatusTransition
     */
    public function execute(Publication $publication, PublicationStatus $to): Publication
    {
        if (! $publication->status->canTransitionTo($to, $publication->modality)) {
            throw InvalidStatusTransition::between($publication->status, $to);
        }

        $publication->status = $to;
        $publication->save();

        return $publication;
    }
}
