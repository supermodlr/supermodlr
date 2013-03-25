<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore__Id extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_String;
    public $name = '_id';
    public $label = 'Id';
    public $description = 'Id';
    public $datatype = 'string';
    public $charset = 'UTF-8';
    public $storage = 'single';
    public $required = TRUE;
    public $unique = TRUE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE;
    public $hidden = TRUE;
    public $private = FALSE;
    public $readonly = TRUE;

}