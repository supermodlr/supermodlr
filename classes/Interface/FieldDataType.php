<?php


interface Interface_FieldDataType {
    
    public function validate_datatype($value);

    /**
     * set_value method will convert any valid and/or convertible value to the format expected by the php object
     * 
     * @param mixed $value Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function set_value($value, $Model = NULL);

    /**
     * export_value takes value from set_value and returns it in the format expected for api usage
     * 
     * @param mixed $value Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function export_value($value, $Model = NULL);


    /**
     * storage_value takes value from set_value and returns it in the format expected by database/storage drivers
     * 
     * @param mixed $value Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function storage_value($value, $Model = NULL);
}