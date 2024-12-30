<style>
    .custom-container {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 0.5rem;
        margin: 10px;
    }

    .btn {
        border-radius: 5px;
        padding: 8px 18px;
        position: relative;
        top: -190px;
        left: 320%;
    }

    .btn-simpan{
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px 24px;
        cursor: pointer;
        margin: 10px 0;
    }
    .btn-reset{
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px 24px;
        cursor: pointer;
        margin: 10px 0;
    }
    .btn-update-bulan {
        background-color: #6c757d;
        color: white;
        border: none;
        border-radius: 5px;
        margin-right: 10px;
    }

    .btn-update-bulan-container {
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }
</style>
<div class="content-wrapper">
	<div class="page-header page-header-light">
		<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
			<div class="d-flex">
				<div class="breadcrumb">
					<a href="<?php echo base_url('dashboard'); ?>" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Beranda</a>
					<span class="breadcrumb-item active"><?php echo $title; ?></span>
				</div>

				<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
			</div>

			<div class="header-elements d-none">
				<div class="breadcrumb justify-content-center">
					<a href="javascript:history.go(-1)" class="breadcrumb-elements-item">
						<i class="icon-upload icon-arrow-left52 mr-2"></i>
						Kembali
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="content">
        <div class="card">
            <div class="card-body">
                <div class="card-header header-elements-inline">                
                    <h4 lass="card-title" style="background-color: #87cefa; display: inline-block; padding: 5px 10px; line-height: 1.2; color: white;"><?php echo $title; ?></h4>
                </div>
                <hr style="margin-bottom: 30px;">
                <div class="row" style="margin-left: 10px;">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="recipient-name" class="control-label">Filter Jenis :</label>  
                        <select name="filter-jenis" id="filter-jenis" class="form-control">
                            <option value="">Pilih jenis</option>
                            <?php foreach ($jenis_data as $jenis): ?>
                                <option value="<?= $jenis['nama_barang']; ?>"><?= $jenis['nama_barang']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="custom-container">
                        <button class="btn btn-warning btn-update-bulan" id="updateBulanBtn" style="background-color: orange;">Update Bulan</button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#formModal">+ Tambah</button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" id="table-penyusutan">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Bulan Beli</th>
                                <th>Harga Beli</th>
                                <th>Di Susutkan</th>
                                <th>Kode Susut</th>
                                <th>Harga Penyusutan Perbulan</th>
                                <th>Lokasi</th>
                                <th>Tempat Barang</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($penyusutan_data)): ?>
                                <?php $no = 1; foreach ($penyusutan_data as $row): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= $row['kode']; ?></td>
                                        <td><?= $row['nama']; ?></td>
                                        <td><?= $row['jenis']; ?></td>
                                        <td><?= date('d F Y', strtotime($row['bulan_beli'])); ?></td>
                                        <td><?= number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                                        <td><?= $row['disusut']; ?></td>
                                        <td><?= $row['kode_susut']; ?></td>
                                        <td>
                                            <?php
                                            $harga_penyusutan_per_bulan = $this->M_penyusutan->calculate_harga_penyusutan_per_bulan($row['harga_beli'], $row['susut_bln']);
                                            echo number_format($harga_penyusutan_per_bulan, 0, ',', '.');
                                            ?>
                                        </td>
                                        <td><?= $row['lokasi']; ?></td>
                                        <td><?= $row['tempat_barang']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data penyusutan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Form -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">Form Penyusutan Aset</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-penyusutan" method="post">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Barang</label>
                        <input type="text" name="nama" id="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis" class="form-label">Jenis</label>
                        <select name="jenis" id="jenis" class="form-control" required>
                            <option value="">Pilih Jenis</option>
                            <?php foreach ($jenis_data as $jenis): ?>
                                <option value="<?= $jenis['nama_barang']; ?>"><?= $jenis['nama_barang']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="kode" class="form-label">Kode</label>
                        <input type="text" name="kode" id="kode" class="form-control" readonly placeholder="Kode akan otomatis diisi">
                    </div>
                    <div class="mb-3">
                        <label for="harga_beli" class="form-label">Harga Beli</label>
                        <input type="number" name="harga_beli" id="harga_beli" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="bulan_beli" class="form-label">Bulan Beli</label>
                        <input type="date" name="bulan_beli" id="bulan_beli" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="kode_susut" class="form-label">Kode Susut</label>
                        <select name="kode_susut" id="kode_susut" class="form-control" required>
                            <option value="">Pilih Kode Susut</option>
                            <?php foreach ($kode_susut_data as $kode): ?>
                                <option value="<?= $kode['kode']; ?>"><?= $kode['kode'] . ' - ' . $kode['susut_bln'] . ' bulan'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi</label>
                        <select name="lokasi" id="lokasi" class="form-control" required>
                            <option value="">Pilih Lokasi</option>
                            <?php foreach ($branch_options as $branch): ?>
                                <option value="<?= $branch['id_divisi']; ?>"><?= $branch['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tempat_barang" class="form-label">Tempat Barang</label>
                        <input type="text" name="tempat_barang" id="tempat_barang" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn-simpan btn-primary">Simpan</button>
                        <button type="reset" class="btn-reset btn-warning">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Rincian Penyusutan - Kode: <span id="detailKode"></span></h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>penyusutan ke</th>
                            <th>Bulan</th>
                            <th>Nilai Sebelum disusutkan</th>
                            <th>Nilai Penyusutan perbulan</th>
                            <th>Nilai Buku Perbulan</th>
                        </tr>
                    </thead>
                    <tbody id="detailTableBody"> 
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- script generate code dan save data -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('bulan_beli').value = today;
});

$(document).ready(function () {
    $('#jenis').change(function() {
        let jenis = $('#jenis').val();
        if (jenis) {
            $.ajax({
                url: '<?= base_url("akunting/penyusutan/generate_kode") ?>',
                method: 'POST',
                data: { jenis: jenis },
                success: function(response) {
                    try {
                        let data = JSON.parse(response);
                        if (data.status) {
                            $('#kode').val(data.kode);
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Terjadi kesalahan dalam memproses data', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal terhubung ke server', 'error');
                }
            });
        }
    });

    $('#form-penyusutan').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: '<?= base_url("akunting/penyusutan/save") ?>',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                try {
                    let data = JSON.parse(response);
                    Swal.fire({
                        icon: data.status ? 'success' : 'error',
                        title: data.status ? 'Berhasil' : 'Gagal',
                        text: data.message
                    }).then(() => {
                        if (data.status) {
                            location.reload(); 
                        }
                    });
                } catch (error) {
                    Swal.fire('Error', 'Gagal memproses data', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Tidak dapat terhubung ke server', 'error');
            }
        });
    });
});
</script>
<!-- script update bulan, detail penyusutan, dan sweetalert -->
<script>
$(document).ready(function () {
    var table = $('#table-penyusutan').DataTable({
        responsive: true,
        processing: true,
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
            search: "Pencarian:",
            lengthMenu: "Tampilkan MENU data",
            info: "Menampilkan START sampai END dari TOTAL data",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        pageLength: 10
    });

    $('#filter-jenis').change(function() {
        var jenis = $(this).val();
        table.search(jenis).draw();
    });

    $('#table-penyusutan tbody').on('click', 'td:nth-child(2)', function() {
        var kode = $(this).text();
        $('#detailKode').html('<strong>' + kode + '</strong>');
        
        $.ajax({
            url: '<?= base_url("akunting/penyusutan/get_penyusutan_detail") ?>',
            method: 'POST',
            data: { kode: kode },
            success: function(response) {
                try {
                    let data = JSON.parse(response);
                    if (data.status) {
                        let histori = data.data;
                        let detailTableBody = $('#detailTableBody');
                        detailTableBody.empty();

                        histori.forEach(function(item) {
                            detailTableBody.append(`
                                <tr>
                                    <td>${item.penyusutan_ke}</td>
                                    <td>${item.bulan}</td>
                                    <td>${item.nilai_sebelum.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}</td>
                                    <td>${item.penyusutan_perbulan.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}</td>
                                    <td>${item.nilai_buku.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}</td>
                                </tr>
                            `);
                        });
                        $('#detailModal').modal('show');
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Gagal memproses data', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Tidak dapat terhubung ke server', 'error');
            }
        });
    });

    $('#updateBulanBtn').on('click', function() {
        var rows = table.rows({ 'search': 'applied' }).nodes();
        var allUpdated = true;
        var messages = {
            success: [],
            validation: [],
            limit: [],
            error: []
        };

        $(rows).each(function() {
            var kode = $(this).find('td:eq(1)').text();
            var disusut = $(this).find('td:eq(6)').text();

            if (!kode || !disusut || !disusut.includes('/')) {
                messages.error.push('Data tidak valid untuk kode: ' + kode);
                allUpdated = false;
                return true;
            }

            $.ajax({
                url: '<?= base_url("akunting/penyusutan/update_bulan") ?>',
                method: 'POST',
                data: { kode: kode, disusut: disusut },
                async: false,
                success: function(response) {
                    try {
                        let data = JSON.parse(response);
                        if (data.status) {
                            messages.success.push('Data berhasil diupdate untuk kode: ' + kode);
                        } else {
                            allUpdated = false;
                            if (data.message.includes('sudah disusutkan untuk bulan')) {
                                messages.validation.push(data.message);
                            } else if (data.message.includes('batas maksimal')) {
                                messages.limit.push(data.message);
                            } else {
                                messages.error.push(data.message);
                            }
                        }
                    } catch (error) {
                        allUpdated = false;
                        messages.error.push('Gagal memproses data untuk kode: ' + kode);
                    }
                },
                error: function() {
                    allUpdated = false;
                    messages.error.push('Tidak dapat terhubung ke server untuk kode: ' + kode);
                }
            });
        });

        function showMessages() {
            if (messages.success.length > 0) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    html: messages.success.join('<br>'),
                    confirmButtonText: 'Oke'
                }).then(() => {
                    if (messages.validation.length > 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi Bulanan',
                            html: messages.validation.join('<br>'),
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            if (messages.limit.length > 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Batas Penyusutan',
                                    html: messages.limit.join('<br>'),
                                    confirmButtonText: 'Oke'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                location.reload();
                            }
                        });
                    } else if (messages.limit.length > 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Penyusutan',
                            html: messages.limit.join('<br>'),
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                });
            } else if (messages.error.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: messages.error.join('<br>')
                }).then(() => {
                    location.reload();
                });
            }
        }
        showMessages();
    });
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>