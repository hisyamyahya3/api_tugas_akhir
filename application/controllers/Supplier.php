<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Supplier extends CI_Controller
{
    public function index()
    {
        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT * FROM tbl_suplier WHERE user_id = $userID")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function insert()
    {
        $suplier_nama = $_POST['suplier_nama'];
        $suplier_alamat = $_POST['suplier_alamat'];
        $suplier_notelp = $_POST['suplier_notelp'];
        $userID = $_POST['userID'];

        $input = $this->db->query("INSERT INTO tbl_suplier (suplier_nama, suplier_alamat, suplier_notelp, user_id) VALUES ('$suplier_nama', '$suplier_alamat', '$suplier_notelp', '$userID')");

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
        $namaSupplier = $_POST['nama_supplier'];
        $userID = $_POST['userID'];

        $data = $this->db->query("SELECT * FROM tbl_suplier WHERE suplier_nama LIKE '%$namaSupplier%' AND user_id = $userID")->result();

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
        $suplier_id = $_POST['kodeSupplier'];
        $suplier_nama = $_POST['suplier_nama'];
        $suplier_alamat = $_POST['suplier_alamat'];
        $suplier_notelp = $_POST['suplier_notelp'];

        $input = $this->db->query("UPDATE tbl_suplier SET suplier_nama = '$suplier_nama', suplier_alamat = '$suplier_alamat', suplier_notelp = '$suplier_notelp' WHERE suplier_id = $suplier_id");

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
        $suplier_id = $_POST['suplier_id'];

        $input = $this->db->query("DELETE FROM tbl_suplier WHERE suplier_id = $suplier_id");

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
        $supplierId = $_POST['supplierId'];
        $supplierBarangId = $_POST['supplierBarangId'];
        $supplierBarangHarjul = $_POST['supplierBarangHarjul'];
        $supplierBarangStok = $_POST['supplierBarangStok'];
        $supplierBarangQty = $_POST['supplierBarangQty'];
        $created_at = date('Y-m-d h:m:s');

        $cekstok = $this->db->query("SELECT * FROM tbl_barang WHERE barang_id = '$supplierBarangId' ")->row();

        // cek stok>
        // klo stok > 0, berarti bisa insert. klo 0, berarti gabisa insert
        // klo misal min stok 1, trus stok skrg sisa 2. trus ada orang beli 2

        // cek klo qty > stok product {
        // gabisa
        // }
        if ($cekstok->barang_stok > 1) {
            // insert here
            $input = $this->db->query("INSERT INTO tbl_keranjang_pembelian 
                (supplier_id, barang_id, barang_harjul, barang_stok, qty, created_at) 
                VALUES ('$supplierId', '$supplierBarangId', '$supplierBarangHarjul', '$supplierBarangStok', '$supplierBarangQty', '$created_at')");

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

    public function tampilKeranjangPembelian()
    {
        $sql = "SELECT 
        s.suplier_id, 
        s.suplier_nama, 
        b.barang_id,
        b.barang_nama,
        kjp.barang_harjul,
        b.barang_stok
    FROM 
        tbl_keranjang_pembelian kjp 
    JOIN 
        tbl_suplier s ON kjp.supplier_id = s.suplier_id 
    JOIN 
        tbl_barang b ON kjp.barang_id = b.barang_id 
    ORDER BY 
        s.suplier_id";

        $query = $this->db->query($sql);

        $customers = [];

        foreach ($query->result_array() as $row) {
            if (!isset($customers[$row['suplier_nama']])) {
                $customers[$row['suplier_nama']] = [
                    'suplier_nama' => $row['suplier_nama'],
                    'suplier_id' => $row['suplier_id'],
                    'data' => []
                ];
            }

            $customers[$row['suplier_nama']]['data'][] = [
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
        $supplierId = $_POST['supplierId'];

        $data = $this->db->query("SELECT kjp.id, kjp.supplier_id, kjp.barang_id, b.barang_nama, kjp.barang_harjul, b.barang_stok, kjp.qty FROM tbl_keranjang_pembelian kjp JOIN tbl_barang b ON kjp.barang_id = b.barang_id WHERE kjp.supplier_id = '$supplierId';")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function deleteKeranjang()
    {
        $keranjang_id = $_POST['id'];

        $input = $this->db->query("DELETE FROM tbl_keranjang_pembelian WHERE id = '$keranjang_id'");

        if ($input) {
            $hasil = [
                'status' => 'ok',
                'keterangan' => 'Pembelian berhasil dihapus'
            ];
        } else {
            $hasil = [
                'status' => 'gagal',
                'keterangan' => 'Pembelian gagal dihapus'
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

        if ($action == 'plus') {
            $qty += 1;
        } else {
            $qty -= 1;
        }

        $input = $this->db->query("UPDATE `tbl_keranjang_pembelian` SET qty = '$qty' WHERE id = '$id' AND barang_id = '$barang_id';");

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
}
