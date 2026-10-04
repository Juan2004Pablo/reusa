<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Publications\UpdatePublicationStatus;
use App\Http\Requests\Publications\UpdatePublicationStatusRequest;
use App\Models\Publication;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PublicationStatusController extends Controller
{
    public function update(UpdatePublicationStatusRequest $request, Publication $publication, UpdatePublicationStatus $action): RedirectResponse
    {
        $action->execute($publication, $request->status());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('messages.publications.status_updated', ['status' => $publication->status->label()]),
        ]);

        return back();
    }
}
