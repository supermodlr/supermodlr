<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Singlenumber extends Field implements Interface_Fieldstorage, Interface_Fielddatatype
{
    use Trait_Fieldstorage_Single, Trait_Fielddatatype_Int;

    public $name = 'singlenumber';
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