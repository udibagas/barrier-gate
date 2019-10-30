<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'sometimes|required|alpha_num|confirmed|min:6',
            'nip' => 'required',
            'department_id' => 'required',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'date',
            'role' => 'required|numeric',
            'status' => 'required|boolean',
            'masa_aktif_kartu' => 'date'
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Nama',
            'email' => 'Email',
            'password' => 'Password',
            'nip' => 'NIP',
            'department_id' => 'Departemen',
            'jenis_kelamin' => 'Jenis Kelamin',
            'role' => 'Level',
            'status' => 'Status',
            'foto' => 'required',
            'masa_aktif_kartu' => 'Masa Aktif Kartu'
        ];
    }
}
