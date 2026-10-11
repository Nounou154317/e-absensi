@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Absensi</h5>
        <div>
            <a href="{{ route('absensi.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-camera"></i> Absen Masuk
            </a>
            <a href="{{ route('absensi.pulang') }}" class="btn btn-sm btn-success">
                <i class="bi bi-box-arrow-right"></i> Absen Pulang
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Jam Masuk</th>
                        <th>Foto Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Foto Pulang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensis as $i => $a)
                    <tr>
                        <td>{{ $absensis->firstItem() + $i }}</td>
                        <td>{{ $a->tanggal }}</td>
                        <td>{{ $a->siswa->nis ?? '-' }}</td>
                        <td>{{ $a->siswa->nama ?? '-' }}</td>
                        <td>{{ $a->siswa->kelas ?? '-' }}</td>
                        <td>{{ $a->jam_masuk ?? '-' }}</td>
                        <td>
                            @if($a->foto_masuk)
                                <img src="{{ $a->foto_masuk }}" alt="Foto Masuk" class="foto-absen rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $a->jam_pulang ?? '-' }}</td>
                        <td>
                            @if($a->foto_pulang)
                                <img src="{{ $a->foto_pulang }}" alt="Foto Pulang" class="foto-absen rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $a->status == 'hadir' ? 'success' : 'warning' }}">
                                {{ ucfirst($a->status) }}
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('absensi.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Hapus data absensi ini?')">
                                @csrf 
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-3 text-muted">Belum ada data absensi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $absensis->links() }}
        </div>
    </div>
</div>
@endsection