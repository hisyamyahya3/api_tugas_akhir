<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Penjualan extends CI_Controller
{
    public function insert()
    {
        $idPelanggan = $_POST['idPel'];
        $hargaPenjualan = $_POST['hargaPenjualan'];
        $jmlUang = $_POST['jmlUang'];
        $jmlKembalian = $_POST['jmlKembalian'];
        $created_at = date('Y-m-d h:m:s');
        $date = date('Y-m-d');
        $keterangan = ($jmlKembalian == 0) ? 'Bayar lunas' : 'Bayar kurang';

        try {
            $this->db->trans_start();

            function isNegative($input)
            {
                $numberValue = floatval($input); // Attempt to convert to float
                return $numberValue < 0; // Check if it's less than zero
            }

            function explodeDate($input)
            {
                $result = [];

                $result[] = substr($input, 0, 2);
                $result[] = substr($input, 2, 2);
                $result[] = substr($input, 4, 2);
                $result[] = substr($input, 6, 7);

                return $result;
            }

            $getLatestDate = $this->db->query("SELECT jual_nofak FROM tbl_jual ORDER BY jual_tanggal DESC LIMIT 1")->row();

            if ($getLatestDate) {
                $tempDate = explodeDate($getLatestDate->jual_nofak); // tempDate[0] = date, tempDate[1] = month, tempDate[2] = year, tempDate[3] = auto increment
            }

            if (!$getLatestDate || ($tempDate[0] != date('d'))) { // different date
                $formattedNofak = date('dmy') . str_pad(1, 6, "0", STR_PAD_LEFT);
            } else {
                $counter = $tempDate[3] + 1;
                $formattedNofak = date('dmy') . str_pad($counter, 6, "0", STR_PAD_LEFT);
            }

            $cartsData = $this->db->query("SELECT tk.barang_id, tb.barang_nama, tb.barang_harpok, tb.barang_harjul, tk.qty 
                FROM tbl_keranjang tk
                JOIN tbl_barang tb ON tk.barang_id = tb.barang_id
                WHERE tk.pelanggan_id = $idPelanggan
                ")
                ->result();;

            // Prepare the SQL query template
            $sqlTemplate = "INSERT INTO tbl_detail_jual (d_jual_nofak, d_jual_barang_id, d_jual_barang_nama, d_jual_barang_satuan, d_jual_barang_harpok, d_jual_barang_harjul, d_jual_qty, d_jual_total) VALUES ";

            // Iterate through the array and insert each item
            foreach ($cartsData as $item) {
                $nofak = $formattedNofak;
                $barangId = $item->barang_id;
                $barangNama = $item->barang_nama;
                $barangSatuan = 'pcs';
                $barangHarpok = $item->barang_harpok;
                $barangHarjul = $item->barang_harjul;
                $qty = $item->qty;
                $total = $barangHarjul * $qty;

                // Build the SQL query for each item
                $sqlItem = "($nofak, $barangId, '$barangNama', '$barangSatuan', $barangHarpok, $barangHarjul, $qty, $total), ";

                // Append the item query to the template
                $sqlTemplate .= $sqlItem;
            }

            // Remove the trailing comma (added by the loop)
            $sqlInsertDetailJual = rtrim($sqlTemplate, ', ');

            if (isNegative($jmlKembalian) == 1) {
                $this->db->query("INSERT INTO tbl_piutang (id_pelanggan, tgl_transaksi, jml_transaksi, jml_dibayar, jml_kekurangan) VALUES ($idPelanggan, '$created_at', $hargaPenjualan, $jmlUang, $jmlKembalian)");
            }

            $this->db->query("DELETE FROM tbl_keranjang WHERE pelanggan_id = $idPelanggan");
            $this->db->query("INSERT INTO tbl_jual (jual_nofak, jual_tanggal, jual_total, jual_jml_uang, jual_kembalian, jual_user_id, jual_keterangan, jual_id_pelanggan) VALUES ('$formattedNofak', '$date', $hargaPenjualan, $jmlUang, $jmlKembalian, 1, '$keterangan', $idPelanggan)");
            $this->db->query($sqlInsertDetailJual);
            $this->db->trans_complete();

            echo json_encode(['status' => 'ok', 'message' => 'Berhasil']);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
