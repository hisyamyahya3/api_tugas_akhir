<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Utang extends CI_Controller
{
    public function index()
    {
        $data = $this->db->query("SELECT s.suplier_nama, u.tgl_transaksi, u.jml_transaksi, u.jml_dibayar, u.jml_kekurangan FROM tbl_hutang u JOIN tbl_suplier s ON u.suplier_id = s.suplier_id;")->result();
    
        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }
}