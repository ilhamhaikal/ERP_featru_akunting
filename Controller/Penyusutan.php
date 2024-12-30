<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penyusutan extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('M_penyusutan');
        $this->M_penyusutan->check_and_reset_is_new();
    }

    public function penyusutan() {
        $data['title'] = "Data Penyusutan";
        $data['description'] = "Halaman Penyusutan";
        $data['content_view'] = 'akunting/penyusutan/penyusutan';
        $data['penyusutan_data'] = $this->M_penyusutan->get_all_data();
        $data['kode_susut_data'] = $this->M_penyusutan->get_k_susut_options();
        $data['jenis_data'] = $this->M_penyusutan->get_jenis_options();
        $data['branch_options'] = $this->M_penyusutan->get_branch_options();
        $this->template->dashboard_template($data);
    }

    public function generate_kode() {
        $jenis = $this->input->post('jenis');
    
        if (!$jenis) {
            echo json_encode(['status' => false, 'message' => 'Nama barang tidak boleh kosong.']);
            return;
        }
    
        log_message('debug', 'Jenis: ' . $jenis);
    
        $kode = $this->M_penyusutan->generate_kode($jenis);
    
        if (!$kode) {
            echo json_encode(['status' => false, 'message' => 'Gagal menghasilkan kode']);
            return;
        }
    
        echo json_encode(['status' => true, 'kode' => $kode]);
    }

    public function save() {
        $data_penyusutan = array(
            'nama' => $this->input->post('nama'),
            'jenis' => $this->input->post('jenis'),
            'harga_beli' => $this->input->post('harga_beli'),
            'bulan_beli' => $this->input->post('bulan_beli'),
            'lokasi' => $this->input->post('lokasi'),
            'tempat_barang' => $this->input->post('tempat_barang'),
            'kode_susut' => $this->input->post('kode_susut')
        );
    
        if (empty($data_penyusutan['nama']) || empty($data_penyusutan['jenis'])) {
            echo json_encode(['status' => false, 'message' => 'Nama dan jenis aset wajib diisi']);
            return;
        }
    
        $kode = $this->M_penyusutan->generate_kode($data_penyusutan['jenis']);
    
        if (!$kode) {
            echo json_encode(['status' => false, 'message' => 'Gagal menghasilkan kode']);
            return;
        }
    
        $data_penyusutan['kode'] = $kode;
    
        $susut_bln = $this->M_penyusutan->get_susut_bln($data_penyusutan['kode_susut']);
        if (!$susut_bln) {
            echo json_encode(['status' => false, 'message' => 'Kode susut tidak valid']);
            return;
        }
    
        $data_penyusutan['disusut'] = $this->M_penyusutan->calculate_susut_bulan($data_penyusutan['bulan_beli'], $susut_bln);
    
        $data_penyusutan['harga_penyusutan'] = $this->M_penyusutan->calculate_rincian_penyusutan_per_bulan($data_penyusutan['harga_beli'], $susut_bln);
        $data_penyusutan['harga_penyusutan'] = $this->M_penyusutan->calculate_harga_penyusutan_per_bulan($data_penyusutan['harga_beli'], $susut_bln);

        if (is_array($data_penyusutan['harga_penyusutan'])) {
            $data_penyusutan['harga_penyusutan'] = json_encode($data_penyusutan['harga_penyusutan']);
        }
    
        $status = $this->M_penyusutan->save_data($data_penyusutan);
    
        if ($status) {
            log_message('debug', 'Data penyusutan berhasil disimpan: ' . json_encode($data_penyusutan));
            echo json_encode(['status' => true, 'message' => 'Data penyusutan berhasil disimpan']);
        } else {
            log_message('error', 'Gagal menyimpan data penyusutan: ' . json_encode($data_penyusutan));
            echo json_encode(['status' => false, 'message' => 'Gagal menyimpan data penyusutan']);
        }
    }        

    public function get_penyusutan_detail() {
        $kode = $this->input->post('kode');
        $detail = $this->M_penyusutan->get_penyusutan_detail($kode);
        if ($detail) {
            $susutParts = explode('/', $detail['disusut']);
            $bulanDisusut = intval($susutParts[0]);
            $totalBulan = intval($susutParts[1]);

            $histori = [];
            $nilaiBuku = floatval($detail['harga_beli']);
            $hargaPenyusutan = floatval($detail['harga_penyusutan']);

            for ($i = 0; $i < $bulanDisusut; $i++) {
                $bulan = new DateTime($detail['bulan_beli']);
                $bulan->modify("+$i month");
                $bulanString = $bulan->format('F Y');

                $nilaiBukuPerbulan = $nilaiBuku - $hargaPenyusutan;
                if ($nilaiBukuPerbulan < 0) {
                    $nilaiBukuPerbulan = 0;
                }

                $histori[] = [
                    'bulan' => $bulanString,
                    'penyusutan_ke' => ($i + 1) . '/' . $totalBulan,
                    'nilai_sebelum' => $nilaiBuku,
                    'penyusutan_perbulan' => $hargaPenyusutan,
                    'nilai_buku' => $nilaiBukuPerbulan
                ];

                $nilaiBuku = $nilaiBukuPerbulan;
            }

            echo json_encode(['status' => true, 'data' => $histori]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
        }
    }

    public function get_harga_penyusutan_per_bulan() {
        $harga_beli = $this->input->post('harga_beli');
        $susut_bln = $this->input->post('susut_bln');

        if (!$harga_beli || !$susut_bln) {
            echo json_encode(['status' => false, 'message' => 'Harga beli dan susut bulan wajib diisi']);
            return;
        }

        $harga_penyusutan_per_bulan = $this->M_penyusutan->calculate_harga_penyusutan_per_bulan($harga_beli, $susut_bln);

        echo json_encode(['status' => true, 'harga_penyusutan_per_bulan' => $harga_penyusutan_per_bulan]);
    }

    public function update_bulan() {
        $kode = $this->input->post('kode');
        $disusut = $this->input->post('disusut');
    
        if (!$kode || !$disusut) {
            echo json_encode(['status' => false, 'message' => 'Kode dan disusut wajib diisi']);
            return;
        }
    
        $check_update = $this->M_penyusutan->can_update_bulan($kode);
        if (!$check_update['can_update']) {
            echo json_encode(['status' => false, 'message' => $check_update['message']]);
            return;
        }
    
        $disusutParts = explode('/', $disusut);
        $bulan = intval($disusutParts[0]);
        $totalBulan = intval($disusutParts[1]);
    
        if ($bulan >= $totalBulan) {
            echo json_encode([
                'status' => false, 
                'message' => 'Aset dengan kode ' . $kode . ' telah mencapai batas maksimal penyusutan (' . $disusut . ')'
            ]);
            return;
        }
    
        $bulan++;
        $status = $this->M_penyusutan->update_bulan($kode, $bulan . '/' . $totalBulan);
    
        if ($status) {
            echo json_encode(['status' => true, 'message' => 'Bulan penyusutan berhasil diperbarui']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal memperbarui bulan penyusutan']);
        }
    }
}