<?php

namespace Webkul\EstateFlow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'price_cents' => ['nullable', 'integer', 'min:0', 'max:9223372036854775807'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
