<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('login_model');
    }

    public function index()
    {
        $this->load->view('templates/header');
        $this->load->view('login');
        $this->load->view('templates/footer');
    }

    public function proses_login()
    {
        $this->_rules();
        
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('templates/header');
            $this->load->view('login');
            $this->load->view('templates/footer');
        } else {
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            $user = $username;
            $pass = $password;

            $cek = $this->login_model->cek_login($user, $pass);

            if ($cek->num_rows() > 0) {
                foreach ($cek->result() as $ck) {
                    $sess_data['username'] = $ck->username;
                    $sess_data['email'] = $ck->email;
                    $sess_data['level'] = $ck->level;

                    $this->session->set_userdata($sess_data);
                }
                if ($sess_data['level'] == 'admin') {
                    redirect('dashboard');
                } else {
                    $this->session->set_flashdata('pesan', '<div class="alert alert-danger" role="alert">
                    Maaf Username atau Password Anda Salah!</div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('pesan', '<div class="alert alert-danger" role="alert">
                Maaf Username atau Password Anda Salah!</div>');
                redirect('auth');
            }
        }
    }

    public function _rules()
    {
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth');
    }
}

/* End of file Auth.php */
