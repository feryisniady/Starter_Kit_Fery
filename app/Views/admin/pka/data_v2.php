<?php
/**
 * View: spt/data.php — Versi perbaikan
 * Tambahan: Progress bar slot SPT + antrian LHP yang harus diselesaikan
 */
$id_irban = $this->session->userdata('login_session')['id_mst_unit_itda'];
// $slot_info dikirim dari controller (getInfoSlot)
$slot = $slot_info ?? ['terpakai' => 0, 'maks' => 3, 'sisa' => 3, 'persen' => 0, 'list_antrian' => []];
?>

<div class="content-wrapper">
    <div class="container-fluid flex-grow-1 container-p-y">
        <h4 class="fw-bold">
            <span class="text-muted fw-light">Data /</span>
            <?= strtoupper($this->uri->segment(1)) ?>
        </h4>

        <!-- ── Tombol Tambah + Info Slot ──────────────────────────────── -->
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">

            <?php if ($can_add['boleh']): ?>
                <a href="<?= base_url('spt/add') ?>" class="btn btn-primary btn-icon-split">
                    <span class="icon"><i class="bx bx-plus"></i></span>
                    <span class="fw-bold text">Tambah Surat Tugas</span>
                </a>
            <?php else: ?>
                <button class="btn btn-danger btn-icon-split"
                        onclick="Swal.fire({
                            icon:  'warning',
                            title: 'SPT Dibatasi',
                            html:  '<?= addslashes($can_add['pesan']) ?>'
                        })">
                    <span class="icon"><i class="bx bx-lock"></i></span>
                    <span class="fw-bold text">Tambah Surat Tugas</span>
                </button>
            <?php endif; ?>

            <!-- Progress bar slot SPT reguler -->
            <div style="min-width:260px;max-width:340px">
                <div class="d-flex justify-content-between mb-1" style="font-size:12px">
                    <span class="fw-bold text-secondary">Slot SPT Aktif</span>
                    <span class="fw-bold <?= $slot['terpakai'] >= $slot['maks'] ? 'text-danger' : ($slot['terpakai'] >= 2 ? 'text-warning' : 'text-success') ?>">
                        <?= $slot['terpakai'] ?> / <?= $slot['maks'] ?> terpakai
                    </span>
                </div>
                <div class="progress" style="height:10px;border-radius:6px">
                    <?php
                    $bar_class = $slot['persen'] >= 100 ? 'bg-danger' : ($slot['persen'] >= 66 ? 'bg-warning' : 'bg-success');
                    ?>
                    <div class="progress-bar <?= $bar_class ?>"
                         role="progressbar"
                         style="width:<?= $slot['persen'] ?>%"
                         aria-valuenow="<?= $slot['persen'] ?>"
                         aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div style="font-size:11px;color:#6c757d;margin-top:3px">
                    Investigasi &amp; ADTT tidak dihitung dalam kuota
                </div>
            </div>
        </div>

        <!-- ── Banner antrian LHP jika ada SPT yang harus diselesaikan ── -->
        <?php if (!empty($slot['list_antrian'])): ?>
        <div class="alert alert-warning d-flex align-items-start gap-2 py-2 mb-3" style="font-size:13px">
            <i class="bx bx-time-five fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Antrian LHP yang harus diselesaikan secara urut:</strong>
                <ol class="mb-0 mt-1 ps-3">
                    <?php foreach ($slot['list_antrian'] as $i => $antrian): ?>
                    <li>
                        <strong><?= esc($antrian['no_st']) ?></strong>
                        — <?= esc($antrian['nm_kegiatan']) ?>
                        (Tgl. <?= IndonesiaTgl($antrian['tgl_st']) ?>)
                        <?php if ($i === 0): ?>
                            <span class="badge bg-danger ms-1">↑ Harus diselesaikan duluan</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <?php endif; ?>

        <?= $this->session->flashdata('pesan'); ?>

        <!-- ── Tabel SPT ──────────────────────────────────────────────── -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered w-100" id="dataTable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <?php if (is_admin()): ?><th>Unit</th><?php endif; ?>
                                <th>Kegiatan</th>
                                <th>Nomor ST</th>
                                <th>Tgl ST</th>
                                <th>Tujuan</th>
                                <th>HP</th>
                                <th>Batas NHP</th>
                                <th>Batas LHP</th>
                                <th>Status NHP</th>
                                <th>Status LHP</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($surat_tugas as $st): ?>
                            <?php
                            $ada_lhp      = $this->db->get_where('laporan_hasil', ['id_st' => $st['id_st']])->num_rows() > 0;
                            $is_pengecualian = !empty($st['is_pengecualian']);
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <?php if (is_admin()): ?><td><?= $st['unit'] ?></td><?php endif; ?>
                                <td>
                                    <?= esc($st['kegiatan']) ?>
                                    <?php if ($is_pengecualian): ?>
                                        <span class="badge bg-info ms-1" title="Tidak dihitung dalam kuota 3 SPT">Pengecualian</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= esc($st['no_st']) ?></td>
                                <td class="text-center"><?= IndonesiaTgl($st['tgl_st']) ?></td>
                                <td><?= str_replace([',', '[', ']', "'", '_'], ', ', $st['tujuan']) ?></td>
                                <td class="text-center fw-bold"><?= $st['jlh_st'] ?> Hari</td>
                                <td class="text-center"><?= IndonesiaTgl($st['NPH']) ?></td>
                                <td class="text-center"><?= IndonesiaTgl($st['LHP']) ?></td>
                                <td class="text-center"><?= showDeadlineBadge($st['NPH']) ?></td>
                                <td class="text-center">
                                    <?php if ($ada_lhp): ?>
                                        <span class="badge bg-success"><i class="bx bx-check"></i> Sudah LHP</span>
                                    <?php else: ?>
                                        <?= showDeadlineBadge($st['LHP']) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $st['status'] == 1 ? 'success' : 'danger' ?>">
                                        <?= $st['status'] == 1 ? 'Approve' : 'Pending' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('word/cetakSPT/'.$st['id_st']) ?>"
                                           class="btn btn-warning" target="_blank" title="Cetak SPT">
                                            <i class="bx bx-file"></i>
                                        </a>
                                        <a href="<?= site_url('spt/det_st/'.encrypt_url($st['id_st'])) ?>"
                                           class="btn btn-primary" title="Detail">
                                            <i class="bx bx-detail"></i>
                                        </a>
                                        <?php if ($ada_lhp): ?>
                                            <button class="btn btn-secondary" disabled title="LHP sudah diupload">
                                                <i class="bx bx-check-circle"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= site_url('spt/upload_lhp/'.$st['id_st']) ?>"
                                               class="btn btn-success" title="Upload LHP">
                                                <i class="bx bx-upload"></i> LHP
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
