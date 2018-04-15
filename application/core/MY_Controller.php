<?php

	class MY_Controller extends CI_Controller {
		/**
		 * Whether or not the controller was called from the api module
		 * Defaults to false 
		 */
		public $api = false;

		public function __construct() {
			parent::__construct ();
			$this->api = is_valid_api_request();
			$this->middleware->run();
		}
	}
