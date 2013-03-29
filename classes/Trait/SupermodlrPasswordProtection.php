<?php defined('SYSPATH') or die('No direct script access.');
/**
  * FileDescription: Password Protection
  */
trait Trait_SupermodlrPasswordProtection {
    public static $__SupermodlrPasswordProtection__scfg = array (
		'traits__SupermodlrPasswordProtection__name' => 'SupermodlrPasswordProtection',
		'traits__SupermodlrPasswordProtection__label' => 'Password Protection',
		'traits__SupermodlrPasswordProtection__description' => 'Password Protection',    	
        'field_keys' => array (
            'password',
        ),
    );

    /**
     * check_password compares a sent password against the stored password
     * 
     * @param mixed $raw_sent_password Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function check_password($raw_sent_password)
    {

        list($password, $salt) = explode('.', $this->password);

        return ($this->hash_password($raw_sent_password, $salt) === $password.'.'.$salt);
    }

    /**
     * field_set_password
     * 
     * @param mixed $value Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function field_set_password($value)
    {
        return $this->hash_password($value);
    }

    /**
     * hash_password
     * 
     * @param mixed $raw_sent_password Description.
     * @param mixed $salt              Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
    public static function hash_password($raw_sent_password, $salt = NULL)
    {
        $salt = (is_null($salt)) ? $this->generate_salt() : $salt;
        $encrypted_password = crypt($raw_sent_password, $salt);
        return $encrypted_password.'.'.$salt;
    }

    /**
     * generate_salt
     * 
     * @access public
     *
     * @return mixed Value.
     */
    public function generate_salt()
    {
        return '12345';
    }

    /**
     * event__trait__SupermodlrPasswordProtection__save on-before-save hook. encrypts the password if its been changed
     * 
     * @param mixed $params Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function event__Trait_SupermodlrPasswordProtection__save($params) 
    {
        $password = $params['set']['password'];

        // If this is a new user, we encrypt their password
        if ($params['is_insert'])
        {
            $params['set']['password'] = $this->hash_password($password);
        }
        else
        {
            // If this is an existing user, we check to see if password has been changed on this object
            $changes = $this->changes();

            // If this password has changed
            if (isset($changes['updated']['password']))
            {
                $params['set']['password'] = $this->hash_password($password);
            }
        }
    }
}
