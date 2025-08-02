{{-- resources/views/docs/report_detail_page.blade.php --}}

@extends('docs.index')

@section('content')
    <div class="prose max-w-none">
        <h2 class="text-2xl font-bold mb-4">Detail Data Report</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div>
                <p class="font-semibold text-gray-700">ID Report:</p>
                <p>{{ $reportData->id_report ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Aktor:</p>
                <p>{{ $reportData->aktor ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Nama Report:</p>
                <p>{{ $reportData->nama_report ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="font-semibold text-gray-700">Keterangan:</p>
                <p class="prose max-w-none">{!! $reportData->keterangan ?? 'N/A' !!}</p>
            </div>
        </div>
    </div>
@endsection