@extends('layouts.app')

@section('title', 'Kelola Siswa')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title mb-0">Kelola Data Siswa</h1>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
            <i class="bi bi-plus-lg"></i> Tambah Siswa
        </button>
    </div>

    {{-- Filter & Search --}}
    <div class="card card-flat mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.siswa') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama atau NIS..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="kelas" class="form-select form-select-sm">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary btn-sm w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Data Siswa --}}
    <div class="card card-flat">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-riwayat mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswas as $index => $siswa)
                            <tr>
                                <td>{{ $siswas->firstItem() + $index }}</td>
                                <td>{{ $siswa->nis ?? '-' }}</td>
                                <td><strong>{{ $siswa->nama ?? $siswa->name }}</strong></td>
                                <td>{{ $siswa->kelas ?? '-' }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm py-0 px-2" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm py-0 px-2" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Data siswa belum ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($siswas->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection