<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Utang extends CI_Controller
{
    public function index()
    {
        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT u.id, u.supplier_id, s.suplier_nama, u.tgl_transaksi, u.jml_transaksi, u.jml_dibayar, u.jml_kekurangan 
            FROM tbl_hutang u 
            JOIN tbl_suplier s ON u.supplier_id = s.suplier_id
            WHERE s.user_id = $userID")
            ->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function detail()
    {
        $utangID = $_POST['utangID'];
        $transaction = $this->db->query("SELECT u.id, u.beli_nofak, u.supplier_id, s.suplier_nama, u.tgl_transaksi, u.jml_transaksi, u.jml_dibayar, u.jml_kekurangan 
            FROM tbl_hutang u 
            JOIN tbl_suplier s ON u.supplier_id = s.suplier_id
            WHERE u.id = $utangID")
            ->row();

        $detailTransaction = $this->db->query("SELECT b.barang_nama, db.d_beli_harga, db.d_beli_jumlah, db.d_beli_total 
            FROM tbl_detail_beli db
            JOIN tbl_barang b ON db.d_beli_barang_id = b.barang_id
            WHERE db.d_beli_nofak = '$transaction->beli_nofak'")->result();

        $data = [
            'transaction' => $transaction,
            'detail' => $detailTransaction
        ];

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }
}
