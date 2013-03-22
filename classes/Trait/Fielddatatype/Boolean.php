<?php

trait Trait_FieldDataType_Boolean {
    public function validate_datatype($value) {
        return (is_bool($value) || in_array(strtolower($value), array('true','false',1,0,'1','0')));

    }
    public function set_value($value, $Model = NULL)
    {
        if ($this->validate_datatype($value) === FALSE) 
        {
            throw new Exception('Invalid value, cannot set');
        }
        if (!is_bool($value))
        {
            if ($value === '1' || $value === 1 || strtolower($value) === 'true')
            {
                $value = TRUE;
            }
            else
            {
                $value = FALSE;
            }
        }
        return (bool) $value; 
    }      

    public function export_value($value, $Model = NULL) 
    {
        return (bool) $value;
    }


    public function storage_value($value, $Model = NULL) 
    {
        return (bool) $value;
    }        
}
