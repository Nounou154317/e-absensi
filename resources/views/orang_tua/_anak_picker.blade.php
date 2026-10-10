@if($anakList->count() > 1)
<div class="card-flat mb-3" style="padding: 12px 16px;">
    <div style="font-size: 11px; color: #64748b; margin-bottom: 8px; font-weight: 600; text-transform: uppercase;">Pilih anak</div>
    <div class="d-flex flex-wrap gap-2">
        @foreach($anakList as $a)
            <a href="{{ request()->url() }}?anak={{ $a->id }}"
               class="anak-chip {{ $a->id === $anak->id ? 'active' : '' }}">
                {{ $a->nama }} <small>· {{ $a->kelas }}</small>
            </a>
        @endforeach
    </div>
</div>
@endif