<?php

class Pages extends MY_Controller {

	public function render ($view, $data) {
		if (!file_exists(APPPATH. 'views/'.$view.'.php')) {
			show_404();
		}
		$this->load->view('templates/header', $data);
		$this->load->view($view, $data);
		$this->load->view('templates/footer', $data);	
	}

	public function view ($page = 'home') {
		$data['title'] = ucfirst($page);

		$this->render('pages/'.$page, $data);
	}

	public function about () {
		$this->render('pages/about', [
			'title' => 'About'
		]);
	}
}
