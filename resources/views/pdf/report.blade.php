@extends('layouts.print')
@section('title', 'Daftar User')

@section('content')
<h3 class="text-center">LAPORAN AKSES BARRIER GATE</h3>
@if ($dateRange)
<h4 class="text-center">{{date('d-M-Y', strtotime($dateRange[0]))}} s.d. {{date('d-M-Y', strtotime($dateRange[1]))}}</h5>
@endif
<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th class="text-center">Tanggal</th>
            <th class="text-center">Staff</th>
            <th class="text-center">Tamu</th>
            <th class="text-center">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $d)
        <tr>
            <td class="text-center">{{date('d-M-Y', strtotime($d->tanggal))}}</td>
            <td class="text-center">{{$d->staff}}</td>
            <td class="text-center">{{$d->tamu}}</td>
            <td class="text-center">{{$d->tamu + $d->staff}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="text-center">TOTAL</td>
            <td class="text-center">{{array_reduce($data, function($prev, $curr) { return $prev + $curr->staff; }, 0)}}</td>
            <td class="text-center">{{array_reduce($data, function($prev, $curr) { return $prev + $curr->tamu; }, 0)}}</td>
            <td class="text-center">{{array_reduce($data, function($prev, $curr) { return $prev + $curr->tamu + $curr->staff; }, 0)}}</td>
        </tr>
    </tfoot>
</table>
@endsection
