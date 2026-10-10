{{-- Partial: statistik & progress bar (diperbarui realtime) --}}
{{-- ============ STATISTIK 5 KOTAK ============ --}}
<div class="row g-2 mb-3">
    <div class="col">
        <div class="stat-box-big" style="background: #d1fae5;">
            <div class="num" style="color: #065f46;">{{ $statistik['hadir'] }}</div>
            <div class="lbl" style="color: #065f46;">Hadir</div>
        </div>
    </div>
    <div class="col">
        <div class="stat-box-big" style="background: #fef3c7;">
            <div class="num" style="color: #92400e;">{{ $statistik['terlambat'] }}</div>
            <div class="lbl" style="color: #92400e;">Terlambat</div>
        </div>
    </div>
    <div class="col">
        <div class="stat-box-big" style="background: #dbeafe;">
            <div class="num" style="color: #1e40af;">{{ $statistik['izin'] }}</div>
            <div class="lbl" style="color: #1e40af;">Izin</div>
        </div>
    </div>
    <div class="col">
        <div class="stat-box-big" style="background: #e9d5ff;">
            <div class="num" style="color: #6b21a8;">{{ $statistik['sakit'] }}</div>
            <div class="lbl" style="color: #6b21a8;">Sakit</div>
        </div>
    </div>
    <div class="col">
        <div class="stat-box-big" style="background: #fee2e2;">
            <div class="num" style="color: #991b1b;">{{ $statistik['alpha'] }}</div>
            <div class="lbl" style="color: #991b1b;">Alpha</div>
        </div>
    </div>
</div>

{{-- ============ PROGRESS BAR KEHADIRAN ============ --}}
<div class="mb-3">
    <div style="font-size: 12px; color: #64748b; margin-bottom: 6px;">
        Tingkat Kehadiran <strong>{{ $persentase }}%</strong> ({{ $statistik['hadir'] }} dari {{ $totalHari }} Hari sekolah)
    </div>
    <div style="height: 8px; background: #e2e8f0; border-radius: 20px; overflow: hidden;">
        <div style="height: 100%; width: {{ $persentase }}%; background: #10b981; border-radius: 20px; transition: width 0.6s;"></div>
    </div>
</div>