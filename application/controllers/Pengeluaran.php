<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Pengeluaran extends CI_Controller
{
    public function index()
    {
        $data = $this->db->query("SELECT * FROM tbl_pengeluaran")->result();
    
        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function insert()
    {
        $uraian = $_POST['uraian'];
        $nominal = $_POST['nominal'];

        $input = $this->db->query("INSERT INTO tbl_pengeluaran (uraian, nominal) VALUES ('$uraian', '$nominal')");

        if ($input) {
            $hasil = [
                'status' => 'ok',
                'keterangan' => 'data berhasil ditambahkan'
            ];
        } else {
            $hasil = [
                'status' => 'gagal',
                'keterangan' => 'data gagal ditambahkan'
            ];
        }

        echo json_encode($hasil);
    }

    public function delete()
    {
        $id_pengeluaran = $_POST['id_pengeluaran'];

        $input = $this->db->query("DELETE FROM tbl_pengeluaran WHERE id_pengeluaran = $id_pengeluaran");

        if ($input) {
            $hasil = [
                'status' => 'ok',
                'keterangan' => 'data berhasil dihapus'
            ];
        } else {
            $hasil = [
                'status' => 'gagal',
                'keterangan' => 'data gagal dihapus'
            ];
        }

        echo json_encode($hasil);
    }
}