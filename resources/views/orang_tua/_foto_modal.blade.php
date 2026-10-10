<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-body p-4 text-center">
                <h5 id="fotoTitle" style="font-weight: 700; color: #0f172a; font-size: 16px; margin-bottom: 4px;"></h5>
                <p id="fotoSubtitle" style="color: #64748b; font-size: 12px; margin-bottom: 16px;"></p>
                <img id="fotoLarge" src="" alt="Foto Absen" style="width: 100%; border-radius: 12px; display: block;">
                <button class="btn btn-secondary btn-sm mt-3" data-bs-dismiss="modal" style="border-radius: 8px; padding: 8px 24px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showFoto(src, tipe, tanggal, jam) {
        document.getElementById('fotoLarge').src = src;
        document.getElementById('fotoTitle').textContent = 'Foto Absen ' + tipe.charAt(0).toUpperCase() + tipe.slice(1);
        document.getElementById('fotoSubtitle').textContent = tanggal + ' • ' + (jam ? jam.substring(0, 5) : '-');
        new bootstrap.Modal(document.getElementById('modalFoto')).show();
    }
</script>
@endpush