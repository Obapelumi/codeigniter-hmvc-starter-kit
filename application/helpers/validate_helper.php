<?php

		/** 
		 * Checks through each rule in the array and performs CodeIgniter validation 
		 * @param array
		 * @return void
		 */
		 function validate ($rules) {
			$CI =& get_instance();
			foreach ($rules as $key => $value) {
				$CI->form_validation->set_rules($key, ucfirst($key), $value);
			}

			if ($CI->api) {
				$CI->form_validation->set_error_delimiters('', '');
			}
		}
