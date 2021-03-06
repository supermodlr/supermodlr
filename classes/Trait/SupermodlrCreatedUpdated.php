<?php defined('SYSPATH') or die('No direct script access.');
/**
  * FileDescription: The Created Updated trait adds auto created datetime and updated datatime fields to a model
  */
trait Trait_SupermodlrCreatedUpdated {

    public static $__SupermodlrCreatedUpdated__scfg = array (
		'traits__SupermodlrCreatedUpdated__name' => 'SupermodlrCreatedUpdated',
		'traits__SupermodlrCreatedUpdated__label' => 'Created Updated Fields',
		'traits__SupermodlrCreatedUpdated__description' => 'The Created Updated trait adds auto created datetime and updated datatime fields to a model',    	
        'field_keys' => array (
            'created',
            'updated',
        ),
    );

    /**
     * check for updated and created fields and set them if not already set
     * 
     * @param mixed $params = array('this'=> $this, 'drivers'=> &$drivers, 'is_insert'=> &$is_insert, 'set'=> &$set, 'result', &$saves_result, 'messages'=> &$messages);
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function event__SupermodlrCreatedUpdated__save($params)
    {
        // Get fields
        $fields = $this->get_fields();

        $Datetime = new DateTime();
        $time = $Datetime->getTimestamp();

        // If the 'created' field exists, is not set, and this is an insert
        if (isset($fields['Created']) && !isset($params['set']['Created']) && $params['is_insert'])
        {
            // Set the current time as the created time
            $this->Created = $Datetime;
            $params['set']['Created'] = $this->Created;
        }

        // Get originally loaded data from db
        $loaded_data = $this->cfg('loaded_data');

        // If the 'updated' field exists and it matches the loaded value for updated and a different updated date was not sent to &$params['set']
        if (isset($fields['Updated']) && ((isset($params['set']['Updated']) && $params['set']['Updated'] === $loaded_data['Updated']) || !isset($params['set']['Updated'])))
        {
            // Set the current time as the updated time
            $this->Updated = $Datetime;
            $params['set']['Updated'] = $this->Updated;
        }

    }

}