<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingRequest;
use App\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:2')->except(['index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $setting = Setting::first();
        return ($setting) ? $setting : response(['message' => 'Belum ada setting'], 404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SettingRequest $request)
    {
        return Setting::create($request->all());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SettingRequest $request, Setting $setting)
    {
        $setting->update($request->all());
        return $setting;
    }
}
