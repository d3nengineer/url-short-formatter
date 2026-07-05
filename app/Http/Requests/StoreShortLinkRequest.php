<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreShortLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'original_url' => trim((string) $this->input('original_url')),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'original_url' => ['required', 'string', 'url', 'starts_with:http://,https://', 'max:2048'],
        ];
    }
}
