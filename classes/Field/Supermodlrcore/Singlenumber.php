<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Singlenumber extends Field 
{
	public $name = 'singlenumber';
	public $datatype = 'int';
	public $multilingual = FALSE;
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