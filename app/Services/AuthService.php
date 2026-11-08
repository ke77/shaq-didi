<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
     public function register(array $data): array
     {

          // password is hashed by the 'hashed' cast on the model
          $user = User::create($data);

          return [
               'user' => $user, 
               'token' => $this->issueToken($user)
          ];
    } 


     public function login(string $email, string $password): array
     {

          $user = User::where('email', $email)->first();

          // same error for unknown email and wrong password
          if (! $user || ! Hash::check($password, $user->password)) {
               throw AuthException::invalidCredentials();
          }

          return [
               'user' => $user, 
               'token' => $this->issueToken($user)
          ];
     }


     private function issueToken(User $user): string
     {
          return $user->createToken('shaq_didi_token')->plainTextToken;
     }
}