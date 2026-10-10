@extends('layouts.app')
@section('title', $mapel->exists ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran')

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0">{{ $mapel->exists ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' }}</h6>
    </div>
    <div class="card-body">
        <form method="POST"
              action="{{ $mapel->exists ? route('admin.mapel.update', $mapel) : route('admin.mapel.store') }}">
            @csrf
            @if($mapel->exists) @method('PUT') @endif

            <div class="row">
                @if($mapel->exists)
                <div class="col-md-2 mb-3">
                    <label class="form-label">ID</label>
                    <input type="text" class="form-control" value="{{ $mapel->id }}" disabled>
                </div>
                @endif
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kode</label>
                    <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                           value="{{ old('kode', $mapel->kode) }}" maxlength="20" placeholder="cth: MTK" required>
                    @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-7 mb-3">
                    <label class="form-label">Nama Mata Pelajaran</label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama', $mapel->nama) }}" maxlength="100" placeholder="cth: Matematika" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Keterangan <small class="text-muted">(opsional)</small></label>
                    <textarea name="keterangan" rows="2" maxlength="255"
                              class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $mapel->keterangan) }}</textarea>
                    @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr>
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            <a href="{{ route('admin.mapel.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection