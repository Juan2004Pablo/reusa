<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationModality;
use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        /** @var array<string, int> $byStatus */
        $byStatus = Publication::query()->selectRaw('status, count(*) as total')->groupBy('status')
            ->pluck('total', 'status')->map(fn ($total) => (int) $total)->all();

        /** @var array<string, int> $byModality */
        $byModality = Publication::query()->selectRaw('modality, count(*) as total')->groupBy('modality')
            ->pluck('total', 'modality')->map(fn ($total) => (int) $total)->all();

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('is_active', true)->count(),
                    'inactive' => User::where('is_active', false)->count(),
                    'admins' => User::where('role', UserRole::Admin)->count(),
                ],
                'publications' => [
                    'total' => Publication::count(),
                    'hidden' => Publication::whereNotNull('hidden_at')->count(),
                    'by_status' => collect(PublicationStatus::cases())->map(fn (PublicationStatus $status) => [
                        'value' => $status->value,
                        'label' => $status->label(),
                        'total' => $byStatus[$status->value] ?? 0,
                    ])->all(),
                    'by_modality' => collect(PublicationModality::cases())->map(fn (PublicationModality $modality) => [
                        'value' => $modality->value,
                        'label' => $modality->label(),
                        'total' => $byModality[$modality->value] ?? 0,
                    ])->all(),
                ],
            ],
        ]);
    }
}
