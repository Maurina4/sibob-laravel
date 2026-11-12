<?php

namespace App\Http\Requests\Bmn;

use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateBmnLokasiPjRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('items'))) {
            $decoded = json_decode($this->input('items'), true);
            if (is_array($decoded)) {
                $this->merge(['items' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:bmns,id'],
            'items.*.lokasi' => ['nullable', 'string', 'max:255'],
            'items.*.penanggungjawab' => ['nullable', 'string', 'max:255'],
            'items.*.penanggung_jawab_id' => ['nullable', 'integer', 'exists:pegawais,id'],
            'items.*.penanggungjawab_nip' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Tidak ada data perubahan yang dikirim.',
            'items.min' => 'Minimal satu data perubahan harus dikirim.',
            'items.*.id.exists' => 'Data BMN tidak ditemukan.',
            'items.*.penanggung_jawab_id.exists' => 'Data pegawai penanggung jawab tidak valid.',
        ];
    }
}
