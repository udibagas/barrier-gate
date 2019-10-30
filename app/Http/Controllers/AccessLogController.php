<?php

namespace App\Http\Controllers;

use App\AccessLog;
use Illuminate\Http\Request;

class AccessLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return AccessLog::paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return AccessLog::create($request->all());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AccessLog $accessLog)
    {
        $accessLog->update($request->all());
        return $accessLog;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccessLog $accessLog)
    {
        $accessLog->delete();
        return ['message' => 'Data berhasil dihapus'];
    }

    // untuk di gate out
    public function search(Request $request)
    {
        $data = AccessLog::where(function($q) use ($request) {
            return $q->where('nomor_kartu', $request->nomor_kartu)
                ->orWhere('nomor_barcode');
        })->where('time_out', null)->latest()->first();

        return ($data) ? $data : response(['message' => 'Data tidak ditemukan'], 404);
    }
}
