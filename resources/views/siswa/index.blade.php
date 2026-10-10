@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between">
        <h5 class="mb-0">Data Siswa</h5>
        <a href="{{ route('siswa.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Tambah Siswa
        </a>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th>
                    <th>Jenis Kelamin</th><th>No HP</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswas as $i => $s)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $s->nis }}</td>
                    <td>{{ $s->nama }}</td>
                    <td>{{ $s->kelas }}</td>
                    <td>{{ $s->jenis_kelamin }}</td>
                    <td>{{ $s->no_hp ?? '-' }}</td>
                    <td>
                        <a href="{{ route('siswa.edit', $s->id) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('siswa.destroy', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection