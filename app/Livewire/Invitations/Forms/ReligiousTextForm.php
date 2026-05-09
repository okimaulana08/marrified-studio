<?php

declare(strict_types=1);

namespace App\Livewire\Invitations\Forms;

use App\Enums\ReligionType;
use App\Models\Invitation;
use Livewire\Form;

/**
 * Single JSON column on Invitation. The active key set is determined by the
 * invitation's religion_type (e.g. islam → ayat/translation/source). When
 * religion changes, fields outside the new key set are simply ignored on
 * persist — they never reach the DB row.
 */
final class ReligiousTextForm extends Form
{
    /**
     * Free-form key/value pairs. Allows admin to fill any field the religion
     * exposes; persist() filters down to the keys this religion supports.
     *
     * @var array<string, string>
     */
    public array $values = [];

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'values.*' => 'nullable|string|max:2000',
        ];
    }

    public function fillFromModel(Invitation $invitation): void
    {
        $religion = $invitation->religion_type !== null ? ReligionType::tryFrom($invitation->religion_type) : null;
        $stored = (array) ($invitation->religious_text ?? []);

        $this->values = [];
        if ($religion === null) {
            return;
        }
        foreach ($religion->fieldKeys() as $key) {
            $this->values[$key] = (string) ($stored[$key] ?? '');
        }
    }

    public function syncKeys(?ReligionType $religion): void
    {
        $existing = $this->values;
        $this->values = [];
        if ($religion === null) {
            return;
        }
        foreach ($religion->fieldKeys() as $key) {
            $this->values[$key] = (string) ($existing[$key] ?? '');
        }
    }

    public function persist(Invitation $invitation): void
    {
        $religion = $invitation->religion_type !== null ? ReligionType::tryFrom($invitation->religion_type) : null;

        if ($religion === null || $religion === ReligionType::None) {
            $invitation->update(['religious_text' => null]);

            return;
        }

        $payload = [];
        foreach ($religion->fieldKeys() as $key) {
            $value = trim((string) ($this->values[$key] ?? ''));
            if ($value !== '') {
                $payload[$key] = $value;
            }
        }

        $invitation->update(['religious_text' => $payload === [] ? null : $payload]);
    }
}
