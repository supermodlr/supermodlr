<?php


interface Interface_FieldStorage {
    
    public function validate_storage($value);

    public function validate_datatype_values($value);

    /**
     * store_value
     * 
     * @param mixed  $value  Description.
     * @param string $method allowed values are 'set', 'export', or 'storage' corresponding with methods made available by Interface_FieldDataType's
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function store_value($value, $method = 'set', $Model = NULL);
}