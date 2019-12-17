<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\User;
use Illuminate\Http\Request;
use PDF;

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

        $users = User::where('name', '!=', 'system')->when($request->status, function($q) use ($request) {
            return $q->where('status', $request->status[0] == 'active' ? 1 : 0);
        })->when($request->expired, function($q) use ($request) {
            return $q->where('masa_aktif_kartu', $request->expired[0] == 'berlaku' ? '>=' : '<', date('Y-m-d'));
        })->when($request->keyword, function ($q) use ($request) {
            return $q->where(function($qq) use ($request) {
                return $qq->where('name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('nip', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('nomor_hp', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('plat_nomor', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('nomor_kartu', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->keyword . '%');
            });
        })->when(is_array($request->role), function($q) use ($request) {
            return $q->whereIn('role', $request->role);
        })->when($request->department_id, function($q) use ($request) {
            return $q->whereIn('department_id', $request->department_id);
        })
        ->orderBy($sort, $order)->paginate($request->pageSize);

        if ($request->action == 'print') {
            return view('pdf.daftar_user', ['users' => $users, 'action' => $request->action]);
        }

        if ($request->action == 'pdf') {
            $pdf = PDF::loadview('pdf.daftar_user', ['users' => $users, 'action' => $request->action]);
            return $pdf->download('daftar_user.pdf');
        }

        return $users;
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
        if ($user->name == 'system') {
            return response(['message' => 'User system tidak boleh dihapus'], 500);
        }

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
