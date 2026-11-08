<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends ApiFormRequest
{
     public function authorize(): bool
     {
          return true;
     }

      public function rules(): array
    {
        return [
            'date' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
        ];
    }
}