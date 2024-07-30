<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// header('Content-Type: application/json');

class Pelanggan extends CI_Controller
{
    public function index()
    {
        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT * FROM tbl_pelanggan WHERE user_id = $userID")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function insert()
    {
        $pelanggan_nama = $_POST['pelanggan_nama'];
        $pelanggan_alamat = $_POST['pelanggan_alamat'];
        $pelanggan_notelp = $_POST['pelanggan_notelp'];
        $userID = $_POST['userID'];

        $input = $this->db->query("INSERT INTO tbl_pelanggan (pelanggan_nama, pelanggan_alamat, pelanggan_notelp, user_id) VALUES ('$pelanggan_nama', '$pelanggan_alamat', '$pelanggan_notelp', '$userID')");

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

    public function search()
    {
        $namaPelanggan = $_POST['nama_pelanggan'];
        $userID = $_POST['userID'];

        $data = $this->db->query("SELECT * FROM tbl_pelanggan WHERE pelanggan_nama LIKE '%$namaPelanggan%' AND user_id = $userID")->result();

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

    public function update()
    {
        $pelanggan_id = $_POST['kodePelanggan'];
        $pelanggan_nama = $_POST['pelanggan_nama'];
        $pelanggan_alamat = $_POST['pelanggan_alamat'];
        $pelanggan_notelp = $_POST['pelanggan_notelp'];

        $input = $this->db->query("UPDATE tbl_pelanggan SET pelanggan_nama = '$pelanggan_nama', pelanggan_alamat = '$pelanggan_alamat', pelanggan_notelp = '$pelanggan_notelp' WHERE pelanggan_id = $pelanggan_id");

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
        $pelanggan_id = $_POST['pelanggan_id'];

        $input = $this->db->query("DELETE FROM tbl_pelanggan WHERE pelanggan_id = $pelanggan_id");

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

    public function keranjang()
    {
        $pelangganId = $_POST['pelangganId'];
        $pelangganBarangId = $_POST['pelangganBarangId'];
        $pelangganBarangHarjul = $_POST['pelangganBarangHarjul'];
        $qty = $_POST['pelangganBarangQty'];
        $userID = $_POST['userID'];
        $created_at = date('Y-m-d h:m:s');

        $cekstok = $this->db->query("SELECT * FROM tbl_barang WHERE barang_id = '$pelangganBarangId'")->row();

        if ($cekstok->barang_stok > 1) {
            $cartCheck = $this->db->query("SELECT * FROM tbl_keranjang WHERE pelanggan_id = '$pelangganId' AND barang_id = '$pelangganBarangId'")->row();

            if ($cartCheck) {
                $qty += $cartCheck->qty;

                $input = $this->db->query("UPDATE tbl_keranjang SET qty = '$qty' WHERE pelanggan_id = '$pelangganId' AND barang_id = '$pelangganBarangId'");
            } else {
                $input = $this->db->query("INSERT INTO tbl_keranjang 
            (pelanggan_id, barang_id, barang_harjul, qty, user_id,created_at) 
            VALUES ('$pelangganId', '$pelangganBarangId', '$pelangganBarangHarjul', '$qty', '$userID','$created_at')");
            }

            if ($input) {
                $hasil = [
                    'status' => 'ok',
                    'keterangan' => 'data berhasil ditambahkan ke Keranjang'
                ];
            } else {
                $hasil = [
                    'status' => 'gagal',
                    'keterangan' => 'data gagal ditambahkan ke Keranjang'
                ];
            }
        } else {
            $hasil = [
                'status' => 'gagal',
                'keterangan' => 'data gagal ditambahkan ke Keranjang'
            ];
        }

        echo json_encode($hasil);
    }

    public function tampilKeranjangPenjualan()
    {
        $sql = "SELECT 
            p.pelanggan_id, 
            p.pelanggan_nama,
            p.pelanggan_alamat,
            p.pelanggan_notelp, 
            b.barang_id,
            b.barang_nama,
            kj.barang_harjul,
            kj.qty
        FROM 
            tbl_keranjang kj 
        JOIN 
            tbl_pelanggan p ON kj.pelanggan_id = p.pelanggan_id 
        JOIN 
            tbl_barang b ON kj.barang_id = b.barang_id 
        ORDER BY 
            p.pelanggan_id";


        $query = $this->db->query($sql);

        $customers = [];

        foreach ($query->result_array() as $row) {

            if (!isset($customers[$row['pelanggan_nama']])) {

                $customers[$row['pelanggan_nama']] = [
                    'pelanggan_nama' => $row['pelanggan_nama'],
                    'pelanggan_alamat' => $row['pelanggan_alamat'],
                    'pelanggan_notelp' => $row['pelanggan_notelp'],
                    'pelanggan_id' => $row['pelanggan_id'],
                    'data' => []
                ];
            }

            $customers[$row['pelanggan_nama']]['data'][] = [
                'barang_id' => $row['barang_id'],
                'barang_nama' => $row['barang_nama'],
                'barang_harjul' => $row['barang_harjul'],
                'qty' => $row['qty']
            ];
        }

        // Convert associative array to indexed array
        $customers = array_values($customers);

        echo json_encode($customers);
    }

    public function bayarKeranjang()
    {
        $pelangganId = $_POST['pelangganId'];

        $data = $this->db->query("SELECT kj.id, kj.pelanggan_id, kj.barang_id, b.barang_nama, kj.barang_harjul, b.barang_stok, kj.qty FROM tbl_keranjang kj JOIN tbl_barang b ON kj.barang_id = b.barang_id WHERE kj.pelanggan_id = '$pelangganId'")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function deleteKeranjang()
    {
        $pelanggan_id = $_POST['pelanggan_id'];
        $keranjang_id = $_POST['id'];

        $input = $this->db->query("DELETE FROM tbl_keranjang WHERE pelanggan_id = '$pelanggan_id' AND id = '$keranjang_id'");

        $latestData = $this->db->query("SELECT COUNT(*) AS latestCart FROM tbl_keranjang WHERE pelanggan_id = '$pelanggan_id'")->row();

        if ($input) {
            $hasil = [
                'status' => 'ok',
                'keterangan' => 'Keranjang berhasil dihapus',
                'data' => $latestData
            ];
        } else {
            $hasil = [
                'status' => 'gagal',
                'keterangan' => 'Keranjang gagal dihapus'
            ];
        }

        echo json_encode($hasil);
    }

    public function editQty()
    {
        $id = $_POST['id'];
        $barang_id = $_POST['barang_id'];
        $qty = $_POST['qty'];
        $action = $_POST['action'];

        // if action?? plus =???? $qty + 1 else qty - 1

        if ($action == 'plus') {
            $qty += 1;
        } else {
            $qty -= 1;
        }

        $input = $this->db->query("UPDATE `tbl_keranjang` SET qty = '$qty' WHERE id = '$id' AND barang_id = '$barang_id';");

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

    public function hitungKeranjang () {

        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT COUNT(*) AS count FROM tbl_keranjang WHERE user_id = $userID;")->row();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);

    }
}
