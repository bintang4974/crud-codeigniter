<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	public function get_data($id)
	{
		$this->db->where('username', $id);
		return $this->db->get('tbl_user')->row();
	}

}

/* End of file User_model.php */
/* Location: ./application/models/User_model.php */