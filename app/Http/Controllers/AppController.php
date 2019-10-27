<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
    public function index()
    {
        return view('layouts.app');
    }

    public function checkAuth(Request $request)
    {
        $operatorPath = [ '/home', '/parking-transaction', '/member' ];

        if ($request->user()->role == 0 && !in_array($request->route, $operatorPath)) {
            return response(['message' => 'Anda tidak berhak mengakses halaman ini'], 401);

        }

        return ['message' => 'ok'];
    }

    public function getNavigation(Request $request)
    {
        $nav = [
            ['label' => 'Home', 'icon' => 'el-icon-s-home', 'path' => 'home' ],
            ['label' => 'Transaksi', 'icon' => 'el-icon-document-copy', 'path' => 'parking-transaction' ],
        ];

        $adminNav = [
            ['label' => 'Laporan', 'icon' => 'el-icon-data-analysis', 'path' => 'report' ],
            ['label' => 'Notifikasi', 'icon' => 'el-icon-bell', 'path' => 'notification' ],
            ['label' => 'Setting', 'icon' => 'el-icon-setting', 'path' => 'setting' ],
        ];

        return $request->user()->role == 1 ? array_merge($nav, $adminNav) : $nav;
    }
}
