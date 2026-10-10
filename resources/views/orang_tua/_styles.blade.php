<style>
    /* status tambahan (melengkapi status-* yang sudah ada di layout) */
    .status-menunggu  { background: #fef3c7; color: #92400e; }
    .status-disetujui { background: #d1fae5; color: #065f46; }
    .status-ditolak   { background: #fee2e2; color: #991b1b; }
    .status-libur     { background: #f1f5f9; color: #64748b; }
    .status-mendatang { background: #f8fafc; color: #94a3b8; }
    .status-kosong    { background: #f1f5f9; color: #94a3b8; }
    .badge-lg { font-size: 14px !important; padding: 6px 16px !important; }

    /* tombol */
    .btn-ajukan {
        display: inline-block; background: #fff; color: #4338ca; font-weight: 700;
        font-size: 13px; padding: 9px 18px; border-radius: 8px; text-decoration: none; border: 0;
    }
    .btn-ajukan:hover { background: #eef2ff; color: #3730a3; }
    .btn-x {
        display: inline-block; background: #4338ca; color: #fff; border: 0; border-radius: 8px;
        padding: 9px 20px; font-weight: 600; font-size: 13px; cursor: pointer; text-decoration: none;
    }
    .btn-x:hover { background: #3730a3; color: #fff; }
    .btn-x.outline { background: #fff; color: #4338ca; border: 1.5px solid #4338ca; }
    .btn-x.outline:hover { background: #4338ca; color: #fff; }
    .btn-x.danger  { background: #ef4444; }
    .btn-x.danger:hover { background: #dc2626; }
    .btn-x.success { background: #10b981; }
    .btn-x.success:hover { background: #059669; }
    .btn-x.sm { padding: 5px 12px; font-size: 12px; }
    .banner-right { display: flex; align-items: center; }

    /* pilih anak */
    .anak-chip {
        padding: 7px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;
        background: #fff; color: #475569; border: 1px solid #e2e8f0; text-decoration: none;
    }
    .anak-chip small { font-weight: 400; color: #94a3b8; }
    .anak-chip:hover { border-color: #c7d2fe; color: #4338ca; }
    .anak-chip.active { background: #4338ca; color: #fff; border-color: #4338ca; }
    .anak-chip.active small { color: #c7d2fe; }

    /* info siswa & persentase */
    .info-row { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
    .info-row:last-child { border-bottom: 0; }
    .info-row span { color: #64748b; }
    .info-row strong { color: #0f172a; text-align: right; }
    .pct-big { font-size: 38px; font-weight: 800; line-height: 1; }
    .kategori-chip { display: inline-block; padding: 4px 14px; border-radius: 14px; font-size: 12px; font-weight: 700; }
    .pct-bar { height: 8px; background: #e2e8f0; border-radius: 20px; overflow: hidden; margin: 12px 0 8px; }
    .pct-bar > div { height: 100%; border-radius: 20px; transition: width .6s; }

    /* ringkasan 4 kotak */
    .sum-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
    .sum-grid .stat-box { padding: 16px 8px; }
    .sum-grid .stat-box .num { font-size: 28px; }
    .sum-grid .stat-box .lbl { font-size: 12px; margin-top: 6px; font-weight: 600; }
    .stat-box.izin { background: #dbeafe; color: #1e40af; }

    /* filter */
    .filter-bar { display: flex; flex-wrap: wrap; gap: 10px; padding: 14px 18px; border-bottom: 1px solid #f1f5f9; align-items: flex-end; background: #fcfcfd; }
    .filter-bar label { display: block; font-size: 11px; color: #64748b; font-weight: 600; margin-bottom: 4px; }
    .f-input {
        width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; color: #334155; background: #fff; outline: none;
    }
    .f-input:focus { border-color: #4338ca; box-shadow: 0 0 0 3px rgba(67,56,202,.1); }
    .filter-bar > div { min-width: 140px; flex: 1 1 140px; }
    .filter-bar .actions { display: flex; gap: 8px; flex: 0 0 auto; min-width: 0; }

    /* foto absensi */
    .foto-absen {
        width: 46px; height: 46px; object-fit: cover; border-radius: 8px;
        border: 2px solid #e2e8f0; cursor: pointer; transition: .2s;
    }
    .foto-absen:hover { transform: scale(1.12); border-color: #4338ca; }

    /* kalender */
    .kal-head, .kal-week { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
    .kal-head div { text-align: center; font-size: 11px; font-weight: 700; color: #64748b; padding: 6px 0; }
    .kal-week { margin-bottom: 6px; }
    .kal-cell {
        min-height: 66px; border-radius: 8px; padding: 6px 8px; font-size: 12px;
        border: 1px solid #e2e8f0; background: #fff;
        display: flex; flex-direction: column; justify-content: space-between;
    }
    .kal-cell.out { visibility: hidden; }
    .kal-cell .d { font-weight: 700; }
    .kal-cell .t { font-size: 10px; font-weight: 600; }
    .kal-cell.today { outline: 2px solid #4338ca; outline-offset: 1px; }
    .legend { display: flex; flex-wrap: wrap; gap: 8px 14px; margin-bottom: 14px; font-size: 12px; color: #475569; }
    .legend i { display: inline-block; width: 12px; height: 12px; border-radius: 3px; margin-right: 5px; vertical-align: -1px; }
    .nav-btn {
        padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600;
        border: 1px solid #e2e8f0; background: #fff; color: #475569; text-decoration: none;
    }
    .nav-btn:hover { border-color: #c7d2fe; color: #4338ca; }

    .card-flat .card-body.p-pad { padding: 16px 18px; }
    .tbl-scroll { overflow-x: auto; }

    @media (max-width: 768px) {
        .sum-grid { grid-template-columns: repeat(2, 1fr); }
        .pct-big { font-size: 32px; }
        .kal-cell { min-height: 46px; padding: 4px 5px; }
        .kal-cell .t { display: none; }
        .filter-bar > div { flex: 1 1 45%; }
        .filter-bar .actions { flex: 1 1 100%; }
    }
</style>