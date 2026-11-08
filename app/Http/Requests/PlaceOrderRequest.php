<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Auth\Access\Response;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends ApiFormRequest
{
     // runs firest. a failure here is a 403
     public function authorize(): bool|Response
     {
          return $this->user()->role === UserRole::PermanentStaff
               ? Response::allow()
               : Response::deny('Only Permanent Staff can place orders');
     }

     public function rules(): array
     {
          return [
               'product_id' => ['required', 'integer', 'exists:products,id'],
               'amount' => ['required', 'integer', 'min:1', 'max:500'],
               'order_note' => ['nullable', 'string', 'max:500'],
               // must exist AND be an NSP(so no manual User::find() needed here)
               'personnel_id' => [
                    'required',
                    'integer',
                    Rule::exists('users', 'id')->where('role', UserRole::NationalService->value)
               ]
          ];
     }

     // laravel will automatically return a 422 with these messages if validation fails
     public function messages(): array
     {
          return [
               'personnel_id.exists' => 'The selected personnel must be a National Service Personnel.'
          ];
     }
}