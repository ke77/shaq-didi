<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Validation\Rule;


class RegisterRequest extends ApiFormRequest
{
     // public endpoint
     public function authorize(): bool
     {
          return true;
     }

     public function rules(): array
     {
          return [
               'role' => ['required', Rule::enum(UserRole::class)],
               'name' => ['required', 'string', 'max:255'],
               'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
               'password' => ['required', 'string', 'min:8'],
               'phone_number' => ['required', 'string', 'unique:users,phone_number']
          ];
     }
}