<?php
defined('BASEPATH') or exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class User extends CI_Controller 
{

    public function regis () {
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $pass = $_POST['pass'];

        $input = $this->db->query("INSERT INTO")
    }

    public function login () {
        $email = $_POST['email'];
        $pass = $_POST['pass'];


    }
}