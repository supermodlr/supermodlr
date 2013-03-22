<?php

trait Trait_FieldDataType_Relationship {

    public function validate_datatype($value) 
    {
        if (is_array($value))
        {
            return (isset($value['model']) && is_string($value['model']) && isset($value['_id']) && count($value) === 2);
        }
        else if ($value instanceof Supermodlr && isset($this->source) && is_array($this->source)) 
        {
            $ok = FALSE;
            foreach ($this->source as $source)
            {
                $model_class_name = Supermodlr::name_to_class_name($source['model']);
                if ($value instanceof $model_class_name)
                {
                    $ok = TRUE;
                    break;
                }
            }
            return $ok;
        }
        else
        {
            return FALSE;
        }
        

    }

    /**
     * set_value sets a value onto a 
     * 
     * @param mixed $value Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set_value($value, $Model = NULL)
    { 
        if ($this->validate_datatype($value) === FALSE) 
        {
            throw new Exception('Invalid value, cannot set');
        }

        if (is_array($value))
        { 
            $model_class_name = Supermodlr::name_to_class_name($value['model']);
            $Dummy_Model = $model_class_name::factory();
            $pk = $Dummy_Model->cfg('pk_name');
            $Dummy_Model->$pk = $value['_id'];
            return $Dummy_Model;
        }
        else if ($value instanceof Supermodlr) 
        {
            return $value; 
        }
        // This should never happen since validate_datatype should catch this condition, but we'll check for it anyway
        else
        {
            throw new Exception('Invalid value, cannot set');
        }
        
    }    

    public function export_value($value, $Model = NULL) 
    {
        if (is_array($value))
        {
            return $value;
        }
        else if ($value instanceof Supermodlr)
        {
            return array('model'=> $value->get_name(), '_id'=> $value->pk_value());
        }
        
    }


    public function storage_value($value, $Model = NULL) 
    {
        if (is_array($value))
        {
            return $value;
        }
        else if ($value instanceof Supermodlr)
        {
            return array('model'=> $value->get_name(), '_id'=> $value->pk_value());
        }
    }        
}


