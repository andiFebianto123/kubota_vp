<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class TaxInvoiceRequest extends FormRequest
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
        return [
            // 'file_faktur_pajak' => 'required|mimes:zip,pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            'invoice' => 'mimes:zip,pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            'file_surat_jalan' => 'mimes:zip,pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            'ds_nums' => 'required'
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
