<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route = [
	'default_controller'=> 'pages/view',
	'404_override' => '',
	'translate_uri_dashes' => FALSE,

	'dashboard' => 'auth/dashboard/index',
	'posts' => 'posts/index',
	'posts/(:any)' => 'posts/show/$1',
	'create-post' => 'posts/create',
	'edit-post/(:any)' => 'posts/edit/$1',
	'(:any)' => 'pages/view/$1',
];
