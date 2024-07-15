<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Satuan extends CI_Controller {

    public function index () {
        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT * FROM tbl_satuan WHERE user_id = $userID")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function insert () {
        
        $satuan_nama = $_POST['satuan_nama'];
        $userID = $_POST['userID'];

        $input = $this->db->query("INSERT INTO tbl_satuan (satuan_nama, user_id) VALUES ('$satuan_nama', '$userID')");

        if ($input) {
            $hasil = [
                'status' => 'ok',
                'keterangan' => 'data berhasil ditambahkan'
            ];
        } else {
            $hasil = [
                'status' => 'ok',
                'ketengan' => 'data gagal ditambahkan'
            ];
        }

        echo json_encode($hasil);
    }

    public function update () {

        $satuan_id = $_POST['satuan_id'];
        $satuan_nama = $_POST['satuan_nama'];

        $input = $this->db->query("UPDATE tbl_satuan SET satuan_nama = '$satuan_nama' WHERE satuan_id = $satuan_id");

        if ($input) {
            $hasil = [
                'status' => 'ok',
                'keterangan' => 'data berhasil diupdate'
            ];
        } else {
            $hasil = [
                'status' => 'gagal',
                'keterangan' => 'data gagal diupdate'
            ];
        }

        echo json_encode($hasil);

    }

    public function delete () {
        
        $satuan_id = $_POST['satuan_id'];

        $input = $this->db->query("DELETE FROM tbl_satuan WHERE satuan_id = $satuan_id");

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

    public function search () {

        $satuan_nama = $_POST['satuan_nama'];
        $userID = $_POST['userID'];
        
        $data = $this->db->query("SELECT * FROM tbl_satuan WHERE user_id = '$userID' AND satuan_nama LIKE '%$satuan_nama%'")->result();
        
        if (count($data) == 0) {
            $hasil = [
                'status' => 'not ok',
                'data' => NULL
            ];
        } else {
            $hasil = [
                'status' => 'ok',
                'data' => $data
            ];
        }

        echo json_encode($hasil);

    }

}