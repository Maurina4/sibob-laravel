<?php

namespace App\Http\Requests\PengajuanPengembalian;

use Illuminate\Foundation\Http\FormRequest;

class TolakPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Operator';
    }

    public function rules(): array
    {
        return [
            'catatan_penolakan' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'catatan_penolakan.required' => 'Catatan alasan penolakan wajib diisi untuk menginformasikan kepada pemohon.',
            'catatan_penolakan.min' => 'Catatan penolakan minimal 5 karakter.',
            'catatan_penolakan.max' => 'Catatan penolakan maksimal 1000 karakter.',
        ];
    }
}
