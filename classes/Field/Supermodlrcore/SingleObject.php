<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_SingleObject extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_Object;
    public $name = 'SingleObject';
    public $label = 'SingleObject';
    public $description = 'SingleObject';
    public $datatype = 'object';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $maxlength = 510;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;

}