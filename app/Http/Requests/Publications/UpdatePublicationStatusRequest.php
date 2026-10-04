<?php

declare(strict_types=1);

namespace App\Http\Requests\Publications;

use App\Enums\PublicationStatus;
use App\Models\Publication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePublicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('changeStatus', $this->publication()) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(PublicationStatus::class)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('status')) {
                return;
            }

            $publication = $this->publication();

            if (! $publication->status->canTransitionTo($this->status(), $publication->modality)) {
                $validator->errors()->add('status', __('messages.status.invalid_transition', [
                    'from' => mb_strtolower($publication->status->label()),
                    'to' => mb_strtolower($this->status()->label()),
                ]));
            }
        }];
    }

    public function status(): PublicationStatus
    {
        return PublicationStatus::from((string) $this->input('status'));
    }

    private function publication(): Publication
    {
        /** @var Publication $publication */
        $publication = $this->route('publication');

        return $publication;
    }
}
