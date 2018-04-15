<?php

class Auth_model extends CI_Model {

	public function __construct () {
		$this->table_name = 'users';
	}

	public function register ($credentials) {
		if ($this->db->insert('users', $credentials)) {
			$user = $this->getOne([
				'email' => $credentials['email']
			]);
			$user['auth_token'] = $this->createToken($user['id']);
			return $user;
		}
	}
	
	public function checkCredentials ($credentials) {
		$user =  $this->getOne($credentials);

		if (empty($user)) {
			return false;
		}
		else {
			$token = $this->createToken($user['id']);
			$user['auth_token'] = $token;
			return $user;
		}
	}

	public function checkToken ($auth_token) {
		$token = $this->db->get_where('auth_tokens', [
			'token' => $auth_token
		])->row_array();

		if(!empty($token)) {
			if (strtotime($token['expires_at']) < time() ) {
				$this->deleteToken($auth_token);
				return false;
			}
			else {
				return $this->getOne([
					'id' => $token['user_id']
				]);
			}
		}
		else {
			return false;
		}
	}

	public function deleteToken($token) {
		$this->db->where('token', $token)->delete('auth_tokens');
		return true;
	}

	public function getOne ($params) {
		return $this->db->get_where('users', $params)->row_array();
	}

	private function generate_api_token() {
		do {
			$salt = hash('sha512', time().openssl_random_pseudo_bytes(20));
			$token = substr($salt, 0, 200);
		}
		while ($this->check_if_token_exists($token));

		return $token;
	}

	private function check_if_token_exists ($token) {
		return $this->db->where('token', $token)->count_all_results('auth_tokens') > 0;
	}

	private function createToken ($user_id) {
		$token = $this->generate_api_token();
		$this->db->insert('auth_tokens', [
			'token' => $token,
			'user_id' => $user_id,
			'expires_at' => date("Y-m-d H:i:s", strtotime("+30 days"))
		]);
		return $this->db->get_where('auth_tokens', ['token'=> $token])->row_array();
	}
}
