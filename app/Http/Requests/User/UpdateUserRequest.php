<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
/**
 * @OA\Schema(
 *      title="Update User Request",
 *      description="Request body for updating an existing user",
 *      type="object"
 * )
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /**
         * @OA\Property(property="name", type="string", example="Jane Doe Updated"),
         * @OA\Property(property="email", type="string", format="email", example="jane.doe.updated@example.com"),
         * @OA\Property(property="password", type="string", format="password", example="newpassword123", description="Optional. If provided, password_confirmation is also required."),
         * @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123", description="Required if password is provided."),
         * @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"user", "editor"}, description="Array of role names to assign to the user. Replaces existing roles.")
         */
        return $this->user()->id == $this->route('user')->id || $this->user()->can('manage users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => 'sometimes|nullable|string|min:8|confirmed', 
            'roles' => 'sometimes|array',
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => __('The given data was invalid.'), 
            'errors' => $validator->errors(),
        ], 422));
    }
}