@extends('layouts.print')
@section('title', 'Log Akses Barrier Gate')

@section('content')
<h3 class="text-center">LOG AKSES BARRIER GATE</h3>
@if ($dateRange)
<h4 class="text-center">{{date('d-M-Y', strtotime($dateRange[0]))}} s.d. {{date('d-M-Y', strtotime($dateRange[1]))}}</h5>
@endif
<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th class="text-center">No Tiket</th>
            <th class="text-center">Plat Nomor</th>
            <th class="text-center">Nomor Kartu</th>
            <th class="text-center">Nama Staff</th>
            <th class="text-center">Waktu Masuk</th>
            <th class="text-center">Waktu Keluar</th>
            <th class="text-center">Durasi</th>
            <th class="text-center">Keterangan</th>
            <th class="text-center">Operator</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($logs as $l)
        <tr>
            <td class="text-center">{{$l->nomor_barcode}}</td>
            <td class="text-center">{{$l->plat_nomor}}</td>
            <td class="text-center">{{$l->nomor_kartu}}</td>
            <td>{{$l->user ? $l->user->name : ''}}</td>
            <td class="text-center">{{$l->time_in ? date('d-M-Y H:i:s', strtotime($l->time_in)) : ''}}</td>
            <td class="text-center">{{$l->time_out ? date('d-M-Y H:i:s', strtotime($l->time_out)) : ''}}</td>
            <td class="text-center">{{$l->durasi}}</td>
            <td>{{$l->keterangan}}</td>
            <td>{{$l->operator}}</td>
            <td class="text-center {{$l->time_out ? 'text-success' : 'text-danger'}}">{{$l->time_out ? 'SUDAH KELUAR' : 'PARKIR'}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
