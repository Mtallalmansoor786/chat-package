<?php

namespace ChatPackage\ChatPackage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateChatRoomRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The room name is required.',
            'name.max' => 'The room name may not be greater than 255 characters.',
            'user_ids.required' => 'Please select at least one user to add to the room.',
            'user_ids.min' => 'Please select at least one user to add to the room.',
            'user_ids.*.exists' => 'One or more selected users are invalid.',
        ];
    }
}

