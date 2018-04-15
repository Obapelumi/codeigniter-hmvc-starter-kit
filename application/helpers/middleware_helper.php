<?php defined('BASEPATH') OR exit('No direct script access allowed');

function auth () {
	if (session_authenticated()) {
		return true;
	}
	else {
		redirect('/auth/login');
	}
}
