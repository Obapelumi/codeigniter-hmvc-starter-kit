<?php

/**
 * Sends json response back
 * @param string
 * @param array
 */
function json_response ($status, $data, $exit = TRUE) {
	$CI =& get_instance();
	$CI->output->set_content_type('application/json');
	$CI->output->set_status_header($status);
	$CI->output->set_output(json_encode($data));
}

function is_valid_api_request () {
	$CI =& get_instance();
	$api_key = $CI->input->get_request_header('Accept', TRUE);
	return $api_key === 'application/json';
}
