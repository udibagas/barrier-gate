<?php

namespace App\Http\Controllers;

use App\BarrierGate;
use App\Notifications\GateNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:2')->except(['store', 'unread']);
    }

    // khusus buat simpan notifikasi gate
    public function store(Request $request)
    {
        $request->validate([
            'barrier_gate_id' => 'required|exists:barrier_gates,id',
            'message' => 'required'
        ]);

        $gate = BarrierGate::find($request->barrier_gate_id);
        return $this->systemUser->notify(new GateNotification($gate, $request->message));
    }

    public function index(Request $request)
    {
        $sort = $request->sort ? $request->sort : 'updated_at';
        $order = $request->order == 'ascending' ? 'asc' : 'desc';

        return $this->systemUser->notifications()
            ->when($request->dateRange, function($q) use ($request) {
                return $q->whereRaw('DATE(created_at) BETWEEN ? AND ?', $request->dateRange);
            })->when($request->keyword, function ($q) use ($request) {
                return $q->where('type', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('data->message', 'LIKE', '%' . $request->keyword . '%');
            })->orderBy($sort, $order)->paginate($request->pageSize);
    }

    public function destroy($id)
    {
        $this->systemUser->notifications()->where('id', $id)->delete();
        return ['message' => 'Notifikasi telah dihapus'];
    }

    public function clear()
    {
        $this->systemUser->notifications()->delete();
        return ['message' => 'Notifikasi telah dihapus'];
    }

    public function unread()
    {
        return $this->systemUser->unreadNotifications;
    }

    public function markAsRead($id)
    {
        $this->systemUser->notifications()->where('id', $id)->update(['read_at' => now()]);
        return ['message' => 'ok'];
    }

    public function markAllAsRead()
    {
        $this->systemUser->unreadNotifications->markAsRead();
        return ['message' => 'ok'];
    }
}
