<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_penyusutan extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->dblocal = $this->load->database('dblocal', true);
    }

    public function get_all_data() {
        $this->dblocal->select('penyusutan.*, m_branch.name as lokasi, k_susut.susut_bln');
        $this->dblocal->from('penyusutan');
        $this->dblocal->join('m_branch', 'penyusutan.lokasi = m_branch.id_divisi', 'left');
        $this->dblocal->join('k_susut', 'penyusutan.kode_susut = k_susut.kode', 'left');
        return $this->dblocal->get()->result_array();
    }

    public function get_branch_options() {
        $this->dblocal->select('id_divisi, name');
        $query = $this->dblocal->get('m_branch');
        return $query->result_array();
    }

    public function generate_kode($jenis) {
        $prefix = 'MSRA/' . strtoupper($jenis) . '/';

        log_message('debug', 'Prefix yang digunakan: ' . $prefix);

        $query_kode = $this->dblocal->query("SELECT kode FROM penyusutan 
                WHERE kode LIKE '$prefix%' 
                ORDER BY kode DESC LIMIT 1");

        if (!$query_kode) {
            log_message('error', 'Query gagal: ' . $this->dblocal->error());
            return false;
        }

        if ($query_kode->num_rows() > 0) {
            $last_kode = $query_kode->row();
            $number = intval(substr($last_kode->kode, strrpos($last_kode->kode, '/') + 1));
            $number++;
        } else {
            log_message('debug', 'Tabel kosong, memulai nomor dari 1.');
            $number = 1;
        }

        $kode_baru = $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
        log_message('debug', 'Kode baru yang dihasilkan: ' . $kode_baru);
        return $kode_baru;
    }

    public function get_k_susut_options() {
        return $this->dblocal->select('kode, susut_bln')->get('k_susut')->result_array();
    }

    public function get_jenis_options() {
        $this->dblocal->select('id, nama_barang');
        $query = $this->dblocal->get('k_jenis_barang');
        return $query->result_array();
    }

    public function save_data($data_penyusutan) {
        $data_penyusutan['is_new'] = 1;
        $this->dblocal->trans_start();
        $this->dblocal->insert('penyusutan', $data_penyusutan);
        $this->dblocal->trans_complete();
        return $this->dblocal->trans_status();
    }

    public function get_grouped_data_by_jenis($jenis) {
        $this->dblocal->select('jenis, COUNT(*) as count, SUM(harga_beli) as total_harga_beli, SUM(susut_bln) as total_susut_bln, SUM(harga_penyusutan) as total_harga_penyusutan');
        if ($jenis) {
            $this->dblocal->where('jenis', $jenis);
        }
        $this->dblocal->group_by('jenis');
        return $this->dblocal->get('penyusutan')->result_array();
    }

    public function get_susut_bln($kode_susut) {
        $this->dblocal->select('susut_bln');
        $this->dblocal->where('kode', $kode_susut);
        $query = $this->dblocal->get('k_susut');
        if ($query->num_rows() > 0) {
            return $query->row()->susut_bln;
        }
        return false;
    }

    public function calculate_susut_bulan($bulan_beli, $susut_bln) {
        try {
            $bulan_sekarang = new DateTime();
            $bulan_beli = new DateTime($bulan_beli);
            $interval = $bulan_beli->diff($bulan_sekarang);
            $bulan_berjalan = min($interval->m + ($interval->y * 12) + 1, $susut_bln);
            return $bulan_berjalan . '/' . $susut_bln;
        } catch (Exception $e) {
            return '0/' . $susut_bln;
        }
    }
    
    public function calculate_rincian_penyusutan_per_bulan($harga_beli, $susut_bln) {
        if ($susut_bln <= 0 || $harga_beli <= 0) {
            return [];
        }
        
        $penyusutan_per_bulan = round($harga_beli / $susut_bln, 2); 
        $nilai_buku = $harga_beli;
        $hasil = [];
    
        for ($i = 1; $i <= $susut_bln; $i++) {
            $nilai_sebelum = $nilai_buku;
            
            $nilai_buku -= $penyusutan_per_bulan;
            if ($nilai_buku < 0) {
                $nilai_buku = 0;
            }
            
            $hasil[] = [
                'bulan' => $i,
                'nilai_sebelum' => $nilai_sebelum,
                'penyusutan_perbulan' => $penyusutan_per_bulan,
                'nilai_buku' => $nilai_buku,
            ];
        }
    
        return $hasil;
    }    

    public function get_penyusutan_detail($kode) {
        $this->dblocal->select('kode, harga_beli, bulan_beli, harga_penyusutan, disusut');
        $this->dblocal->where('kode', $kode);
        $query = $this->dblocal->get('penyusutan');
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    public function calculate_harga_penyusutan_per_bulan($harga_beli, $susut_bln) {
        if ($susut_bln <= 0 || $harga_beli <= 0) {
            return 0;
        }
        
        return round($harga_beli / $susut_bln, 2);
    }

    public function update_bulan($kode, $disusut) {
        $this->dblocal->where('kode', $kode);
        $this->dblocal->where('is_new', 0);
    

        return $this->dblocal->update('penyusutan', [
            'disusut' => $disusut,
            'last_updated_month' => date('Y-m-d')
        ]);
    }
    

    public function mark_as_old($kode) {
        $this->dblocal->where('kode', $kode);
        return $this->dblocal->update('penyusutan', ['is_new' => 0]);
    }

    public function check_and_reset_is_new() {
        $last_reset_month = $this->session->userdata('last_reset_month') ?? null;

        $current_month = date('Y-m');
    
        if ($last_reset_month !== $current_month) {
            $this->dblocal->query("
                UPDATE penyusutan 
                SET is_new = 0 
                WHERE DATE_FORMAT(bulan_beli, '%Y-%m') < '$current_month' AND is_new = 1
            ");

            $this->session->set_userdata('last_reset_month', $current_month);
    
            return $this->dblocal->affected_rows();
        }
    
        return 0;
    }  

    public function can_update_bulan($kode) {
        $this->dblocal->select('is_new, last_updated_month, disusut');
        $this->dblocal->where('kode', $kode);
        $query = $this->dblocal->get('penyusutan');
        
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $current_month = date('Y-m');
            
            // Check depreciation limit first
            $disusutParts = explode('/', $row->disusut);
            if ($disusutParts[0] == $disusutParts[1]) {
                return [
                    'can_update' => false,
                    'message' => 'Aset dengan kode ' . $kode . ' telah mencapai batas maksimal penyusutan (' . $row->disusut . ')'
                ];
            }
            
            // Then check monthly update status
            if ($row->is_new == 0 && date('Y-m', strtotime($row->last_updated_month)) == $current_month) {
                return [
                    'can_update' => false,
                    'message' => 'Aset dengan kode ' . $kode . ' sudah disusutkan untuk bulan ' . date('F Y', strtotime($row->last_updated_month))
                ];
            }
        }
        return ['can_update' => true];
    }
}