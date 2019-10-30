<?php

namespace App\Http\Controllers;

use App\AccessLog;
use App\BarrierGate;
use App\User;
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
        $data = AccessLog::when($request->nomor_kartu, function($q) use ($request) {
            return $q->where('nomor_kartu', $request->nomor_kartu);
        })->when($request->nomor_barcode, function($q) use ($request) {
            return $q->where('nomor_barcode', $request->nomor_barcode);
        })->where('time_out', null)->latest()->first();

        if ($data) {
            return $data;
        }

        // ini sudah lewat pengecekan member aktif atau tidak
        if ($request->nomor_kartu) {
            return AccessLog::create([
                'time_in' => now(),
                'nomor_barcode' => 'NOTAPIN',
                'is_staff' => 1,
                'nomor_kartu' => $request->nomor_kartu,
                'user_id' => User::where('nomor_kartu', '%'.$request->nomor_kartu)->first()
            ]);
        }

        return response(['message' => 'Data tidak ditemukan'], 500);
    }
}
