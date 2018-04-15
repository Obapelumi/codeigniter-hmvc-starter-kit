<?php defined('BASEPATH') OR exit('No direct script access allowed');

	class Posts extends MY_Controller {

		public function __construct() {
			parent::__construct ();
		}

		/**
		 * Fetch Blog Posts and Categories from the db and render in view
		 * @return void
		 */
		public function index () {
			$data['title'] = 'Blog';

			$data['posts'] = $this->post_model->get($this->input->get('take', TRUE));

			$data['categories'] =  $this->category_model->get();

			render('index', $data);
		}

		/**
		 * Fetch Post based on its uri
		 * @param string
		 * @return void
		 */
		public function show ($slug) {
			$data['post'] = $this->post_model->getOne([
				'slug' => $slug,
			], TRUE);

			if (empty($data['post'])) {
				show_404();
			}
			else {
				$data['title'] = $data['post']['title'];
				render('posts/show', $data);
			}
		}

		/**
		 * Render Create Post View if validation rules have not been checked or if validation fails
		 * Submit post if validation is successful
		 * @return void
		 */
		public function create () {
			$data['title'] = 'Create Post';

			validate([
				'title' => 'required',
				'body' => 'required'
			]);
			
			if ($this->form_validation->run() === FALSE) {
				$data['categories'] = $this->category_model->get();
				if ($this->api) {
					json_response(422, validation_errors());
				}
				else{
					render('posts/create', $data);
				}
			}
			else {
				$params = $this->input->post(NULL, TRUE);
				$params['slug'] = url_title($params['title']);
				$params['user_id'] = auth_user()['id'];
				$post = $this->post_model->create($params);
				$file = $this->upload_file([
					'post_id' => $post['id']
				]);
				if (!$this->api) {
					redirect('posts');
				}
				else {
					json_response(200, [
						'message' => 'Post created Successfully',
					]);
				}
			}
		}

		public function try_upload() {
			$config = [
				'upload_path' => './uploads/',
				'allowwed_types' => 'gif|jpg|png',
			];	
			$this->load->library('upload', $config);
			if (!$this->upload->do_upload())
			{
				return [
					'status' => FALSE,
					'error' => $this->upload->display_errors()
				];
			}
			else
			{
				return [
					'status' => TRUE,
					'data' => $this->upload->data()
				];
			}
		}

		public function upload_file ($params) {
			$file = $this->try_upload();

			if ($file['status'] === TRUE) {
				$params['path'] = $file['data']['file_name'];
				$this->file_model->create($params);
			}
			else {
				print_r($file['error']);
				render('posts/create');
			}
		}

		/**
		 * Fetch Post from db based on uri and render the edit page if validation rules have not been checked 
		 * or if validation fails
		 * Update post if validation is successful
		 * @param string
		 * @return void 
		 */
		public function edit ($slug) {
			validate([
				'title' => 'required',
				'body' => 'required'
			]);

			if (! $this->form_validation->run()) {
				$data['post'] = $this->post_model->getOne([
					'slug' => $slug,
				], TRUE);
	
				if (empty($data['post'])) {
					show_404();
				}
				else {
					$data['title'] = 'Edit Post';
					$data['heading'] = 'Edit: '. $data['post']['title'];
					$data['categories'] = $this->category_model->get();
					render('posts/edit', $data);
				}
			}
			else {
				$slug = $this->post_model->update([
					'slug' => $slug
				]);
				if ($this->api) {
					json_response(200, [
						'message' => 'Post Updated'
					]);
				}
				else {
					redirect('posts/'. $slug);
				}
			}
		}

		/**
		 * Delete post with a given $id from the database
		 * @param int
		 * @return void 
		 */
		public function delete ($id) {
			$this->post_model->delete($id);
			redirect('posts');
		}

		/**
		 * Fetch Posts in a Category with given uri from the database and render the by_category view
		 * @param string
		 * @return void
		 */
		public function categories ($slug) {
			$data['category'] = $this->category_model->posts([
				'slug' => $slug
			]);

			$data['title'] = ucfirst($data['category']['name']);

			render('posts/by_category', $data);
		}
	}
