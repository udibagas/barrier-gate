<?php

namespace App\Http\Controllers;

use App\BarrierGate;
use App\Http\Requests\BarrierGateRequest;
use Illuminate\Http\Request;

class BarrierGateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return BarrierGate::paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BarrierGateRequest $request)
    {
        return BarrierGate::create($request->all());
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
        return ['message' => 'Data berhasil dihapus'];
    }
}
