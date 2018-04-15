<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Specify Middlewares for each route. e.g 
 * $middleware['create-posts'] = ['is_authenticated', 'can_create_posts'];
 */

$middleware['create-post'] = ['auth'];

$middleware['edit-post'] = ['auth'];

// $middleware_matches = [
// 	'create-post' => ['auth'],
// 	'edit-post' => ['auth'],
// ];

