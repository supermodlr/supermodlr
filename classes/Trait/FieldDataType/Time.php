<?php

trait Trait_FieldDataType_Time {
    public function validate_datatype($value) {
        return ((is_object($value) && $value InstanceOf DateTime));
    }

    public function set_value($value, $Model = NULL)
    {
        if ($this->validate_datatype($value) === FALSE) 
        {
            throw new Exception('Invalid value, cannot set');
        }
        return $value; 
    }

    public function export_value($value, $Model = NULL) 
    {
        return $value->format('H:i:s');
    }


    public function storage_value($value, $Model = NULL) 
    {
        return $value;
    }        
}
