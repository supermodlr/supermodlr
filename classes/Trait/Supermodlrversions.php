<?php defined('SYSPATH') or die('No direct script access.');
/**
  * FileDescription: Versions
  */
trait Trait_SupermodlrVersions {
    public static $__SupermodlrVersions__scfg = array(
            'field_keys' => array(
        )
    );


    public function event__trait_SupermodlrVersions__save_end($params)
    {

        $changed = $this->changed();

        // If there were any changes, save a copy
        if (count($changed) >= 0)
        {
            
            $Versionhistory = new Model_Versionhistory();

            // Change the db_name
            $db_name = $this->get_name().'_versionhistory';
            $Versionhistory->cfg('db_name', $db_name);

            // Add the model id and changes
            $Versionhistory->set('modelid', $this->_id);
            $Versionhistory->set('changes', $changed);

            // Save the changes
            $r = $Versionhistory->save();
            
        }
    }

}
