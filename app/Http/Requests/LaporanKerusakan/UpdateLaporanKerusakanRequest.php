<?php

namespace App\Http\Requests\LaporanKerusakan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLaporanKerusakanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:Dilaporkan,Diproses,Selesai'],
            'tanggapan' => ['nullable', 'string'],
        ];
    }
}
