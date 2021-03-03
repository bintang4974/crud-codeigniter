<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Siswa extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('siswa_model');
    }

    public function index()
    {
        $data['siswa'] = $this->siswa_model->get_data('tbl_siswa')->result();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('siswa', $data);
        $this->load->view('templates/footer');
    }

    public function tambah()
    {
        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('tambah_siswa');
        $this->load->view('templates/footer');
    }

    public function tambah_aksi()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->tambah();
        } else {
            $nama_siswa = $this->input->post('nama_siswa');
            $kelas_siswa = $this->input->post('kelas_siswa');
            $alamat_siswa = $this->input->post('alamat_siswa');
            $nomer_telpon = $this->input->post('nomer_telpon');

            $data = array(
                'nama_siswa' => $nama_siswa,
                'kelas_siswa' => $kelas_siswa,
                'alamat_siswa' => $alamat_siswa,
                'nomer_telpon' => $nomer_telpon
            );

            $this->siswa_model->insert_data($data, 'tbl_siswa');
            $this->session->set_flashdata('pesan', '<div class="alert alert-primary alert-dismissible fade show" role="alert"><strong>Data Berhasil Ditambahkan!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            redirect('siswa');
        }
    }

    public function edit($id)
    {
        $data['siswa'] = $this->db->query("SELECT * FROM tbl_siswa WHERE id_siswa = '$id'")->result();

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar');
        $this->load->view('edit_siswa', $data);
        $this->load->view('templates/footer');
    }

    public function edit_aksi()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->tambah();
        } else {
            $id_siswa = $this->input->post('id_siswa');
            $nama_siswa = $this->input->post('nama_siswa');
            $kelas_siswa = $this->input->post('kelas_siswa');
            $alamat_siswa = $this->input->post('alamat_siswa');
            $nomer_telpon = $this->input->post('nomer_telpon');

            $data = array(
                'nama_siswa' => $nama_siswa,
                'kelas_siswa' => $kelas_siswa,
                'alamat_siswa' => $alamat_siswa,
                'nomer_telpon' => $nomer_telpon
            );

            $where = array('id_siswa' => $id_siswa);

            $this->siswa_model->update_data($data, $where, 'tbl_siswa');
            $this->session->set_flashdata('pesan', '<div class="alert alert-warning alert-dismissible fade show" role="alert"><strong>Data Berhasil Diedit!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            redirect('siswa');
        }
    }

    public function _rules()
    {
        $this->form_validation->set_rules('nama_siswa', 'Nama Siswa', 'required');
        $this->form_validation->set_rules('kelas_siswa', 'Kelas Siswa', 'required');
        $this->form_validation->set_rules('alamat_siswa', 'Alamat Siswa', 'required');
        $this->form_validation->set_rules('nomer_telpon', 'Nomer Telpon', 'required');
    }

    public function delete($id)
    {
        $where = array('id_siswa' => $id);

        $this->siswa_model->delete($where, 'tbl_siswa');
        $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Data Berhasil Dihapus!<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        redirect('siswa');
    }
}

/* End of file Siswa.php */
