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
        $totalPembelian = $_POST['totalPembelian'];
        $jmlUang = $_POST['jmlUang'];
        $jmlKembalian = $_POST['jmlKembalian'];
        $created_at = date('Y-m-d h:m:s');
        $date = date('Y-m-d');
        // $keterangan = ($jmlKembalian == 0) ? 'Bayar lunas' : 'Bayar kurang';

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
                $this->db->query("INSERT INTO tbl_hutang (supplier_id, beli_nofak, tgl_transaksi, jml_transaksi, jml_dibayar, jml_kekurangan) VALUES ($idSupplier, '$formattedNofak', '$created_at', $totalPembelian, $jmlUang, $jmlKembalian)");
            }

            $this->db->query("DELETE FROM tbl_keranjang_pembelian WHERE supplier_id = $idSupplier");
            $this->db->query("INSERT INTO tbl_beli (beli_nofak, beli_tanggal, beli_suplier_id, beli_user_id, beli_total, beli_jml_uang, beli_kembalian) VALUES ('$formattedNofak', '$date', $idSupplier, 1, $totalPembelian, $jmlUang, $jmlKembalian)");
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
}
