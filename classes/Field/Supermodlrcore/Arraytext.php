<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_ArrayText extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Array , Trait_FieldDataType_String;

    public $name = 'ArrayText';
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