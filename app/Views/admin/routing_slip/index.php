<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$sptId      = $spt['id'];
$isAdm      = isAuditAdmin();
$canReview  = isKtInSpt($sptId) || isDalnisInSpt($sptId) || isPjInSpt($sptId) || $isAdm;
$totalSlips = count($slips);
$selesai    = count(array_filter($slips, fn($s) => $s['status'] === 'selesai'));
$pending    = count(array_filter($slips, fn($s) => !in_array($s['status'], ['selesai', 'dikembalikan'])));

$statusColor = [
    'draft'         => ['bg'=>'#f1f5f9','txt'=>'#475569','border'=>'#cbd5e1'],
    'kt_review'     => ['bg'=>'#fef9c3','txt'=>'#854d0e','border'=>'#fde047'],
    'dalnis_review' => ['bg'=>'#fff7ed','txt'=>'#9a3412','border'=>'#fdba74'],
    'pj_review'     => ['bg'=>'#eff6ff','txt'=>'#1e40af','border'=>'#93c5fd'],
    'selesai'       => ['bg'=>'#f0fdf4','txt'=>'#166534','border'=>'#86efac'],
    'dikembalikan'  => ['bg'=>'#fff1f2','txt'=>'#9f1239','border'=>'#fca5a5'],
];

$jenisIcon = \App\Models\RoutingSlipModel::JENIS_ICON;
$statusLabel = \App\Models\RoutingSlipModel::STATUS_LABEL;
?>

<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-route"></i> Routing Slip</h1>
        <p>SPT: <?= esc($spt['nomor_naskah'] ?: '#'.$sptId) ?> — <?= esc($spt['irban_nama']) ?></p>
    </div>
    <div class="page-actions">
        <?php if($canCreate): ?>
        <a href="/admin/spt/<?= $sptId ?>/routing-slip/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Routing Slip
        </a>
        <?php endif; ?>
        <a href="/admin/spt/<?= $sptId ?>/km" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke KM
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
<div class="alert-success-inline mb-3"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert-error-inline mb-3"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- Summary bar -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
    <div class="card" style="border-top:3px solid #6366f1">
        <div class="card-body" style="padding:14px 18px;display:flex;align-items:center;gap:14px">
            <div style="width:40px;height:40px;border-radius:8px;background:#eef2ff;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-route" style="color:#6366f1;font-size:16px"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#1e293b;line-height:1"><?= $totalSlips ?></div>
                <div style="font-size:11px;color:#64748b;margin-top:2px">Total Routing Slip</div>
            </div>
        </div>
    </div>
    <div class="card" style="border-top:3px solid #f59e0b">
        <div class="card-body" style="padding:14px 18px;display:flex;align-items:center;gap:14px">
            <div style="width:40px;height:40px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-clock" style="color:#f59e0b;font-size:16px"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#1e293b;line-height:1"><?= $pending ?></div>
                <div style="font-size:11px;color:#64748b;margin-top:2px">Menunggu Review</div>
            </div>
        </div>
    </div>
    <div class="card" style="border-top:3px solid #22c55e">
        <div class="card-body" style="padding:14px 18px;display:flex;align-items:center;gap:14px">
            <div style="width:40px;height:40px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-circle-check" style="color:#22c55e;font-size:16px"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#1e293b;line-height:1"><?= $selesai ?></div>
                <div style="font-size:11px;color:#64748b;margin-top:2px">Selesai</div>
            </div>
        </div>
    </div>
</div>

<?php if(empty($slips)): ?>
<div class="card">
    <div class="card-body" style="text-align:center;padding:48px 20px;color:#94a3b8">
        <i class="fas fa-route" style="font-size:48px;margin-bottom:16px;display:block;color:#cbd5e1"></i>
        <div style="font-size:15px;font-weight:600;margin-bottom:8px;color:#64748b">Belum ada routing slip</div>
        <div style="font-size:13px;margin-bottom:20px">Buat routing slip pertama untuk memulai alur review dokumen.</div>
        <?php if($canCreate): ?>
        <a href="/admin/spt/<?= $sptId ?>/routing-slip/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Routing Slip
        </a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>

<div style="display:flex;flex-direction:column;gap:14px">
<?php foreach($slips as $slip):
    $sc     = $statusColor[$slip['status']] ?? $statusColor['draft'];
    $prog   = \App\Models\RoutingSlipModel::getProgress($slip);
    $isRet  = $slip['status'] === 'dikembalikan';
    $canRev = \App\Models\RoutingSlipModel::canReview($slip, $sptId);
    $icon   = $jenisIcon[$slip['jenis_dokumen'] ?? ''] ?? 'fa-folder';
?>
<div class="card slip-card" data-id="<?= $slip['id'] ?>" style="border-left:4px solid <?= $sc['border'] ?>">
    <div class="card-body" style="padding:16px 18px">

        <!-- Row 1: judul + badge status + aksi -->
        <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:14px">
            <div style="width:38px;height:38px;border-radius:8px;background:<?= $sc['bg'] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas <?= $icon ?>" style="color:<?= $sc['txt'] ?>;font-size:15px"></i>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:14px;color:#1e293b;margin-bottom:3px">
                    <?= esc($slip['judul']) ?>
                </div>
                <div style="font-size:11px;color:#64748b">
                    Oleh <strong><?= esc($slip['pengirim_nama'] ?? '-') ?></strong>
                    &nbsp;·&nbsp;
                    <?= $slip['tanggal_kirim'] ? date('d M Y H:i', strtotime($slip['tanggal_kirim'])) : '-' ?>
                    <?php if($slip['jenis_dokumen']): ?>
                    &nbsp;·&nbsp;
                    <span style="background:#e0e7ff;color:#3730a3;padding:1px 7px;border-radius:4px;font-size:10px;font-weight:600">
                        <?= esc(\App\Models\RoutingSlipModel::JENIS_LABEL[$slip['jenis_dokumen']] ?? $slip['jenis_dokumen']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                <span style="background:<?= $sc['bg'] ?>;color:<?= $sc['txt'] ?>;border:1px solid <?= $sc['border'] ?>;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap">
                    <?php if($isRet): ?>
                    <i class="fas fa-rotate-left"></i>
                    <?php endif; ?>
                    <?= $statusLabel[$slip['status']] ?? $slip['status'] ?>
                </span>

                <!-- Aksi -->
                <div style="display:flex;gap:6px">
                    <?php if($canRev): ?>
                    <button type="button" class="btn btn-xs btn-primary btn-review"
                        data-id="<?= $slip['id'] ?>"
                        data-judul="<?= esc($slip['judul']) ?>"
                        data-stage="<?= \App\Models\RoutingSlipModel::getCurrentStage($slip) ?>"
                        data-url="/admin/spt/<?= $sptId ?>/routing-slip/<?= $slip['id'] ?>/review">
                        <i class="fas fa-stamp"></i> Review
                    </button>
                    <?php endif; ?>
                    <?php if(\App\Models\RoutingSlipModel::canEdit($slip) && ($slip['pengirim_sdm_id'] == getCurrentSdmId() || $isAdm)): ?>
                    <a href="/admin/spt/<?= $sptId ?>/routing-slip/<?= $slip['id'] ?>/edit" class="btn btn-xs btn-secondary">
                        <i class="fas fa-pen"></i>
                    </a>
                    <?php endif; ?>
                    <a href="/admin/spt/<?= $sptId ?>/routing-slip/<?= $slip['id'] ?>/print" target="_blank" class="btn btn-xs btn-success">
                        <i class="fas fa-print"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Row 2: Visual Pipeline -->
        <div class="pipeline-wrap">
            <?php
            $stages = [
                ['key'=>'kt',     'label'=>'Ketua Tim',    'icon'=>'fa-user-tie'],
                ['key'=>'dalnis', 'label'=>'Dalnis',       'icon'=>'fa-user-shield'],
                ['key'=>'pj',     'label'=>'PJ / Daltu',  'icon'=>'fa-user-check'],
                ['key'=>'done',   'label'=>'Selesai',      'icon'=>'fa-circle-check'],
            ];
            foreach($stages as $si => $stage):
                $stageKey = $stage['key'];
                if($stageKey === 'done') {
                    $stDone  = $slip['status'] === 'selesai';
                    $stActive = false;
                    $stRet    = false;
                    $stPend   = !$stDone;
                    $byName   = '';
                    $byDate   = '';
                } else {
                    $stStatus = $slip["{$stageKey}_status"] ?? 'pending';
                    $stDone   = $stStatus === 'diterima';
                    $stRet    = $stStatus === 'dikembalikan';
                    $stActive = ($slip['status'] === "{$stageKey}_review");
                    $stPend   = !$stDone && !$stRet && !$stActive;
                    $byName   = $slip["{$stageKey}_nama"] ?? '';
                    $byDate   = $slip["{$stageKey}_tanggal"] ?? '';
                }

                $circColor  = $stDone  ? '#22c55e' : ($stRet ? '#ef4444' : ($stActive ? '#f59e0b' : '#e2e8f0'));
                $circTxt    = $stDone  ? '#fff'     : ($stRet ? '#fff'    : ($stActive ? '#fff'    : '#94a3b8'));
                $labelTxt   = $stDone  ? '#166534'  : ($stRet ? '#9f1239' : ($stActive ? '#854d0e' : '#94a3b8'));
            ?>
            <div class="pipeline-step <?= $stDone ? 'done' : ($stRet ? 'ret' : ($stActive ? 'active' : 'pend')) ?>">
                <div class="pipeline-circle" style="background:<?= $circColor ?>;color:<?= $circTxt ?>">
                    <?php if($stDone): ?>
                        <i class="fas fa-check" style="font-size:12px"></i>
                    <?php elseif($stRet): ?>
                        <i class="fas fa-rotate-left" style="font-size:11px"></i>
                    <?php elseif($stActive): ?>
                        <i class="fas fa-hourglass-half" style="font-size:11px"></i>
                    <?php else: ?>
                        <i class="fas <?= $stage['icon'] ?>" style="font-size:11px"></i>
                    <?php endif; ?>
                </div>
                <div class="pipeline-label" style="color:<?= $labelTxt ?>">
                    <div style="font-weight:<?= ($stDone||$stActive||$stRet) ? '700' : '500' ?>;font-size:11px">
                        <?= $stage['label'] ?>
                    </div>
                    <?php if($byName): ?>
                    <div style="font-size:10px;color:#64748b;margin-top:1px"><?= esc($byName) ?></div>
                    <?php endif; ?>
                    <?php if($byDate): ?>
                    <div style="font-size:10px;color:#94a3b8"><?= date('d/m H:i', strtotime($byDate)) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($si < 3): ?>
            <div class="pipeline-line <?= ($prog > $si) ? 'done' : '' ?>"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Catatan jika dikembalikan -->
        <?php
        $catatanRet = '';
        foreach(['pj','dalnis','kt'] as $s) {
            if($slip["{$s}_status"] === 'dikembalikan' && $slip["{$s}_catatan"]) {
                $catatanRet = $slip["{$s}_catatan"];
                break;
            }
        }
        ?>
        <?php if($isRet && $catatanRet): ?>
        <div style="margin-top:10px;background:#fff1f2;border:1px solid #fca5a5;border-radius:6px;padding:8px 12px;font-size:12px;color:#9f1239">
            <i class="fas fa-circle-exclamation"></i>
            <strong>Catatan reviewer:</strong> <?= esc($catatanRet) ?>
        </div>
        <?php endif; ?>

    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal Review -->
<div id="modal-review" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;width:460px;max-width:95vw;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <div style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:4px">
            <i class="fas fa-stamp" style="color:#6366f1"></i> Review Routing Slip
        </div>
        <div id="modal-judul" style="font-size:12px;color:#64748b;margin-bottom:20px"></div>

        <div class="form-group" style="margin-bottom:16px">
            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;display:block">Catatan (opsional)</label>
            <textarea id="review-catatan" class="form-control" rows="3" placeholder="Catatan untuk pengirim..."></textarea>
        </div>

        <div style="display:flex;gap:10px">
            <button type="button" class="btn btn-success" id="btn-terima" style="flex:1">
                <i class="fas fa-check"></i> Terima & Teruskan
            </button>
            <button type="button" class="btn btn-danger" id="btn-kembalikan" style="flex:1">
                <i class="fas fa-rotate-left"></i> Kembalikan
            </button>
        </div>
        <button type="button" id="btn-cancel-review" class="btn btn-secondary" style="width:100%;margin-top:10px">Batal</button>
    </div>
</div>

<style>
.pipeline-wrap {
    display:flex;align-items:flex-start;gap:0;
}
.pipeline-step {
    display:flex;flex-direction:column;align-items:center;gap:4px;flex:1;position:relative;
}
.pipeline-circle {
    width:32px;height:32px;border-radius:50%;display:flex;align-items:center;
    justify-content:center;font-weight:700;flex-shrink:0;
    box-shadow:0 1px 4px rgba(0,0,0,.12);transition:all .2s;
}
.pipeline-label {
    text-align:center;max-width:90px;
}
.pipeline-line {
    flex:1;height:2px;background:#e2e8f0;margin-top:16px;align-self:flex-start;
    transition:background .3s;
}
.pipeline-line.done {
    background:#22c55e;
}
.slip-card { transition:box-shadow .2s; }
.slip-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.08); }
</style>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
let reviewUrl = '', reviewSlipId = 0;

$(document).on('click', '.btn-review', function() {
    const btn = $(this);
    reviewUrl    = btn.data('url');
    reviewSlipId = btn.data('id');
    const stage  = btn.data('stage');
    const judul  = btn.data('judul');

    const stageLabel = {kt:'Ketua Tim', dalnis:'Pengendali Teknis', pj:'Penanggung Jawab'};
    $('#modal-judul').text(`"${judul}" — giliran ${stageLabel[stage] || stage}`);
    $('#review-catatan').val('');
    $('#modal-review').css('display','flex');
});

$('#btn-cancel-review, #modal-review').on('click', function(e) {
    if (e.target === this) $('#modal-review').hide();
});

$('#btn-terima, #btn-kembalikan').on('click', function() {
    const action  = $(this).attr('id') === 'btn-terima' ? 'diterima' : 'dikembalikan';
    const catatan = $('#review-catatan').val();
    const btn     = $(this);

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url   : reviewUrl,
        method: 'POST',
        data  : { action, catatan, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
        success: function(res) {
            if (res.success) {
                $('#modal-review').hide();
                // Reload halaman agar pipeline terupdate
                location.reload();
            } else {
                alert(res.message || 'Terjadi kesalahan.');
                btn.prop('disabled', false);
            }
        },
        error: function() {
            alert('Terjadi kesalahan. Coba lagi.');
            btn.prop('disabled', false);
        },
        complete: function() {
            $('#btn-terima').html('<i class="fas fa-check"></i> Terima & Teruskan');
            $('#btn-kembalikan').html('<i class="fas fa-rotate-left"></i> Kembalikan');
        }
    });
});
</script>
<?= $this->endSection() ?>
