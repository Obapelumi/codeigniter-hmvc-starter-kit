<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

	public $api = false;
	public $user = FALSE;

	public function __construct() {
		parent::__construct ();
		$this->api = is_valid_api_request();
	}

	public function register () {
		$data['title'] = 'Register';

		validate([
			'email' => 'required|valid_email|is_unique[users.email]',
			'password' => 'required',
			'confirm_password' => 'required|matches[password]'
		]);
		
		if ($this->form_validation->run() === FALSE) {
			render('auth/register', $data);
		}
		else {
			$credentials = $this->input->post(NULL, TRUE);
			unset($credentials['confirm_password']);
			$credentials['password'] = md5($credentials['password']);

			$user = $this->auth_model->register($credentials);

			if (!empty($user)) {
				if ($this->api) {
					return json_response(200, [
						'message' => 'Successfully registered',
						'data' => $user
					]);
				}
				else {
					$this->session->set_flashdata('success_registered', 'Your account has been created');
					$this->session->set_userdata([
						'is_authenticated' => TRUE,
						'user' => $user
					]);
					redirect('/dasboard');
				}
			}
			else {
				if ($this->api) {
					return json_response(500, [
						'message' => 'Registeration Failed',
					]);
				}
				else {
					$this->session->set_flashdata('error_registeration_failed', 'Registeration Failed');
					redirect('/dashboard');	
				}
			}
		}	
	}

	public function login () {
		$data['title'] = 'Login';

		validate([
			'email' => 'required',
			'password' => 'required'
		]);
		
		if ($this->form_validation->run() === FALSE) {
			if ($this->api) {
				json_response(422, [
					'message' => validation_errors()
				]);
			}
			else {
				render('auth/login', $data);
			}
		}
		else {
			$credentials = $this->input->post(NULL, TRUE);
			$credentials['password'] = md5($credentials['password']);

			$user = $this->auth_model->checkCredentials($credentials);

			if ($user == FALSE) {
				if ($this->api) {
					return json_response(401, [
						'message' => 'Unauthorized',
					]);
				}
				else {
					$this->session->set_flashdata('error_login_failed', 'Your User Name or Password is incorrect');
					redirect('auth/login');
				}
			}
			else {
				if ($this->api) {
					return json_response(200, [
						'message' => 'Login Successful',
						'user' => $user
					]);
				}
				else {
					$this->session->set_userdata([
						'is_authenticated' => TRUE,
						'user' => $user
					]);
	
					redirect('/dashboard');
				}
			}
		}
	}

	public function logout () {
		if ($this->input->post('logout') === 'logout') {
			$this->session->unset_userdata(['is_authenticated', 'user']);
			redirect('/');
		}
	}
}
