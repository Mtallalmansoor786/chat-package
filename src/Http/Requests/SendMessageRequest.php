<?php

namespace ChatPackage\ChatPackage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $maxLength = config('chat-package.chat.max_message_length', 1000);

        return [
            'message' => "required|string|max:{$maxLength}",
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $maxLength = config('chat-package.chat.max_message_length', 1000);

        return [
            'message.required' => 'The message cannot be empty.',
            'message.max' => "The message may not be greater than {$maxLength} characters.",
        ];
    }
}

