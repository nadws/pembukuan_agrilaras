<?php

namespace App\Http\Requests;

use App\Models\AkunPerkiraan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SimpanAkunPerkiraanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $akun = $this->route('akun_perkiraan_baru');
        $id = $akun instanceof AkunPerkiraan ? $akun->getKey() : null;

        return [
            'tipe_akun' => ['required', 'string', 'max:20'],
            'kode_perkiraan' => ['required', 'string', 'max:50', Rule::unique('akun_perkiraan')->ignore($id, 'id_akun_perkiraan')],
            'nama' => ['required', 'string', 'max:255'],
            'id_akun_induk' => ['nullable', 'integer', 'exists:akun_perkiraan,id_akun_perkiraan'],
            'cabang_saldo' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $akun = $this->route('akun_perkiraan_baru');
            $indukId = $this->integer('id_akun_induk') ?: null;

            if (! $akun instanceof AkunPerkiraan || ! $indukId) {
                return;
            }

            if ($akun->getKey() === $indukId || $this->isDescendant($akun, $indukId)) {
                $validator->errors()->add('id_akun_induk', 'Akun induk tidak boleh membentuk hubungan melingkar.');
            }
        }];
    }

    private function isDescendant(AkunPerkiraan $akun, int $candidateId): bool
    {
        $parent = AkunPerkiraan::find($candidateId);

        while ($parent) {
            if ($parent->getKey() === $akun->getKey()) {
                return true;
            }

            $parent = $parent->akunInduk;
        }

        return false;
    }
}
