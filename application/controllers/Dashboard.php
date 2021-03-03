<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}

    public function index()
    {
    	$data = $this->user_model->get_data($this->session->userdata['username']);
    	$data = array(
    		'username' => $data->username,
    		'email' => $data->email,
    		'level' => $data->level,
    		'create_at' => $data->create_at
    	);

        $this->load->view('templates/header');
        $this->load->view('templates/sidebar', $data);
        $this->load->view('dashboard', $data);
        $this->load->view('templates/footer');
    }

}

/* End of file Dashboard.php */
