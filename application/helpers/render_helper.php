<?php
/**
 * Render a page in the view folder 
 * @param string
 * @param array
 * @return void 
 */
function render ($view, $data = ['title' => 'CI Tutorial']) {
	$CI =& get_instance();

	if ($CI->api == TRUE) {
		json_response(200, $data);
	}
	else {
		// if (!file_exists(APPPATH. 'views/'.$view.'.php')) {
		// 	show_404();
		// }

		$CI->load->view('templates/header', $data);
		$CI->load->view($view, $data);
		$CI->load->view('templates/footer', $data);	
	}
}
