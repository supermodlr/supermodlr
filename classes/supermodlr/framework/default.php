<?php


class supermodlr_framework_default {

	protected $drivers = array();
	public $template_ext = '.php';
	
	
	//loads a config file
	public function load_config($file) {
		return unserialize(file_get_contents($file));
		
	}
	
	//throw error message
	public function error($message = '', $code = 0) {
		throw new Exception($message);
	}
	
	//get message based on message code / language
	public function message($key, $lang) {
		//if no message file has been loaded yet, load one
		if (!isset($this->messages)) {
			include 'messages.php';
			$this->messages = $messages;
		}
		
		return $this->messages[$key][$lang];
	}
	
	//load database driver
	public function get_driver($driver,$params = array()) 
	{
		if (isset($this->drivers[$driver])) 
		{
			return $this->drivers[$driver];

		} 
		else 
		{
			if (class_exists($driver)) {
				$this->drivers[$driver] = new $driver($params); 
				return $this->drivers[$driver];
			} else {
				return NULL;
			}

		}
	}
	
	public function find_file($file) {
		if (file_exists($file)) 
		{
			require_once $driver.'.php';
			return TRUE;
		} 
		else 
		{
			return FALSE;
		}
	}
	
	public function get_view($template = 'default', $theme = 'default', $media = 'web')
	{
	
	}
	
	public function render($View)
	{
	
	}
	
	public function bind($View,$name,$value)
	{
	
	}

	public function prepare_input_value($value)
	{
	
	}
	
	public function get_new_pk($model = NULL)	
	{
		return NULL;
	}
	
}