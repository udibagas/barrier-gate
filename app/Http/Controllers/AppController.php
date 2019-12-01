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
        $operatorPath = [ '/home', '/access-log' ];

        if ($request->user()->role == 0 && !in_array($request->route, $operatorPath)) {
            return response(['message' => 'Anda tidak berhak mengakses halaman ini'], 401);

        }

        return ['message' => 'ok'];
    }

    public function getNavigation(Request $request)
    {
        $nav = [
            ['label' => 'Dashboard', 'icon' => 'el-icon-s-home', 'path' => 'home' ],
            ['label' => 'Pos', 'icon' => 'el-icon-discount', 'path' => 'pos' ],
            ['label' => 'Karcis Hilang', 'icon' => 'el-icon-s-ticket', 'path' => 'karcis-hilang' ],
            ['label' => 'Buka Manual', 'icon' => 'el-icon-unlock', 'path' => 'buka-manual' ],
            ['label' => 'Log Akses', 'icon' => 'el-icon-document-copy', 'path' => 'access-log' ],
        ];

        $adminNav = [
            // ['label' => 'Laporan', 'icon' => 'el-icon-data-analysis', 'path' => 'report' ],
            ['label' => 'Snapshot', 'icon' => 'el-icon-camera', 'path' => 'snapshot' ],
            ['label' => 'Notifikasi', 'icon' => 'el-icon-bell', 'path' => 'notification' ],
            ['label' => 'Setting', 'icon' => 'el-icon-setting', 'path' => 'setting' ],
        ];

        return $request->user()->role == 1 ? array_merge($nav, $adminNav) : $nav;
    }
}
