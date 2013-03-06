<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Name extends Field implements Interface_Fieldstorage, Interface_Fielddatatype
{
    use Trait_Fieldstorage_Single, Trait_Fielddatatype_String;    
    public $name = 'name';
    public $datatype = 'string';
    public $storage = 'single';    
    public $multilingual = FALSE;
    public $charset = 'UTF-8';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;
    public $core = TRUE;

}