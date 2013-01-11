<?php


class Supermodlr_Framework_Default {

    protected $drivers = array();
    
    //loads a config file
    public function load_config($file = NULL) {
        if ($file !== NULL && file_exists($file)) {
            return unserialize(file_get_contents($file));
        }
        
        
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
    

    public function prepare_input_value($value)
    {
    
    }
    
    public function get_new_pk($model = NULL)   
    {
        return NULL;
    }
    
}