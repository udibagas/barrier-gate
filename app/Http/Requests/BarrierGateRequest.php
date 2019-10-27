<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BarrierGateRequest extends FormRequest
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
            'jenis' => 'required|in:IN,OUT',
            'camera_username' => 'required',
            'camera_password' => 'required',
            'camera_snapshot_url' => 'required|url',
            'controller_ip_address' => 'required|ipv4',
            'controller_port' => 'required|numeric',
            'printer_type' => 'required|in:network,local',
            'printer_device' => 'required_if:printer_type,local',
            'printer_ip_address' => 'required_if:printer_type,network|ipv4',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Nama',
            'type' => 'Jenis',
            'controller_ip_address' => 'Alamat IP Kontroller',
            'controller_port' => 'Port Kontroller',
            'printer_type' => 'Jenis Printer',
            'printer_device' => 'Device Printer',
            'printer_ip_address' => 'Alamat IP Printer',
            'camera_username' => 'Username Kamera',
            'camera_password' => 'Password Kamera',
            'camera_snapshot_url' => 'URL Snapshot Kamera',
        ];
    }

}
