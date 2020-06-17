<?php

namespace App\AuditLog\Requests;

use App\Http\Requests\Request;

/**
 * Class AllEvents
 */
class AllEvents extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'user_id' => 'exists:users,id',
        ];

        return $rules;
    }
}
