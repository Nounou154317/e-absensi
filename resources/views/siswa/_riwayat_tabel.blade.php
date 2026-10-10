{{-- Partial: tabel riwayat (diperbarui realtime) --}}
{{-- ============ TABEL RIWAYAT ============ --}}
<div class="card-flat">
    <div style="overflow-x: auto;">
    <table class="table-riwayat">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Foto Masuk</th>
                <th>Foto Pulang</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $r)
            @php
                $tgl         = \Carbon\Carbon::parse($r->tanggal);
                $isTerlambat = $r->jam_masuk && $r->jam_masuk > '07:00:00' && $r->status == 'hadir';
                $statusText  = $isTerlambat ? 'Terlambat' : ucfirst($r->status);
                $statusClass = $isTerlambat ? 'status-terlambat' : 'status-' . $r->status;
            @endphp
            <tr>
                <td>{{ $tgl->format('d/m/Y') }}</td>
                <td>{{ $tgl->translatedFormat('l') }}</td>
                <td>{{ $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '-' }}</td>
                <td>{{ $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '-' }}</td>
                <td>
                    @if($r->foto_masuk)
                        <img src="{{ asset('storage/' . $r->foto_masuk) }}"
                             class="foto-absen" alt="Foto masuk" loading="lazy"
                             onclick="showFoto('{{ asset('storage/' . $r->foto_masuk) }}', 'masuk', '{{ $tgl->format('d/m/Y') }}', '{{ $r->jam_masuk }}')">
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </td>
                <td>
                    @if($r->foto_pulang)
                        <img src="{{ asset('storage/' . $r->foto_pulang) }}"
                             class="foto-absen" alt="Foto pulang" loading="lazy"
                             onclick="showFoto('{{ asset('storage/' . $r->foto_pulang) }}', 'pulang', '{{ $tgl->format('d/m/Y') }}', '{{ $r->jam_pulang }}')">
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </td>
                <td><span class="badge-status {{ $statusClass }}">{{ $statusText }}</span></td>
                <td>
                    @if($isTerlambat)
                        <span style="color: #dc2626; font-style: italic; font-size: 11px;">Harap Tepat Waktu</span>
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                    Belum ada riwayat absensi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

@if($riwayat->hasPages())
<div class="mt-3">{{ $riwayat->links() }}</div>
@endif