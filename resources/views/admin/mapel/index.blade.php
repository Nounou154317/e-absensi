@extends('layouts.app')
@section('title', 'Kelola Mata Pelajaran')

@section('content')
@include('partials._flash')

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0">Daftar Mata Pelajaran</h6>
        <a href="{{ route('admin.mapel.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Mapel
        </a>
    </div>
    <div class="card-body">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="Cari kode atau nama mata pelajaran...">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary flex-fill">Cari</button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.mapel.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">ID</th>
                        <th width="110">Kode</th>
                        <th>Nama</th>
                        <th>Keterangan</th>
                        <th>Guru Pengampu</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mapels as $m)
                    @php $gurus = $m->penugasan->pluck('guru')->filter()->unique('id'); @endphp
                    <tr>
                        <td>{{ $m->id }}</td>
                        <td><span class="badge bg-primary">{{ $m->kode }}</span></td>
                        <td>{{ $m->nama }}</td>
                        <td><small class="text-muted">{{ $m->keterangan ?: '-' }}</small></td>
                        <td>
                            @forelse($gurus as $g)
                                <span class="badge bg-light text-dark border">{{ $g->name }}</span>
                            @empty
                                <small class="text-muted">Belum ada guru</small>
                            @endforelse
                        </td>
                        <td>
                            <a href="{{ route('admin.mapel.edit', $m) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.mapel.destroy', $m) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus mata pelajaran {{ $m->nama }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada mata pelajaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $mapels->links() }}
    </div>
</div>
@endsection