<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $sql = "SELECT
            DATE(created_at) AS tanggal,
            SUM(CASE WHEN is_staff = 1 THEN 1 ELSE 0 END) AS staff,
            SUM(CASE WHEN is_staff = 0 THEN 1 ELSE 0 END) AS tamu
        FROM access_logs
        WHERE DATE(created_at) BETWEEN :start AND :end
        GROUP BY tanggal";

        $dateRange = is_array($request->dateRange) ? $request->dateRange : explode(',', $request->dateRange);

        $data = DB::select($sql, [
            ':start' => $dateRange[0],
            ':end' => $dateRange[1],
        ]);

        if ($request->action == 'print')
        {
            return view('pdf.report', [
                'data' => $data,
                'action' => $request->action,
                'dateRange' => $dateRange
            ]);
        }

        if ($request->action == 'pdf')
        {
            $pdf = PDF::loadview('pdf.report', [
                'data' => $data,
                'action' => $request->action,
                'dateRange' => $dateRange
            ]);

            return $pdf->download('report_barrier_gate.pdf');
        }

        return $data;
    }

    public function terparkir(Request $request)
    {
        $sql = "SELECT COUNT(id) AS jumlah, is_staff
        FROM access_logs
        WHERE time_out IS NULL
            AND DATE(created_at) BETWEEN :start AND :end
        GROUP BY is_staff";

        $data = DB::select($sql, [
            ':start' => $request->dateRange[0],
            ':end' => $request->dateRange[1],
        ]);

        return response()->json($data);
    }

    public function tanpaKartu(Request $request)
    {
        $sql = "SELECT COUNT(id) AS jumlah
        FROM access_logs
        WHERE DATE(created_at) BETWEEN :start AND :end
            AND is_staff = 1
            AND nomor_kartu IS NULL";

        $data = DB::selectOne($sql, [
            ':start' => $request->dateRange[0],
            ':end' => $request->dateRange[1],
        ]);

        return response()->json($data);
    }

    public function bukaManual(Request $request)
    {
        $sql = "SELECT COUNT(buka_manuals.id) AS jumlah, barrier_gates.nama AS gate
        FROM buka_manuals
        JOIN barrier_gates ON barrier_gates.id = buka_manuals.barrier_gate_id
        WHERE DATE(buka_manuals.created_at) BETWEEN :start AND :end
        GROUP BY barrier_gates.nama";

        $data = DB::select($sql, [
            ':start' => $request->dateRange[0],
            ':end' => $request->dateRange[1],
        ]);

        return response()->json($data);
    }

    public function karcisHilang(Request $request)
    {
        $sql = "SELECT `status`, COUNT(id) AS jumlah
        FROM karcis_hilangs
        WHERE DATE(created_at) BETWEEN :start AND :end
        GROUP BY `status`";

        $data = DB::select($sql, [
            ':start' => $request->dateRange[0],
            ':end' => $request->dateRange[1],
        ]);

        return response()->json($data);
    }
}
