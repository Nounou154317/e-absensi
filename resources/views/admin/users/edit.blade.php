@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" id="role" class="form-select" required onchange="toggleFields()">
                        <option value="admin"     {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guru"      {{ $user->role == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="siswa"     {{ $user->role == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="orang_tua" {{ $user->role == 'orang_tua' ? 'selected' : '' }}>Orang Tua Siswa</option>
                    </select>
                </div>
            </div>

            {{-- Field Guru --}}
            <div class="row" id="field-guru" style="display:none;">
                <div class="col-12 mb-3">
                    <div class="alert alert-info py-2 mb-0 small">
                        Mata pelajaran guru diatur di
                        <a href="{{ route('admin.penugasan.edit', $user) }}">menu Penugasan Guru</a>.
                    </div>
                </div>
            </div>

            {{-- Field Siswa --}}
            <div class="row" id="field-siswa" style="display:none;">
                <div class="col-md-4 mb-3">
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" class="form-control" value="{{ old('nis', $user->nis) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kelas</label>
                    <input type="text" name="kelas" class="form-control" value="{{ old('kelas', $user->kelas) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="L" {{ optional($user->siswa)->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ optional($user->siswa)->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            {{-- Field Orang Tua --}}
            <div class="row" id="field-ortu" style="display:none;">
                <div class="col-12 mb-3">
                    <label class="form-label">Anak yang dihubungkan
                        <small class="text-muted">(tahan Ctrl / Cmd untuk memilih lebih dari satu)</small>
                    </label>
                    @php $terpilih = old('anak_ids', $user->anak->pluck('id')->all()); @endphp
                    <select name="anak_ids[]" class="form-select @error('anak_ids') is-invalid @enderror" multiple size="8">
                        @foreach($siswaList as $s)
                            <option value="{{ $s->id }}" {{ in_array($s->id, $terpilih) ? 'selected' : '' }}>
                                {{ $s->nama }} — {{ $s->kelas }} (NIS {{ $s->nis }}){{ $s->ortu && $s->ortu_id !== $user->id ? ' • saat ini terhubung: ' . $s->ortu->name : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('anak_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr>
            <button class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleFields() {
    const role = document.getElementById('role').value;
    document.getElementById('field-guru').style.display  = (role === 'guru')      ? 'flex' : 'none';
    document.getElementById('field-siswa').style.display = (role === 'siswa')     ? 'flex' : 'none';
    document.getElementById('field-ortu').style.display  = (role === 'orang_tua') ? 'flex' : 'none';
}
toggleFields();
</script>
@endpush