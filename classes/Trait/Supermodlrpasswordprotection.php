<?php defined('SYSPATH') or die('No direct script access.');
/**
  * FileDescription: Password Protection
  */
trait Trait_SupermodlrPasswordProtection {
    public static $__SupermodlrPasswordProtection__scfg = array(
        'field_keys' => array(
            'password',
        )
    );

    /**
     * compares a sent password against the stored password
     */
    public function check_password($raw_sent_password)
    {

        list($password, $salt) = explode('.', $this->password);

        return ($this->hash_password($raw_sent_password, $salt) === $password.'.'.$salt);
    }

    public function field_set_password($value)
    {
        return $this->hash_password($value);
    }


    public static function hash_password($raw_sent_password, $salt = NULL)
    {
        $salt = (is_null($salt)) ? $this->generate_salt() : $salt;
        $encrypted_password = crypt($raw_sent_password, $salt);
        return $encrypted_password.'.'.$salt;
    }

    public function generate_salt()
    {
        return '12345';
    }

    /**
     * on-before-save hook
     * encrypts the password if its been changed
     */
    public function event__trait__SupermodlrPasswordProtection__save($params) 
    {
        fbl('event__trait__supermodlrpasswordprotection__save', 'on save');

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
