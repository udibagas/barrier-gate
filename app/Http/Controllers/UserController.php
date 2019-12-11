<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:2')->except(['search']);
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

        return User::when($request->keyword, function ($q) use ($request) {
            return $q->where('name', 'LIKE', '%' . $request->keyword . '%')
                ->orWhere('nip', 'LIKE', '%' . $request->keyword . '%');
        })->orderBy($sort, $order)->paginate($request->pageSize);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $input = $request->all();
        $input['password'] = bcrypt($request->password);
        return User::create($input);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        $input = $request->all();

        if ($request->password) {
            $input['password'] = bcrypt($request->password);
        }

        $user->update($input);
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        return $user->delete();
        return ['message' => 'Data berhasil dihapus'];
    }

    public function search(Request $request)
    {
        $user = User::when($request->nomor_kartu, function($q) use ($request) {
                return $q->where('nomor_kartu', 'like', '%'.$request->nomor_kartu);
            })->when($request->plat_nomor, function ($q) use ($request) {
                return $q->where('plat_nomor', $request->plat_nomor);
            })->where('status', $request->status)->first();

        return ($user) ? $user : response(['message' => 'Data tidak ditemukan'], 404);
    }
}
