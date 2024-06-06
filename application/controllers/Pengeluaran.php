<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Pengeluaran extends CI_Controller
{
    public function index()
    {
        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT * FROM tbl_pengeluaran WHERE user_id = $userID")->result();
    
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
        $userID = $_POST['userID'];

        $input = $this->db->query("INSERT INTO tbl_pengeluaran (uraian, nominal, user_id) VALUES ('$uraian', '$nominal', '$userID')");

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