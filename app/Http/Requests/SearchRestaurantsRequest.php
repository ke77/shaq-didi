<?php

namespace App\Http\Requests;

class SearchRestaurantsRequest extends ApiFormRequest
{
     public function authorize(): bool
     {
          return true;
     }

     public function rules(): array
     {
          return [
               'search' => ['nullable', 'string', 'max:100'],
          ];
     }  
}