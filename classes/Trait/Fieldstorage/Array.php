<?php

trait Trait_FieldStorage_Array {

    public function validate_storage($value = NULL) {
        return (is_array($value) && !$this->is_assoc($value));

    }

    public function validate_datatype_values($value) {
        foreach ($value as $i => $v)
        {
            $valid = $this->validate_datatype($v);
            if ($valid !== TRUE)
            {
                return FALSE;
            }
        }
    }

    public function store_value($value,$method = 'set', $Model = NULL)
    {
        $values = array();
        $method = $method.'_value';
        foreach ($value as $i => $v)
        {
            $values[] = $this->$method($v);
        }
        return $values;
    }
}