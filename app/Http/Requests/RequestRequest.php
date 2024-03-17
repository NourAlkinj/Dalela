<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
             return [
                 'user_id'=> 'required',
//                 'status'=> 'required',
                 'request_date'=> 'required',
             ];
    }

}
