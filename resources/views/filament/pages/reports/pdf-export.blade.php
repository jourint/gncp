@extends('layouts.pdf')

@section('content')
    @include("filament.pages.partials.report-{$active_report}", [
        'data' => $data,
        'selected_date' => $selected_date
    ])
@endsection