<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class User extends CI_Controller
{
    public function register()
    {
        $name = ucwords($_POST['name']);
        $email = $_POST['email'];
        $passInput = $_POST['password'];

        try {
            $this->db->trans_start();

            $emailExist = $this->db->query("SELECT * FROM tbl_user WHERE email = '$email'")->row();

            // Validate input
            if (empty($name)) {
                $error = 'Nama wajib diisi!';
            } elseif (empty($email)) {
                $error = 'Email wajib diisi!';
            } elseif (empty($passInput)) {
                $error = 'Password wajib diisi!';
            } elseif (strlen($name) > 20) {
                $error = 'Nama maksimal 20 karakter!';
            } elseif (strlen($passInput) < 8) {
                $error = 'Password minimal 8 karakter!';
            } elseif ($emailExist) {
                $error = 'Email sudah digunakan!';
            }

            // If there is an error, return it
            if (isset($error) && $error) {
                throw new Exception($error);
            }

            // Sanitize input
            $hashedPassword = password_hash($passInput, PASSWORD_DEFAULT);

            // Insert into database
            $this->db->query("INSERT INTO tbl_user (full_name, email, password) VALUES ('$name', '$email', '$hashedPassword')");
            $this->db->trans_complete();

            echo json_encode(['status' => 'ok', 'message' => 'Berhasil registrasi']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'not ok', 'message' => $e->getMessage()]);
        }
    }

    public function login()
    {
        $email = $_POST['email'];
        $passInput = $_POST['password'];

        try {
            $this->db->trans_start();

            if (empty($email)) {
                $error = 'Email belum diisi!';
            } elseif (empty($passInput)) {
                $error = 'Password belum diisi!';
            }

            if (isset($error) && $error) {
                throw new Exception($error);
            }

            $checkUser = $this->db->query("SELECT * FROM tbl_user WHERE email = '$email'")->row();

            $this->db->trans_complete();
            // Verify the password
            if (isset($checkUser) && password_verify($passInput, $checkUser->password)) {
                echo json_encode(['message' => 'Berhasil login', 'status' => 'ok']);
            } else {
                echo json_encode(['message' => 'Email atau Password salah', 'status' => 'not ok']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'not ok', 'message' => $e->getMessage()]);
        }
    }
}
