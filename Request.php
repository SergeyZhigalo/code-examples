<?php

namespace App\Http\Requests\Api\Example;

use App\Models\Example;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateExampleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255', Rule::unique(Example::class, 'name')->ignore($this->id)],
            'code' => ['nullable', 'string', 'max:255', Rule::unique(Example::class, 'code')->ignore($this->id)],
            'isActive' => 'nullable|boolean',
            'updatedBy' => 'required|uuid',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Сущность с таким наименованием уже добавлена',
            'code.unique' => 'Сущность с таким кодом уже добавлена',
        ];
    }
}
