<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Barang extends CI_Controller
{
    public function index()
    {
        $data = $this->db->query("SELECT b.barang_id, b.barang_nama, b.barang_satuan, b.barang_harpok, b.barang_harjul, b.barang_harjul_grosir, b.barang_stok, b.barang_min_stok, b.barang_kategori_id, k.kategori_id, k.kategori_nama FROM tbl_barang b JOIN tbl_kategori k ON b.barang_kategori_id = k.kategori_id;")->result();
    
        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function insert()
    {
        $barang_nama = $_POST['barang_nama'];
        $barang_satuan = $_POST['barang_satuan'];
        $barang_harpok = $_POST['barang_harpok'];
        $barang_harjul = $_POST['barang_harjul'];
        $barang_harjul_grosir = $_POST['barang_harjul_grosir'];
        $barang_stok = $_POST['barang_stok'];
        $barang_min_stok = $_POST['barang_min_stok'];
        $barang_kategori_id = $_POST['barang_kategori_id'];

        $input = $this->db->query("INSERT INTO tbl_barang 
            (barang_nama, barang_satuan, barang_harpok, barang_harjul, barang_harjul_grosir, barang_stok, barang_min_stok, barang_kategori_id) 
            VALUES ('$barang_nama', '$barang_satuan', '$barang_harpok', '$barang_harjul', '$barang_harjul_grosir', '$barang_stok', '$barang_min_stok', '$barang_kategori_id')");

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
        $barang_id = $_POST['kodeBarang'];
        $barang_nama = $_POST['barang_nama'];
        $barang_satuan = $_POST['barang_satuan'];
        $barang_harpok = $_POST['barang_harpok'];
        $barang_harjul = $_POST['barang_harjul'];
        $barang_harjul_grosir = $_POST['barang_harjul_grosir'];
        $barang_stok = $_POST['barang_stok'];
        $barang_min_stok = $_POST['barang_min_stok'];
        $barang_kategori_id = $_POST['barang_kategori_id'];

        $input = $this->db->query("UPDATE tbl_barang SET barang_nama = '$barang_nama', barang_satuan = '$barang_satuan', barang_harpok = '$barang_harpok', barang_harjul = '$barang_harjul', barang_harjul_grosir = '$barang_harjul_grosir', barang_stok = '$barang_stok', barang_min_stok = '$barang_min_stok', barang_kategori_id = '$barang_kategori_id' WHERE barang_id = $barang_id");

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

    public function search()
    {
        $namaBarang = $_POST['nama_barang'];

        $data = $this->db->query("SELECT * FROM tbl_barang WHERE barang_nama LIKE '%$namaBarang%'")->result();

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

    public function delete()
    {
        $barang_id = $_POST['barang_id'];

        $input = $this->db->query("DELETE FROM tbl_barang WHERE barang_id = $barang_id");

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

    public function searchTable()
    {
        $namaBarang = $_POST['nama_barang'];

        $data = $this->db->query("SELECT b.barang_id, b.barang_nama, b.barang_satuan, b.barang_harpok, b.barang_harjul, b.barang_harjul_grosir, b.barang_stok, b.barang_min_stok, b.barang_kategori_id, k.kategori_id, k.kategori_nama FROM tbl_barang b JOIN tbl_kategori k ON b.barang_kategori_id = k.kategori_id WHERE b.barang_nama LIKE '%$namaBarang%'")->result();

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