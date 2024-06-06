<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Kategori extends CI_Controller
{
    public function index()
    {
        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT * FROM tbl_kategori WHERE user_id = $userID")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function insert()
    {
        $kategori_nama = $_POST['kategori_nama'];
        $userID = $_POST['userID'];

        $input = $this->db->query("INSERT INTO tbl_kategori (kategori_nama, user_id) VALUES ('$kategori_nama', '$userID')");

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

    public function update()
    {
        $id_kategori = $_POST['id_kategori'];
        $kategori_nama = $_POST['kategori_nama'];

        $input = $this->db->query("UPDATE tbl_kategori SET kategori_nama = '$kategori_nama' WHERE kategori_id = $id_kategori");

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

    public function delete()
    {
        $id_kategori = $_POST['id_kategori'];

        $input = $this->db->query("DELETE FROM tbl_kategori WHERE kategori_id = $id_kategori");

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

    public function search()
    {
        $namaKategori = $_POST['kategori_nama'];
        $userID = $_POST['userID'];

        $data = $this->db->query("SELECT * FROM tbl_kategori WHERE user_id = '$userID' AND kategori_nama LIKE '%$namaKategori%'")->result();

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
