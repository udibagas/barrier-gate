<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KarcisHilangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nama' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'no_plat' => 'required',
            'jenis_kartu_identitas' => 'required',
            // 'nomor_kartu_identitas' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'nama' => 'Nama',
            'alamat' => 'Alamat',
            'no_hp' => 'No HP',
            'no_plat' => 'No Plat',
            'jenis_kartu_identitas' => 'Jenis Kartu Identitas',
            'nomor_kartu_identitas' => 'Nomor Kartu Identitas',
        ];
    }
}
