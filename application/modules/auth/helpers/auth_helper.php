<?php
/**
 * Get the currently authenticated user from the session
 * @return array
 */
function session_user() {
	if (session_authenticated()) {
		$CI =& get_instance();
		return $CI->session->userdata('user');
	}
	else {
		return FALSE;
	}
}

/**
 * Check if a user is authenticated via session
 * @return bool
 */
function session_authenticated () {
	$CI =& get_instance();
	if ($CI->session->userdata('is_authenticated') === TRUE) {
		return TRUE;
	}
	else {
		return FALSE;
	}
}

/**
 * Check if a user is authenticated via ouath
 * @return bool
 */
function api_authenticated () {
	$CI =& get_instance();
	$auth_header = $CI->input->get_request_header('Authorization', TRUE);
	$token = substr($auth_header, strpos($auth_header, "Bearer ") + 1);
	$user = $CI->auth_model->checkToken($token);
	if ($user != FALSE) {
		$CI->user = $user;
		return $user;
	}
	else {
		return FALSE;
	}
}

/**
 * Univeral helper for getting the currently authenticated user (session or oauth)
 * @return array
 */
function auth_user() {
	$CI =& get_instance();
	if ($CI->api) {
		return api_authenticated();
	}
	else {
		return session_user();
	}
}

function belongs_to_user ($user_id) {
	$CI =& get_instance();
	$user = auth_user();
	return $user['id'] === $user_id;
}
