<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Arraytext extends Field implements Interface_Fieldstorage, Interface_Fielddatatype
{
    use Trait_Fieldstorage_Array , Trait_Fielddatatype_String;

    public $name = 'arraytext';
    public $datatype = 'string';
    public $multilingual = FALSE;
    public $charset = 'UTF-8';
    public $storage = 'array';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $maxlength = '255';
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;

}