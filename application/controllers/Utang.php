<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Utang extends CI_Controller
{
    public function categorySupplier()
    {
        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT DISTINCT s.suplier_nama, u.supplier_id 
            FROM tbl_hutang u 
            JOIN tbl_suplier s 
            ON u.supplier_id = s.suplier_id 
            JOIN tbl_beli b 
            ON u.beli_nofak = b.beli_nofak 
            WHERE b.beli_user_id = $userID;")
            ->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function index()
    {
        $userID = $_POST['userID'];
        $supplierID = $_POST['supplierID'];
        $data = $this->db->query("SELECT u.id, u.supplier_id, s.suplier_nama, u.tgl_transaksi, u.jml_transaksi, u.jml_dibayar, u.jml_kekurangan 
            FROM tbl_hutang u 
            JOIN tbl_suplier s ON u.supplier_id = s.suplier_id
            JOIN tbl_beli b ON u.beli_nofak = b.beli_nofak
            WHERE b.beli_user_id = $userID
            AND s.suplier_id = $supplierID")
            ->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function detail()
    {
        $utangID = $_POST['utangID'];
        $transaction = $this->db->query("SELECT u.id, u.beli_nofak, u.supplier_id, s.suplier_nama, u.tgl_transaksi, u.jml_transaksi, u.jml_dibayar, u.jml_kekurangan, u.status 
            FROM tbl_hutang u 
            JOIN tbl_suplier s ON u.supplier_id = s.suplier_id
            WHERE u.id = $utangID")
            ->row();

        $detailTransaction = $this->db->query("SELECT b.barang_nama, db.d_beli_harga, db.d_beli_jumlah, db.d_beli_total 
            FROM tbl_detail_beli db
            JOIN tbl_barang b ON db.d_beli_barang_id = b.barang_id
            WHERE db.d_beli_nofak = '$transaction->beli_nofak'")->result();

        $data = [
            'transaction' => $transaction,
            'detail' => $detailTransaction
        ];

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function transaction()
    {
        $utangID = $_POST['id'];
        $beliNofak = $_POST['noTransaksi'];
        $jmlUang = (int) $_POST['jmlUang'];

        try {
            $this->db->trans_start();

            $utang = $this->db->query("SELECT * FROM tbl_hutang WHERE id = $utangID AND beli_nofak = '$beliNofak'")->row();

            $outstanding = abs((int) (isset($utang) ? (isset($utang->jml_kekurangan) ? $utang->jml_kekurangan : 0) : 0));

            if ($jmlUang !== $outstanding) {
                throw new Exception("Harap membayar sesuai dengan nominal kekurangan!");
            }

            $this->db->query("UPDATE tbl_beli SET beli_keterangan = 'LUNAS' WHERE beli_nofak = '$beliNofak'");
            $this->db->query("UPDATE tbl_hutang SET jml_angsuran = $jmlUang, status = 'LUNAS' WHERE id = $utangID AND beli_nofak = '$beliNofak'");

            $data = $this->db->query("SELECT h.beli_nofak AS noTransaksi, s.suplier_nama AS nama, h.jml_angsuran
                FROM tbl_hutang h
                JOIN tbl_suplier s ON h.supplier_id = s.suplier_id
                WHERE h.id = $utangID AND h.beli_nofak = '$beliNofak'")->row();

            $this->db->trans_complete();

            echo json_encode(['status' => 'ok', 'message' => 'Berhasil', 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'not ok', 'message' => $e->getMessage()]);
        }
    }

    public function search () {

        $userID = $_POST['userID'];
        $supplier_nama = $_POST['supplier_nama'];

        $data = $this->db->query("SELECT DISTINCT s.suplier_nama, u.supplier_id 
            FROM tbl_hutang u 
            JOIN tbl_suplier s 
            ON u.supplier_id = s.suplier_id 
            JOIN tbl_beli b 
            ON u.beli_nofak = b.beli_nofak 
            WHERE b.beli_user_id = $userID
            AND s.suplier_nama 
            LIKE '%$supplier_nama%';")
            ->result();

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
