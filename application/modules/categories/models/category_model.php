<?php

class category_model extends CI_MODEL {

	public function _construct () {
		$this->load->database();
	}

	public function posts ($params) {
		$category = $this->db->get_where('categories', $params)->row_array();
		
		$category['posts'] = $this->db->order_by('created_at', 'DESC')
								->get_where('posts', [
									'category_id' => $category['id']
								])->result_array();
		return $category;
	}

	public function get($take = false) {
		$this->db->order_by('created_at', 'DESC');
		return $this->db->get('categories', $take)->result_array();
	}

	public function getSome ($params) {
		return $this->db->get_where('categories', $params)->row_array();
	}
}
