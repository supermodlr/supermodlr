<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Singlemixed extends Field implements Interface_Fieldstorage, Interface_Fielddatatype
{
    use Trait_Fieldstorage_Single, Trait_Fielddatatype_Boolean;

    public $name = 'singleboolean';
    public $datatype = 'boolean';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $maxlength = '4';
    public $nullvalue = FALSE;
    public $validation = array (
  0 => 
  array (
    0 => 'numeric',
    1 => 
    array (
    ),
  ),
);
    public $hidden = FALSE;
    public $private = FALSE;
    public $readonly = FALSE;

}