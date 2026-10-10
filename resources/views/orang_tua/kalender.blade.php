@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Dashboard Orang Tua</h2>
            
            <div class="card">
                <div class="card-header bg-primary text-white">Daftar Anak</div>

                <div class="card-body">
                    @if(empty($anak) || is_bool($anak))
                        <p class="text-center text-muted">Belum ada data anak yang terhubung ke akun Anda.</p>
                    @else
                        <div class="list-group">
                            @php
                                $listAnak = is_iterable($anak) ? $anak : [$anak];
                            @endphp

                            @foreach($listAnak as $siswa)
                                @if(is_object($siswa))
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1"><strong>{{ $siswa->nama ?? $siswa->name ?? '-' }}</strong></h5>
                                            <small class="text-muted">
                                                NIS: {{ $siswa->nis ?? '-' }} | 
                                                Kelas: {{ is_object($siswa->kelas) ? $siswa->kelas->nama_kelas : ($siswa->kelas ?? '-') }}
                                            </small>
                                        </div>
                                        {{-- Menggunakan route orang_tua.izin yang sudah terdaftar di web.php --}}
                                        <a href="{{ route('orang_tua.izin') }}" class="btn btn-outline-primary btn-sm">
                                            Lihat Absensi &amp; Ajukan Izin
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection