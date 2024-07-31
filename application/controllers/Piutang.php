<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Piutang extends CI_Controller
{
    public function categoryCustomer()
    {
        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT DISTINCT p.pelanggan_nama, c.id_pelanggan
            FROM tbl_piutang c 
            JOIN tbl_pelanggan p ON p.pelanggan_id = c.id_pelanggan
            JOIN tbl_jual j ON c.jual_nofak = j.jual_nofak
            WHERE j.jual_user_id = 3  
            ORDER BY p.pelanggan_nama;")
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
        $customerID = $_POST['customerID'];
        $data = $this->db->query("SELECT c.id, p.pelanggan_nama, c.tgl_transaksi, c.jml_transaksi, c.jml_dibayar, c.jml_kekurangan 
            FROM tbl_piutang c 
            JOIN tbl_pelanggan p ON p.pelanggan_id = c.id_pelanggan
            JOIN tbl_jual j ON c.jual_nofak = j.jual_nofak
            WHERE j.jual_user_id = $userID 
            AND p.pelanggan_id = $customerID")
            ->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function detail()
    {
        $utangID = $_POST['piutangID'];
        $transaction = $this->db->query("SELECT c.id, c.jual_nofak, p.pelanggan_nama, c.tgl_transaksi, c.jml_transaksi, c.jml_dibayar, c.jml_kekurangan, c.status
            FROM tbl_piutang c 
            JOIN tbl_pelanggan p ON p.pelanggan_id = c.id_pelanggan
            WHERE c.id = $utangID")
            ->row();

        $detailTransaction = $this->db->query("SELECT b.barang_nama, dj.d_jual_barang_harjul, dj.d_jual_qty, dj.d_jual_total
            FROM tbl_detail_jual dj
            JOIN tbl_barang b ON dj.d_jual_barang_id = b.barang_id
            WHERE dj.d_jual_nofak = '$transaction->jual_nofak'")->result();

        $hasil = [
            'status' => 'ok',
            'data' => [
                'transaction' => $transaction,
                'detail' => $detailTransaction
            ]
        ];

        echo json_encode($hasil);
    }

    public function transaction()
    {
        $utangID = $_POST['id'];
        $jualNofak = $_POST['noTransaksi'];
        $jmlUang = (int) $_POST['jmlUang'];

        try {
            $this->db->trans_start();

            $utang = $this->db->query("SELECT * FROM tbl_piutang WHERE id = $utangID AND jual_nofak = '$jualNofak'")->row();

            $outstanding = abs((int) $utang->jml_kekurangan);

            if ($jmlUang !== $outstanding) {
                throw new Exception("Harap membayar sesuai dengan nominal kekurangan!");
            }

            $this->db->query("UPDATE tbl_jual SET jual_keterangan = 'LUNAS' WHERE jual_nofak = '$jualNofak'");
            $this->db->query("UPDATE tbl_piutang SET jml_angsuran = $jmlUang, status = 'LUNAS' WHERE id = $utangID AND jual_nofak = '$jualNofak'");

            $data = $this->db->query("SELECT c.jual_nofak AS noTransaksi, p.pelanggan_nama AS nama, c.jml_angsuran 
                FROM tbl_piutang c 
                JOIN tbl_pelanggan p ON p.pelanggan_id = c.id_pelanggan
                WHERE c.id = $utangID AND c.jual_nofak = '$jualNofak'")
                ->row();

            $this->db->trans_complete();

            echo json_encode(['status' => 'ok', 'message' => 'Berhasil', 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'not ok', 'message' => $e->getMessage()]);
        }
    }

    public function search () {

        $userID = $_POST['userID'];
        $pelanggan_nama = $_POST['pelanggan_nama'];

        $data = $this->db->query("SELECT DISTINCT p.pelanggan_nama, c.id_pelanggan 
            FROM tbl_piutang c 
            JOIN tbl_pelanggan p 
            ON p.pelanggan_id = c.id_pelanggan 
            JOIN tbl_jual j 
            ON c.jual_nofak = j.jual_nofak 
            WHERE j.jual_user_id = 3 
            AND p.pelanggan_nama 
            LIKE '%$pelanggan_nama%';")->result();
                
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
