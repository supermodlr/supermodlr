<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_SingleNumber extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_Int;

    public $name = 'SingleNumber';
    public $datatype = 'int';
    public $multilingual = FALSE;
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $maxlength = 4;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;

}