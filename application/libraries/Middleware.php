<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Middleware {

	private $CI;
	private $middlewares_from_config = [
		// 'create-post' => ['auth'],
		// 'edit-post' => ['auth'],
	];
	private $current_route;
	private $current_class_name;
	private $current_method_name;

	/**
	 * Class variables are initialized by calling initialize attributes
	 * The constructor takes an optional parameter $middlewares and calls the execute_middlewares function
	 * if it is specified. This can be placed in invidual controllers to block out all functions.
	 * @param array $middlewares to be executed on current controller
	 * @return bool 
	 */
    public function __construct(array $middlewares = [])
    {
		$this->initialize_attributes();
		
		if (count($middlewares) > 0) {
			return $this->execute_middlewares($middlewares);
		}
	}

	/**
	 * This checks the current route against those in the middleware.php config file
	 * It calls execute_middlewares() if there are middlewares specified for the current route.
	 * @return bool
	 */
	public function run () {
		$routes = array_keys($this->middlewares_from_config);

		if (array_key_exists($this->current_route, $this->middlewares_from_config)) {
			$applicable_middlewares = $this->middlewares_from_config[$this->current_route];
			return $this->execute_middlewares($applicable_middlewares);
		}
		else {
			return true;
		}
	}

	/**
	 * This recieves an array of middlewares to be executed only for function names specified in the second parameter
	 * It calls execute_middlewares() if the current route matches to a function specified in the $functionNames array
	 * @param array $middlewares to be executed
	 * @param array $functionNames to be protected by middleware
	 * @return bool
	 */
	public function only (array $middlewares, array $functionNames) {
		if (in_array($this->current_method_name, $functionNames)) {
			return $this->execute_middlewares($middlewares);
		}
		else {
			return true;
		}
	}

	/**
	 * This recieves an array of middlewares to be executed for function names not specified in the second parameter
	 * It calls execute_middlewares() if the current route matches to a function specified in the $functionNames array
	 * @param array $middlewares to be executed
	 * @param array $functionNames to be exempted from middleware checks
	 * @return bool
	 */
	public function except (array $middlewares, array $functionNames) {
		if (! in_array($this->current_method_name, $functionNames)) {
			return $this->execute_middlewares($middlewares);
		}
		else {
			return true;
		}
	}

	/**
	 * This function takes an array of middleware names and loads the middleware helper.
	 * It then executes in sequence functions that match the middleware names specified
	 * @param array $middlewares to be executed
	 * @return bool
	 */
	public function execute_middlewares (array $middlewares) {
		if (count($middlewares) > 0) {
			$this->CI->load->helper('middleware');
			$next = true;
			foreach ($middlewares as $middleware) {
				if ($next === true) {
					if (function_exists($middleware)) {
						call_user_func($middleware);
					}
					else {
						show_error(500, 'Middleware not defined... Define middleware in application/helpers/middleware_helper.php');
					}
				}
				else {
					show_error(401, 'Access Denied');
				}
			}
			if ($next === true) {
				return true;
			}
		}
		else {
			return true;
		}	
	}

	private function get_applicable_middlewares () {
		return $this->middlewares_from_config[$this->current_route];
	}

	private function initialize_attributes () {
		$this->CI =& get_instance();
		// $this->CI->config->load('middleware');
		$this->middlewares_from_config = config_item('middleware');
		$this->CI->load->library('router');
		$this->current_route = $this->CI->uri->uri_string();
		$this->current_class_name = $this->CI->router->fetch_class();
		$this->current_method_name = $this->CI->router->fetch_method();
	}	
}
