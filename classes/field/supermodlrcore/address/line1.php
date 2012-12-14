<?php defined('SYSPATH') or die('No direct script access.');
class field_supermodlrcore_address_line1 extends field 
{
	public $name = 'line1';
	public $datatype = 'string';
	public $multilingual = TRUE;
	public $charset = '';
	public $storage = 'single';
	public $required = FALSE;
	public $unique = FALSE;
	public $searchable = FALSE;
	public $filterable = FALSE;
	public $nullvalue = FALSE;
	public $hidden = FALSE;
	public $extends = array (
  'model' => 'field',
  '_id' => 'field_supermodlrcore_name',
);
	public $parentfield = array (
  'model' => 'field',
  '_id' => 'field_supermodlrcore_address',
);
	public $private = FALSE;
	public $readonly = FALSE;

}