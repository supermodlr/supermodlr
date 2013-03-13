<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Useraccesstags extends Field implements Interface_Fieldstorage, Interface_Fielddatatype
{
    use Trait_Fieldstorage_Array, Trait_Fielddatatype_String;

    public $name = 'useraccesstags';
    public $datatype = 'string';
    public $multilingual = FALSE;
    public $charset = 'UTF-8';
    public $storage = 'array';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $nullvalue = FALSE;
    public $hidden = TRUE;
    public $private = TRUE;
    public $readonly = FALSE;
    public $core = TRUE;
    public $defaultvalue = array('auth');
    public $stored = FALSE;
}