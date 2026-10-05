<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $question
 * @property string|null $conversation_id
 */
class AskCompanionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:2', 'max:2000'],
            'conversation_id' => ['nullable', 'string', 'uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'question' => (string) trans('assistant.field.question'),
            'conversation_id' => (string) trans('assistant.field.conversation_id'),
        ];
    }
}
