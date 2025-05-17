<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * @OA\Schema(
 *      title="Store User Request",
 *      description="Request body for creating a new user",
 *      type="object",
 *      required={"name", "email", "password", "password_confirmation"}
 * )
 */

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /**
         * @OA\Property(property="name", type="string", example="John Doe"),
         * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
         * @OA\Property(property="password", type="string", format="password", example="password123"),
         * @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
         * @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"user"}, description="Array of role names to assign to the user.")
         */
        return $this->user()->can('manage users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
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