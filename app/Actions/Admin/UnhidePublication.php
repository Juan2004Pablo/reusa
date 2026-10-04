<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Publication;

class UnhidePublication
{
    public function execute(Publication $publication): Publication
    {
        $publication->forceFill([
            'hidden_at' => null,
            'hidden_reason' => null,
            'hidden_by' => null,
        ])->save();

        return $publication;
    }
}
