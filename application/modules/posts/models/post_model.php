<?php

class post_model extends CI_MODEL {

	public function __construct () {
		// $this->load->database();
	}

	/**
	 * This covenience method returns a category based on given $parameters
	 * @param array
	 * @return array
	 */
	public function category ($params) {
		return $this->db->get_where('categories', $params)->row_array();
	}

	/**
	 * This covenience method returns a files based on given $parameters
	 * @param array
	 * @return array
	 */
	public function files ($params) {
		return $this->db->get_where('files', $params)->result_array();
	}
	/**
	 * Get All Posts from the db limited by the $limit parameter
	 * @param int
	 * @return array
	 */
	public function get($limit = false) {
		$this->db->order_by('created_at', 'DESC');
		return $this->db->get('posts', $limit)->result_array();
	}

	/**
	 * Get a single post from the db based on given parameters 
	 * Choose whether or not to associate category
	 * @param array
	 * @param bool
	 * @return array
	 */
	public function getOne ($params, $eagerLoad = FALSE) {
		$post = $this->db->get_where('posts', $params)->row_array();

		if ($eagerLoad === TRUE) {
			$post['category'] = $this->category([
				'id' => $post['category_id']
			]);

			// $post['files'] = $this->files([
			// 	'post_id' => $post['id'] 
			// ]);
		}
		return $post;
	}

	/**
	 * Check for XSS on inputs
	 * Submit blog post into the database from input
	 * @return void
	 */
	public function create ($params) {
		if ($this->db->insert('posts', $params)) {
			return $this->getOne([
				'slug' => $params['slug']
			]);
		}
	}

	/**
	 * Update blog post with given $id in database
	 * @param int
	 * @return string
	 */
	public function update ($params) {
		$data = $this->input->post(NULL, TRUE);
		$data['slug'] = url_title($data['title']);

		$this->db->where($params)->update('posts', $data);	
		return $slug;
	}

	/**
	 * Delete blog post with given $id from database
	 * @param int
	 * @return bool
	 */
	public function delete ($id) {
		$this->db->where('id', $id)->delete('posts');
		return true;
	}
}
