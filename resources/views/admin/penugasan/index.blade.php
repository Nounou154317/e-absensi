@extends('layouts.app')
@section('title', 'Penugasan Guru')

@section('content')
@include('partials._flash')

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0">Penugasan Mata Pelajaran Guru</h6>
        <small class="text-muted">Setiap guru wajib mengampu 1–3 mata pelajaran</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Guru</th>
                        <th>Mata Pelajaran &amp; Kelas</th>
                        <th class="text-center" width="80">Mapel</th>
                        <th class="text-center" width="80">Kelas</th>
                        <th width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gurus as $i => $g)
                    @php $r = $g->ringkasanPenugasan(); @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $g->name }}</div>
                            <small class="text-muted">{{ $g->email }}</small>
                        </td>
                        <td>
                            @forelse($r['per_mapel'] as $pm)
                                <div class="mb-1">
                                    <span class="badge bg-primary">{{ $pm['mapel']->kode }}</span>
                                    <span>{{ $pm['mapel']->nama }}</span>
                                    <small class="text-muted">— {{ implode(', ', $pm['kelas']) }}</small>
                                </div>
                            @empty
                                <span class="badge bg-warning text-dark">Belum ditugaskan</span>
                            @endforelse
                        </td>
                        <td class="text-center">{{ $r['jumlah_mapel'] }} / 3</td>
                        <td class="text-center">{{ $r['jumlah_kelas'] }}</td>
                        <td>
                            <a href="{{ route('admin.penugasan.edit', $g) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square"></i> Atur
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada akun guru</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection