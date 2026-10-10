@extends('layouts.app')
@section('title', 'Tambah User')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" id="role" class="form-select" required onchange="toggleFields()">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin"     {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guru"      {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="siswa"     {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="orang_tua" {{ old('role') == 'orang_tua' ? 'selected' : '' }}>Orang Tua Siswa</option>
                    </select>
                </div>
            </div>

            {{-- Field Guru --}}
            <div class="row" id="field-guru" style="display:none;">
                <div class="col-12 mb-3">
                    <div class="alert alert-info py-2 mb-0 small">
                        Mata pelajaran dan kelas yang diajar diatur di menu <strong>Penugasan Guru</strong>.
                        Setelah akun disimpan, Anda langsung diarahkan ke sana. Guru wajib memiliki 1–3 mata pelajaran.
                    </div>
                </div>
            </div>

            {{-- Field Siswa --}}
            <div class="row" id="field-siswa" style="display:none;">
                <div class="col-md-4 mb-3">
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" class="form-control" value="{{ old('nis') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kelas</label>
                    <input type="text" name="kelas" class="form-control" value="{{ old('kelas') }}" placeholder="cth: XII IPA 1">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            {{-- Field Orang Tua --}}
            <div class="row" id="field-ortu" style="display:none;">
                <div class="col-12 mb-3">
                    <label class="form-label">Anak yang dihubungkan
                        <small class="text-muted">(tahan Ctrl / Cmd untuk memilih lebih dari satu)</small>
                    </label>
                    <select name="anak_ids[]" class="form-select @error('anak_ids') is-invalid @enderror" multiple size="8">
                        @foreach($siswaList as $s)
                            <option value="{{ $s->id }}" {{ in_array($s->id, old('anak_ids', [])) ? 'selected' : '' }}>
                                {{ $s->nama }} — {{ $s->kelas }} (NIS {{ $s->nis }}){{ $s->ortu ? ' • saat ini terhubung: ' . $s->ortu->name : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('anak_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr>
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
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