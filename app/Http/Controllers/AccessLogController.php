<?php

namespace App\Http\Controllers;

use App\AccessLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort = $request->sort ? $request->sort : 'updated_at';
        $order = $request->order == 'ascending' ? 'asc' : 'desc';

        return AccessLog::when($request->dateRange, function($q) use ($request) {
            return $q->whereRaw('DATE(updated_at) BETWEEN "'.$request->dateRange[0].'" AND "'.$request->dateRange[1].'"');
        })->when($request->keyword, function ($q) use ($request) {
            return $q->where('nomor_barcode', 'LIKE', '%' . $request->keyword . '%')
                ->orWhere('plat_nomor', 'LIKE', '%' . $request->keyword . '%')
                ->orWhere('nomor_kartu', 'LIKE', '%' . $request->keyword . '%');
        })->when($request->is_staff, function ($q) use ($request) {
            return $q->whereIn('is_staff', $request->is_staff);
        })->orderBy($sort, $order)->paginate($request->pageSize);
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
            // member tapi gak tap waktu in
            return AccessLog::create([
                'time_in' => now(),
                'nomor_barcode' => 'NOTAPIN',
                'is_staff' => 1,
                'nomor_kartu' => $request->nomor_kartu,
                'user_id' => User::where('nomor_kartu', 'LIKE', '%'.$request->nomor_kartu)->first()->id
            ]);
        }

        return response(['message' => 'Data tidak ditemukan'], 500);
    }

    public function setSudahKeluar(AccessLog $accessLog)
    {
        $accessLog->time_out = now();
        $accessLog->operator = auth()->user()->name;
        $accessLog->save();
        return ['message' => 'KENDARAAN BERHASIL DISET SUDAH KELUAR'];
    }

    public function setSudahKeluarSemua(Request $request)
    {
        $sql = 'UPDATE access_logs
            SET time_out = :time_out,
                operator = :operator
            WHERE time_out IS NULL
                AND DATE(time_in) BETWEEN :start AND :stop';

        DB::update($sql, [
            ':time_out' => now(),
            ':operator' => $request->user()->name,
            ':start' => $request->dateRange[0],
            ':stop' => $request->dateRange[1]
        ]);

        return ['message' => 'KENDARAAN BERHASIL DISET SUDAH KELUAR'];
    }

    public function getQueue()
    {
        $queue = AccessLog::where('on_queue', 1)->where('time_out', null)->first();
        return $queue ? $queue : response(['message' => 'Tidak ada antrian di gate keluar'], 404);
    }
}
