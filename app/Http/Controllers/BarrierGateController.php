<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BarrierGateRequest;
use App\BarrierGate;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use GuzzleHttp\Client;
use App\Notifications\SettingChanged;

class BarrierGateController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:2')->except(['getList', 'search', 'openGate', 'takeSnapshot']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort = $request->sort ? $request->sort : 'nama';
        $order = $request->order == 'ascending' ? 'asc' : 'desc';

        return BarrierGate::when($request->keyword, function ($q) use ($request) {
                return $q->where('nama', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('controller_ip_address', 'LIKE', '%' . $request->keyword . '%');
            })->orderBy($sort, $order)->paginate($request->pageSize);
        }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BarrierGateRequest $request)
    {
        $gate = BarrierGate::create($request->all());
        // shell_exec('sudo systemctl restart barrier_gate');
        $message = 'User '.$request->user()->name.' menambahkan gate '.json_encode($gate);
        $this->systemUser->notify(new SettingChanged($request->user(), $message));
        return $gate;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $gate = BarrierGate::when($request->jenis, function($q) use ($request) {
                return $q->where('jenis', $request->jenis);
            })->first();

        if (!$gate) {
            return response(['message' => 'Not found'], 404);
        }

        return $gate;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BarrierGateRequest $request, BarrierGate $barrierGate)
    {
        $barrierGate->update($request->all());
        $changes = $barrierGate->getChanges();

        if (!!$changes)
        {
            // shell_exec('sudo systemctl restart barrier_gate');
            $message = 'User '.$request->user()->name.' merubah setingan gate '.json_encode($changes);
            $this->systemUser->notify(new SettingChanged($request->user(), $message));
        }

        return $barrierGate;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(BarrierGate $barrierGate)
    {
        $barrierGate->delete();
        // shell_exec('sudo systemctl restart barrier_gate');
        $message = 'User '.auth()->user()->name.' menghapus gate '.json_encode($barrierGate);
        $this->systemUser->notify(new SettingChanged(auth()->user(), $message));
        return ['message' => 'Parking gate telah dihapus'];
    }

    public function getList()
    {
        return BarrierGate::orderBy('nama', 'asc')->get();
    }

    public function testCamera(BarrierGate $barrierGate)
    {
        $client = new Client(['timeout' => 3]);

        try {
            $response = $client->request('GET', $barrierGate->camera_snapshot_url, [
                'auth' => [
                    $barrierGate->camera_username,
                    $barrierGate->camera_password,
                    'digest'
                ]
            ]);

            if ($response->getHeader('Content-Type')[0] != 'image/jpeg') {
                return response(['message' => 'GAGAL MENGAMBIL GAMBAR. URL SNAPSHOT KAMERA TIDAK SESUAI'], 500);
            }

        } catch (\Exception $e) {
            return response(['message' => 'GAGAL MENGAMBIL GAMBAR. '. $e->getMessage()], 500);
        }

        return [
            'message' => 'BERHASIL MENGAMBIL SNAPSHOT',
            'snapshot' => base64_encode($response->getBody()->getContents())
        ];
    }

    public function testPrinter(BarrierGate $barrierGate)
    {
        if ($barrierGate->type == 'IN' && $barrierGate->printer_type == 'local') {
            return response(['message' => 'PRINTER GATE IN SERIAL TIDAK BISA DITEST DARI WEB'], 500);
        }

        try {
            if ($barrierGate->printer_type == "network") {
                $connector = new NetworkPrintConnector($barrierGate->printer_ip_address, 9100);
            } else if ($barrierGate->printer_type == "local") {
                $connector = new FilePrintConnector($barrierGate->printer_device);
            } else {
                return response(['message' => 'INVALID PRINTER'], 500);
            }

            $printer = new Printer($connector);
        } catch (\Exception $e) {
            return response(['message' => 'KONEKSI KE PRINTER GAGAL. ' . $e->getMessage()], 500);
        }

        try {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("TEST PRINTER\n");
            $printer->text($barrierGate->name . "\n");
            $printer->text(date('d-M-Y H:i:s'));
            $printer->text("\n\n");
            $printer->setBarcodeHeight(100);
            $printer->setBarcodeWidth(4);
            $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
            $printer->barcode("ABC123");
            $printer->cut();
            $printer->close();
        } catch (\Exception $e) {
            return response(['message' => 'GAGAL MENCETAK.' . $e->getMessage()], 500);
        }

        return ['message' => 'BERHASIL MENCETAK'];
    }

    public function takeSnapshot(BarrierGate $barrierGate)
    {
        if (!$barrierGate->camera_status || !$barrierGate->camera_snapshot_url) {
            return response(['message' => 'GAGAL MENGAMBIL GAMBAR. TIDAK ADA KAMERA.'], 404);
        }

        $client = new Client(['timeout' => 3]);

        $fileName = 'snapshot/'.date('Y/m/d/H/').$barrierGate->nama.'-'.date('YmdHis').'.jpg';

        if (!is_dir('snapshot/'.date('Y/m/d/H'))) {
            mkdir('snapshot/'.date('Y/m/d/H'), 0777, true);
        }

        try {
            $response = $client->request('GET', $barrierGate->camera_snapshot_url, [
                'auth' => [
                    $barrierGate->camera_username,
                    $barrierGate->camera_password,
                    'digest'
                ]
            ]);

            if ($response->getHeader('Content-Type')[0] == 'image/jpeg') {
                file_put_contents($fileName, $response->getBody());
            } else {
                return response(['message' => 'GAGAL MENGAMBIL GAMBAR. URL SNAPSHOT KAMERA TIDAK SESUAI'], 500);
            }
        } catch (\Exception $e) {
            return response(['message' => 'GAGAL MENGAMBIL GAMBAR. '. $e->getMessage()], 500);
        }

        return ['filename' => $fileName];
    }
}
