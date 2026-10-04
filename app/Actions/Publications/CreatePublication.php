<?php

declare(strict_types=1);

namespace App\Actions\Publications;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreatePublication
{
    public function __construct(private readonly SyncPublicationImages $images) {}

    /**
     * @param  list<string>  $imageOrder
     * @param  list<UploadedFile>  $uploads
     */
    public function execute(User $owner, PublicationData $data, array $imageOrder, array $uploads): Publication
    {
        return DB::transaction(function () use ($owner, $data, $imageOrder, $uploads): Publication {
            $publication = new Publication($data->toAttributes());
            $publication->user()->associate($owner);
            $publication->save();

            $this->images->execute($publication, $imageOrder, $uploads);

            return $publication;
        });
    }
}
