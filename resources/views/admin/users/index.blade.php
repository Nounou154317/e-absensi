@extends('layouts.app')
@section('title', 'Kelola User')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Daftar User</h6>
        <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah User
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>NIS / Kelas / Mapel / Anak</th>
                        <th width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $u)
                    <tr>
                        <td>{{ $users->firstItem() + $i }}</td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @php
                                $badge = ['admin' => 'danger', 'guru' => 'primary', 'siswa' => 'success', 'orang_tua' => 'warning'][$u->role] ?? 'secondary';
                                $labelRole = $u->role === 'orang_tua' ? 'Orang Tua' : ucfirst($u->role);
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $labelRole }}</span>
                        </td>
                        <td>
                            @if($u->role === 'siswa')
                                <small>{{ $u->nis }} / {{ $u->kelas }}</small>
                            @elseif($u->role === 'guru')
                                <small>{{ $u->mata_pelajaran }}</small>
                            @elseif($u->role === 'orang_tua')
                                <small>{{ $u->anak->pluck('nama')->implode(', ') ?: 'Belum ada anak terhubung' }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada user</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</div>
@endsection