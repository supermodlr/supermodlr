<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Singlerelationship extends Field implements Interface_Fieldstorage, Interface_Fielddatatype
{
    use Trait_Fieldstorage_Single , Trait_Fielddatatype_Relationship;

    public $name = 'singlerelationship';
    public $datatype = 'relationship';
    public $multilingual = FALSE;
    public $charset = 'UTF-8';
    public $storage = 'single';
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