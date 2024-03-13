<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Piutang extends CI_Controller
{
    public function index()
    {
        $data = $this->db->query("SELECT p.pelanggan_nama, c.tgl_transaksi, c.jml_transaksi, c.jml_dibayar, c.jml_kekurangan FROM tbl_piutang c JOIN tbl_pelanggan p ON p.pelanggan_id = c.id_pelanggan;")->result();
    
        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }
}