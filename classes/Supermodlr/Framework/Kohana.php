<?php 

class Supermodlr_Framework_Kohana extends Supermodlr_Framework_Default {
    
    private $config = NULL;

    public function load_config($file = NULL) 
    {

        if ($this->config === NULL) 
        {
                $this->config = Kohana::$config->load('supermodlr');
        }
        return (array) $this->config;
    }

    public function prepare_input_value($value) 
    {
        return HTML::chars($value);
    }
    
    public function Supermodlr_root()
    {
        return MODPATH.'supermodlr'.DIRECTORY_SEPARATOR;
    }
    
    public function saved_classes_root()
    {
        return APPPATH.'classes'.DIRECTORY_SEPARATOR;
    }   
    

    /**
     * returns a valid supermodlruser or false for use in access control methods
     * 
     * @access public
     *
     * @return bool|Supermodlruser Returns FALSE if no user, returns an instance of Supermodlruser if a user was found.
     */
    public function get_user()
    {
fbl('here');
        $Request = Request::current();
        if (method_exists($Request, 'get_user') && $Request->get_user() instanceof Model_Supermodlruser)
        {
            return $Request->get_user();
        }
        else if (isset($Request->user) && $Request->user instanceof Model_Supermodlruser)
        {
            return $Request->user;
        }
        // Bind a dummy admin user to supermodlr.  This is expected to be overwritten by an application if it has users.
        else if (Kohana::$environment !== Kohana::PRODUCTION)
        {       
        fbl('set admin'); 
            $User = new Model_Supermodlruser();
            $User->useraccesstags = array('admin');
            return $User;
        }
        return FALSE;
    }
}