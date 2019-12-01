<?php

namespace App\Http\Controllers;

use App\Http\Requests\KarcisHilangRequest;
use App\KarcisHilang;
use Illuminate\Http\Request;

class KarcisHilangController extends Controller
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

        return KarcisHilang::when($request->dateRange, function($q) use ($request) {
            return $q->whereRaw('DATE(updated_at) BETWEEN "'.$request->dateRange[0].'" AND "'.$request->dateRange[1].'"');
        })->when($request->keyword, function ($q) use ($request) {
            return $q->where('no_plat', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('nama', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('no_hp', 'LIKE', '%' . $request->keyword . '%')
                        ->orWhere('alamat', 'LIKE', '%' . $request->keyword . '%');
        })->orderBy($sort, $order)->paginate($request->pageSize);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(KarcisHilangRequest $request)
    {
        $input = $request->all();
        $input['user_id'] = $request->user()->id;
        return KarcisHilang::create($input);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(KarcisHilangRequest $request, KarcisHilang $karcisHilang)
    {
        $karcisHilang->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(KarcisHilang $karcisHilang)
    {
        $karcisHilang->delete();
        return ['message' => 'Data telah dihapus'];
    }
}
