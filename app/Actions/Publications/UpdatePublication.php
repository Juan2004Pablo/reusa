<?php

declare(strict_types=1);

namespace App\Actions\Publications;

use App\Models\Publication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdatePublication
{
    public function __construct(private readonly SyncPublicationImages $images) {}

    /**
     * @param  list<string>  $imageOrder
     * @param  list<UploadedFile>  $uploads
     */
    public function execute(Publication $publication, PublicationData $data, array $imageOrder, array $uploads): Publication
    {
        return DB::transaction(function () use ($publication, $data, $imageOrder, $uploads): Publication {
            // El slug no cambia aunque cambie el título: los enlaces compartidos siguen vigentes.
            $publication->fill($data->toAttributes())->save();

            $this->images->execute($publication, $imageOrder, $uploads);

            return $publication;
        });
    }
}
