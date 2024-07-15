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
        $userID = $_POST['userID'];
        $hargaPenjualan = $_POST['hargaPenjualan'];
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
                $result = [];

                $result[] = substr($input, 0, 2);
                $result[] = substr($input, 2, 2);
                $result[] = substr($input, 4, 2);
                $result[] = substr($input, 6, 7);

                return $result;
            }

            $getTotalTransaction = $this->db->query("SELECT COUNT(jual_nofak) AS total_transaction FROM tbl_jual WHERE jual_user_id = $userID AND jual_tanggal = '$date'")->row();

            if ($getTotalTransaction->total_transaction == 10) {
                throw new Exception("Teleh melewati batas transaksi harian!");
            }

            $getLatestDate = $this->db->query("SELECT * FROM tbl_jual WHERE jual_tanggal = '$date' ORDER BY jual_nofak DESC LIMIT 1")->row();

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
                WHERE tk.pelanggan_id = $idPelanggan")
                ->result();;

            // Prepare the SQL query template
            $sqlTemplate = "INSERT INTO tbl_detail_jual (d_jual_nofak, d_jual_barang_id, d_jual_barang_nama, d_jual_barang_satuan, d_jual_barang_harpok, d_jual_barang_harjul, d_jual_qty, d_jual_total) VALUES ";

            // Iterate through the array and insert each item
            foreach ($cartsData as $item) {
                $barangId = $item->barang_id;
                $barangNama = $item->barang_nama;
                $barangSatuan = 'pcs';
                $barangHarpok = $item->barang_harpok;
                $barangHarjul = $item->barang_harjul;
                $qty = $item->qty;
                $total = $barangHarjul * $qty;

                // Build the SQL query for each item
                $sqlItem = "('$formattedNofak', $barangId, '$barangNama', '$barangSatuan', $barangHarpok, $barangHarjul, $qty, $total), ";

                // Append the item query to the template
                $sqlTemplate .= $sqlItem;

                $getLatestStock = $this->db->query("SELECT * FROM tbl_barang WHERE barang_id = $barangId")->row();

                $finalStock = $getLatestStock->barang_stok - $qty;

                if ($qty > $getLatestStock->barang_stok) {
                    throw new Exception("Stok barang $barangNama tidak tersedia!");
                }

                if ($getLatestStock->barang_min_stok == $getLatestStock->barang_stok) {
                    throw new Exception("Stok barang $barangNama tidak tersedia!");
                }

                if ($finalStock < $getLatestStock->barang_min_stok) {
                    throw new Exception("Stok barang $barangNama tidak tersedia!");
                }

                $this->db->query("UPDATE tbl_barang SET barang_stok = $finalStock WHERE barang_id = $barangId");
            }

            // Remove the trailing comma (added by the loop)
            $sqlInsertDetailJual = rtrim($sqlTemplate, ', ');

            if (isNegative($jmlKembalian) == 1) {
                $this->db->query("INSERT INTO tbl_piutang (id_pelanggan, jual_nofak, tgl_transaksi, jml_transaksi, jml_dibayar, jml_kekurangan, status) VALUES ($idPelanggan, '$formattedNofak', '$created_at', $hargaPenjualan, $jmlUang, $jmlKembalian, 'BELUM LUNAS')");
            }

            $this->db->query("DELETE FROM tbl_keranjang WHERE pelanggan_id = $idPelanggan");
            $this->db->query("INSERT INTO tbl_jual (jual_nofak, jual_tanggal, jual_total, jual_jml_uang, jual_kembalian, is_debt, jual_user_id, jual_keterangan, jual_id_pelanggan) VALUES ('$formattedNofak', '$date', $hargaPenjualan, $jmlUang, $jmlKembalian, $isDebt, $userID, '$keterangan', $idPelanggan)");
            $this->db->query($sqlInsertDetailJual);
            $data = $this->db->query("SELECT tj.*, tp.pelanggan_nama 
                FROM tbl_jual tj 
                JOIN tbl_pelanggan tp ON tj.jual_id_pelanggan = tp.pelanggan_id
                WHERE tj.jual_id_pelanggan = '$idPelanggan' AND tj.jual_nofak = '$formattedNofak'")
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
        $data = $this->db->query("SELECT p.pelanggan_id, p.pelanggan_nama, j.jual_nofak, j.jual_tanggal, j.jual_jml_uang, dj.d_jual_barang_nama, dj.d_jual_barang_harjul, dj.d_jual_qty, dj.d_jual_total, j.jual_keterangan
            FROM tbl_pelanggan p 
            JOIN tbl_jual j 
            ON p.pelanggan_id = j.jual_id_pelanggan 
            JOIN tbl_detail_jual dj 
            ON j.jual_nofak = dj.d_jual_nofak
            WHERE j.jual_user_id = $userID")->result();

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

        $data = $this->db->query("SELECT p.pelanggan_id, p.pelanggan_nama, j.jual_nofak, j.jual_tanggal, j.jual_jml_uang, dj.d_jual_barang_nama, dj.d_jual_barang_harjul, dj.d_jual_qty, dj.d_jual_total, j.jual_keterangan
            FROM tbl_pelanggan p 
            JOIN tbl_jual j 
            ON p.pelanggan_id = j.jual_id_pelanggan 
            JOIN tbl_detail_jual dj 
            ON j.jual_nofak = dj.d_jual_nofak
            WHERE j.jual_user_id = $userID
            AND j.jual_tanggal >= '$formattedFromDate' AND j.jual_tanggal <= '$formattedToDate'")->result();

        $hasil = [
            'status' => 'ok',
            'data' => $data
        ];

        echo json_encode($hasil);
    }
}
