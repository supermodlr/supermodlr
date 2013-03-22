<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Label extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_String;    
    public $name = 'label';
    public $datatype = 'string';
    public $multilingual = TRUE;
    public $charset = 'UTF-8';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;
    public $core = TRUE;

    /**
     * get_defaultvalue
     * 
     * @access public
     *
     * @return mixed Value.
     */
    public function get_defaultvalue()
    {
        if (isset($this->model) && is_object($this->model) && $this->model instanceof Supermodlr && isset($this->model->name))
        {
            return str_replace('_',' ',ucfirst($this->model->name));
        }
        else
        {
            return '';
        }
    }
}