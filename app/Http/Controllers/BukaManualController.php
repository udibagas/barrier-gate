<?php

namespace App\Http\Controllers;

use App\BukaManual;
use App\Http\Requests\BukaManualRequest;
use Illuminate\Http\Request;

class BukaManualController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort = $request->sort ? $request->sort : 'buka_manuals.updated_at';
        $order = $request->order == 'ascending' ? 'asc' : 'desc';

        return BukaManual::selectRaw('buka_manuals.*, users.name AS user, barrier_gates.nama AS gate')
        ->join('users', 'users.id', '=', 'buka_manuals.user_id')
        ->join('barrier_gates', 'barrier_gates.id', '=', 'buka_manuals.barrier_gate_id')
        ->when($request->dateRange, function($q) use ($request) {
            return $q->whereRaw('DATE(buka_manuals.updated_at) BETWEEN "'.$request->dateRange[0].'" AND "'.$request->dateRange[1].'"');
        })->when($request->keyword, function ($q) use ($request) {
            return $q->where('alasan', 'LIKE', '%' . $request->keyword . '%');
        })->orderBy($sort, $order)->paginate($request->pageSize);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BukaManualRequest $request)
    {
        $input = $request->all();
        $input['user_id'] = $request->user()->id;

        return BukaManual::create($input);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BukaManualRequest $request, BukaManual $bukaManual)
    {
        $bukaManual->update($request->all());
        return $bukaManual;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(BukaManual $bukaManual)
    {
        $bukaManual->delete();
        return ['response' => 'Data telah dihapus'];
    }
}
