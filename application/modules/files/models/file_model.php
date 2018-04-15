<?php

class file_model extends CI_MODEL {
	public function __construct () {
		$this->load->database();
	}

	public function create ($params) {
		return $this->db->insert('files', $params)->row_array();
	}
}
