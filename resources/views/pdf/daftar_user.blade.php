@extends('layouts.print')
@section('title', 'Daftar User')

@section('content')
<h3 class="text-center">DAFTAR USER</h3>
<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th class="text-left" style="width:40px">#</th>
            <th>Nama</th>
            <th class="text-center">NIP</th>
            <th>Departemen</th>
            <th class="text-center">Nomor Kartu</th>
            <th class="text-center">Plat Nomor</th>
            <th class="text-center" style="width:80px">Masa Aktif</th>
            <th class="text-center">Status Kartu</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $u)
        <tr>
            <td>{{$loop->index+1}}</td>
            <td>{{$u->name}}</td>
            <td class="text-center">{{$u->nip}}</td>
            <td>{{$u->department ? $u->department->nama : ''}}</td>
            <td class="text-center">{{$u->nomor_kartu}}</td>
            <td class="text-center">{{$u->plat_nomor}}</td>
            <td class="text-center">{{date('d-M-Y', strtotime($u->masa_aktif_kartu))}}</td>
            <td class="text-center {{$u->expired ? 'text-danger' : 'text-success'}}">{{$u->expired ? 'Kedaluarsa' : 'Berlaku'}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
