<?php

trait Trait_FieldDataType_Object {
    public function validate_datatype($value) {
        return (is_array($value) || $value instanceof Supermodlr);

    }

    public function set_value($value, $args = NULL)
    {
        if ($this->validate_datatype($value) === FALSE) 
        {
            throw new Exception('Invalid value, cannot set');
        }
        return $value; 
    }      

    public function export_value($value, $args = NULL) 
    {
        return $value;
    }


    public function storage_value($value, $args = NULL) 
    {
        return $value;
    }      

    public function get_submodel()  
    {
        if (is_array($this->submodel))
        {
            $model_class_name = $this->submodel['_id'];
            $Dummy_Model = $model_class_name::factory();
            return $Dummy_Model;
        }
        else if ($value instanceof Supermodlr) 
        {
            return $value; 
        }
    }

}
