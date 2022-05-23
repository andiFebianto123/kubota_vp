<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class DeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $minDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $maxDate = Carbon::now()->addDay(7)->format('Y-m-d');

        return [
            'shipped_date' => 'required|date|after:' . $minDate . '|before:'.$maxDate,
            'shipped_qty' => 'required|numeric|gt:0',
            'petugas_vendor' => 'required',
            'no_surat_jalan_vendor' => 'required'
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
