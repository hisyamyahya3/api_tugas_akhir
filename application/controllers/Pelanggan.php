<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Pelanggan extends CI_Controller
{
    public function index()
    {
        $data = $this->db->query("SELECT * FROM tbl_pelanggan")->result();

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

        $input = $this->db->query("INSERT INTO tbl_pelanggan (pelanggan_nama, pelanggan_alamat, pelanggan_notelp) VALUES ('$pelanggan_nama', '$pelanggan_alamat', '$pelanggan_notelp')");

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

        $data = $this->db->query("SELECT * FROM tbl_pelanggan WHERE pelanggan_nama LIKE '%$namaPelanggan%'")->result();

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
        $pelangganBarangStok = $_POST['pelangganBarangStok'];
        $created_at = date('Y-m-d h:m:s');

        $cekstok = $this->db->query("SELECT * FROM tbl_barang WHERE barang_id = '$pelangganBarangId'")->row();

        if ($cekstok->barang_stok > 1) {
            $input = $this->db->query("INSERT INTO tbl_keranjang 
            (pelanggan_id, barang_id, barang_harjul, barang_stok, created_at) 
            VALUES ('$pelangganId', '$pelangganBarangId', '$pelangganBarangHarjul', '$pelangganBarangStok', '$created_at')");

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
            b.barang_id,
            b.barang_nama,
            kj.barang_harjul,
            kj.barang_stok
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
                    'pelanggan_id' => $row['pelanggan_id'],
                    'data' => []
                ];
            }
 
            $customers[$row['pelanggan_nama']]['data'][] = [
                'barang_id' => $row['barang_id'],
                'barang_nama' => $row['barang_nama'],
                'barang_harjul' => $row['barang_harjul'],
                'barang_stok' => $row['barang_stok']
            ];
        }

        // Convert associative array to indexed array
        $customers = array_values($customers);

        echo json_encode($customers);
    }

    public function bayarKeranjang()
    {
        $pelangganId = $_POST['pelangganId'];

        $data = $this->db->query("SELECT kj.pelanggan_id, kj.barang_id, b.barang_nama, kj.barang_harjul, kj.barang_stok FROM tbl_keranjang kj JOIN tbl_barang b ON kj.barang_id = b.barang_id WHERE kj.pelanggan_id = '$pelangganId'")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }
}
