# Code Igniter Blog With HMVC and Middleware Implementation
I started this project to track my Learning of Code Igniter. Coming from a background with [Laravel](https://laravel.com) I like the simplicity of CodeIgniter but there are a lot of things not supported out of the box. 

## Features
- Authentication System with support for Sessions and OAuth.
- Create, Read Update and Delete of Blog Posts by only authenticated Users.
- Implementation of a CodeIgniter middleware Library for handling Access control.

## Middleware Library
This middleware Library is modelled after [Laravel](https://laravel.com)'s Middleware System. It provisions access control for Routes and Controller Functions

### Usage
1. Copy the ````application/libraries/Middleware.php```` file from this repo and paste in the same directory of your CodeIgniter installation. You can either Autoload the Library or Load it Manually in your Constructor.

````php
// config/autoload.php
$autoload['libraries'] = array('middleware');
````
OR

````php
// in your controller
class Feelgood extends CI_Controller {
	public function __construct () {
		$this->load->library('middleware');
	}
}
````
2. In  ````application/helpers```` create a  ````middleware_helper.php```` file. You will specify your middleware functions within this helper Like so:

````php
// application/helpers/middleware_helper.php

function is_authenticated () {
	$CI =& get_instance();
	$CI->load->library('session')
	if ($CI->session->userdata('is_authenticated') === TRUE) {
		return TRUE;
	}
	else {
		redirect('/login');
	}
}
```` 

3. Assign middlewares to the routes being guarded in your ```` application/config/config.php```` in a middleware config item  Like so.

````php
// application/config/config.php

$config['middleware'] = array(
	'create-post' => 'is_authenticated', // name of the helper created
	'delete-post' => 'is_authenticated',
);
````

4. Now when you visit http://www.example.com/create-post without being logged in you will be redirected to the Login Page. You can specify any type of Check in your middlewares just make sure to return ````TRUE```` if the check is successful.

5. You can also guard functions in your Controllers by specifying which the middlewares to run and the function names to include or exempt in the checks. For Example:

````php
// in your controller constructor
$middlewares = ['is_authenticated', 'is_admin'];
$funtionNames = ['doSomething', 'saveTheWorld'];

$this->middleware->only($middlewares, $funtionNames);

// check the specified function names
````

OR

````php
// in your controller constructor
$middlewares = ['is_authenticated', 'is_admin'];
$funtionNames = ['doSomething', 'saveTheWorld'];

$this->middleware->except($middlewares, $funtionNames);

// check all other functions in the controller except the ones specified
````

I hope this helps someone out. For me it seems really useful for the lack of a built in sysytem for this. Feel free to adjust the middleware library as you deem fit. Cheers! 

