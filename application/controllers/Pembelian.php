<?php

defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Pembelian extends CI_Controller
{
    public function insert()
    {
        $idSupplier = $_POST['idSupplier'];
        $userID = $_POST['userID'];
        $totalPembelian = $_POST['totalPembelian'];
        $jmlUang = $_POST['jmlUang'];
        $jmlKembalian = $_POST['jmlKembalian'];
        $created_at = date('Y-m-d h:m:s');
        $date = date('Y-m-d');
        $isDebt = ($jmlKembalian >= 0) ? 0 : 1;
        $keterangan = ($jmlKembalian >= 0) ? 'LUNAS' : 'KURANG';

        try {
            $this->db->trans_start();

            function isNegative($input)
            {
                $numberValue = floatval($input); // Attempt to convert to float
                return $numberValue < 0; // Check if it's less than zero
            }

            function explodeDate($input)
            {
                $input = str_replace('FAK', '', $input);
                $result = [];

                $result[] = substr($input, 0, 2);
                $result[] = substr($input, 2, 2);
                $result[] = substr($input, 4, 2);
                $result[] = substr($input, 6, 7);

                return $result;
            }

            $getTotalTransaction = $this->db->query("SELECT COUNT(beli_nofak) AS total_transaction FROM tbl_beli WHERE beli_user_id = $userID AND beli_tanggal = '$date'")->row();

            if ($getTotalTransaction->total_transaction == 10) {
                throw new Exception("Teleh melewati batas transaksi harian!");
            }

            $getLatestDate = $this->db->query("SELECT * FROM tbl_beli WHERE beli_tanggal = '$date' ORDER BY beli_nofak DESC LIMIT 1")->row();

            if ($getLatestDate) {
                $tempDate = explodeDate($getLatestDate->beli_nofak); // tempDate[0] = date, tempDate[1] = month, tempDate[2] = year, tempDate[3] = auto increment
            }

            if (!$getLatestDate || ($tempDate[0] != date('d'))) { // different date
                $formattedNofak = 'FAK' . date('dmy') . str_pad(1, 6, "0", STR_PAD_LEFT);
            } else {
                $counter = $tempDate[3] + 1;
                $formattedNofak = 'FAK' . date('dmy') . str_pad($counter, 6, "0", STR_PAD_LEFT);
            }

            $cartsData = $this->db->query("SELECT tkp.barang_id, tb.barang_nama, tb.barang_harjul, tkp.qty 
                FROM tbl_keranjang_pembelian tkp
                JOIN tbl_barang tb ON tkp.barang_id = tb.barang_id
                WHERE tkp.supplier_id = $idSupplier")
                ->result();;

            // Prepare the SQL query template
            $sqlTemplate = "INSERT INTO tbl_detail_beli (d_beli_nofak, d_beli_barang_id, d_beli_harga, d_beli_jumlah, d_beli_total) VALUES ";

            // Iterate through the array and insert each item
            foreach ($cartsData as $item) {
                $nofak = $formattedNofak;
                $barangId = $item->barang_id;
                $barangHarjul = $item->barang_harjul;
                $qty = $item->qty;
                $total = $barangHarjul * $qty;

                // Build the SQL query for each item
                $sqlItem = "('$nofak', $barangId, $barangHarjul, $qty, $total), ";

                // Append the item query to the template
                $sqlTemplate .= $sqlItem;

                $getLatestStock = $this->db->query("SELECT * FROM tbl_barang WHERE barang_id = $barangId")->row();

                $finalStock = $getLatestStock->barang_stok + $qty;

                $this->db->query("UPDATE tbl_barang SET barang_stok = $finalStock WHERE barang_id = $barangId");
            }

            // Remove the trailing comma (added by the loop)
            $sqlInsertDetailJual = rtrim($sqlTemplate, ', ');

            if (isNegative($jmlKembalian) == 1) {
                $this->db->query("INSERT INTO tbl_hutang (supplier_id, beli_nofak, tgl_transaksi, jml_transaksi, jml_dibayar, jml_kekurangan, status) VALUES ($idSupplier, '$formattedNofak', '$created_at', $totalPembelian, $jmlUang, $jmlKembalian, 'BELUM LUNAS')");
            }

            $this->db->query("DELETE FROM tbl_keranjang_pembelian WHERE supplier_id = $idSupplier");
            $this->db->query("INSERT INTO tbl_beli (beli_nofak, beli_tanggal, beli_suplier_id, beli_user_id, beli_total, beli_jml_uang, beli_kembalian, is_debt, beli_keterangan) VALUES ('$formattedNofak', '$date', $idSupplier, $userID, $totalPembelian, $jmlUang, $jmlKembalian, $isDebt, '$keterangan')");
            $this->db->query($sqlInsertDetailJual);

            $data = $this->db->query("SELECT tb.*, ts.suplier_nama AS nama_supplier
                FROM tbl_beli tb
                JOIN tbl_suplier ts ON tb.beli_suplier_id  = ts.suplier_id 
                WHERE tb.beli_suplier_id  = '$idSupplier' AND tb.beli_nofak = '$formattedNofak'")
                ->row();

            $this->db->trans_complete();

            echo json_encode(['status' => 'ok', 'message' => 'Berhasil', 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'not ok', 'message' => $e->getMessage()]);
        }
    }

    public function laporan() 
    {
        $userID = $_POST['userID'];
        $data = $this->db->query("SELECT s.suplier_id, s.suplier_nama, b.beli_nofak, b.beli_tanggal, b.beli_jml_uang, b.beli_total, tb.barang_nama, db.d_beli_barang_id, db.d_beli_harga, db.d_beli_jumlah, b.beli_keterangan
            FROM tbl_suplier s 
            JOIN tbl_beli b
            ON s.suplier_id = b.beli_suplier_id 
            JOIN tbl_detail_beli db
            ON b.beli_nofak = db.d_beli_nofak
            JOIN tbl_barang tb
            ON db.d_beli_barang_id = tb.barang_id
            WHERE b.beli_user_id = $userID;")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }

    public function filterTanggal() 
    {
        $userID = $_POST['userID'];
        $dariTgl = $_POST['dariTgl'];
        $sampaiTgl = $_POST['sampaiTgl'];
        $formattedFromDate = date('Y-m-d', strtotime($dariTgl));
        $formattedToDate = date('Y-m-d', strtotime($sampaiTgl));

        $data = $this->db->query("SELECT s.suplier_id, s.suplier_nama, b.beli_nofak, b.beli_tanggal, b.beli_jml_uang, b.beli_total, tb.barang_nama, db.d_beli_barang_id, db.d_beli_harga, db.d_beli_jumlah, b.beli_keterangan
            FROM tbl_suplier s 
            JOIN tbl_beli b
            ON s.suplier_id = b.beli_suplier_id 
            JOIN tbl_detail_beli db
            ON b.beli_nofak = db.d_beli_nofak
            JOIN tbl_barang tb
            ON db.d_beli_barang_id = tb.barang_id
            WHERE b.beli_user_id = $userID
            AND b.beli_tanggal >= '$formattedFromDate' AND b.beli_tanggal <= '$formattedToDate'")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }
}
