<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
    protected $navs = [
        ['label' => 'Dashboard', 'icon' => 'el-icon-s-home', 'path' => '/', 'roles' => [1, 2] ],
        ['label' => 'Pos', 'icon' => 'el-icon-discount', 'path' => '/pos', 'roles' => [1, 2] ],
        ['label' => 'Karcis Hilang', 'icon' => 'el-icon-s-ticket', 'path' => '/karcis-hilang', 'roles' => [1, 2] ],
        ['label' => 'Buka Manual', 'icon' => 'el-icon-unlock', 'path' => '/buka-manual', 'roles' => [1, 2] ],
        ['label' => 'Log Akses', 'icon' => 'el-icon-document-copy', 'path' => '/access-log', 'roles' => [1, 2] ],
        // ['label' => 'Laporan', 'icon' => 'el-icon-data-analysis', 'path' => '/report', 'roles' => [1, 2] ],
        ['label' => 'Snapshot', 'icon' => 'el-icon-camera', 'path' => '/snapshot', 'roles' => [2] ],
        ['label' => 'Notifikasi', 'icon' => 'el-icon-bell', 'path' => '/notification', 'roles' => [2] ],
        ['label' => 'Kelola Department', 'icon' => 'el-icon-menu', 'path' => '/department', 'roles' => [2] ],
        ['label' => 'Kelola User', 'icon' => 'el-icon-user', 'path' => '/user', 'roles' => [2] ],
        ['label' => 'Setting', 'icon' => 'el-icon-setting', 'path' => '/setting', 'roles' => [2] ],
    ];

    public function index()
    {
        return view('layouts.app');
    }

    public function checkAuth(Request $request)
    {
        $navs = array_filter($this->navs, function($nav) use ($request) {
            return $nav['path'] == $request->route && in_array(auth()->user()->role, $nav['roles']);
        });

        if (count($navs) == 0) {
            return response(['message' => 'Anda tidak berhak mengakses halaman ini.'], 403);
        }

        return ['message' => 'ok'];
    }

    public function getNavigation(Request $request)
    {
        return array_filter($this->navs, function($nav) {
            return in_array(auth()->user()->role, $nav['roles']);
        });
    }
}
