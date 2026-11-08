<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class ApiFormRequest extends FormRequest
{
     abstract public function rules(): array;

     public function after(): array
     {
          return [
               function (Validator $validator) {
                    $unexpected = array_diff(array_keys($this->all()), array_keys($this->rules()));

                    foreach ($unexpected as $field) {
                         $validator->errors()->add($field, 'This field is not accepted');
                    }
               }
          ];
     }
}