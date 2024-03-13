<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Penjualan extends CI_Controller
{
    public function insert()
    {
        $hargaPenjualan = $_POST['hargaPenjualan'];
        $jmlUang = $_POST['jmlUang'];
        $jmlKembalian = $_POST['jmlKembalian'];

        if ($jmlUang < $hargaPenjualan) {
            $this->db->trans_start();
            $this->db->query('INSERT INTO tbl_piutang (id_pelanggan, tgl_transaksi, jml_transaksi, jml_dibayar, jml_kekurangan) VALUES ()');
            $this->db->query('INSERT INTO tbl_jual (jual_nofak, jual_tanggal, jual_total, jual_jml_uang, jual_kembalian, jual_user_id, jual_keterangan, jual_id_pelanggan) VALUES ()');
            $this->db->query('INSERT INTO tbl_detail_jual (d_jual_nofak, d_jual_barang_id, d_jual_barang_nama, d_jual_barang_satuan, d_jual_barang_harpok, d_jual_barang_harjul, d_jual_qty, d_jual_diskon, d_jual_total) VALUES ()');
            $this->db->trans_complete();
        } else {
            $this->db->trans_start();
            $this->db->query('INSERT INTO tbl_jual (jual_nofak, jual_tanggal, jual_total, jual_jml_uang, jual_kembalian, jual_user_id, jual_keterangan, jual_id_pelanggan) VALUES ()');
            $this->db->query('INSERT INTO tbl_detail_jual (d_jual_nofak, d_jual_barang_id, d_jual_barang_nama, d_jual_barang_satuan, d_jual_barang_harpok, d_jual_barang_harjul, d_jual_qty, d_jual_diskon, d_jual_total) VALUES ()');
            $this->db->trans_complete();
        }
    }
}