<?php defined('SYSPATH') or die('No direct script access.');
class field_supermodlrcore_address extends field 
{
	public $name = 'address';
	public $datatype = 'object';
	public $multilingual = FALSE;
	public $charset = '';
	public $storage = 'keyed_array';
	public $required = FALSE;
	public $unique = FALSE;
	public $searchable = FALSE;
	public $filterable = FALSE;
	public $nullvalue = FALSE;
	public $hidden = FALSE;
	public $fields = array (
  0 => 
  array (
    '_id' => 'field_supermodlrcore_address_line1',
    'model' => 'field',
  ),
  1 => 
  array (
    '_id' => 'field_supermodlrcore_address_city',
    'model' => 'field',
  ),
  2 => 
  array (
    '_id' => 'field_supermodlrcore_address_state',
    'model' => 'field',
  ),
);
	public $private = FALSE;
	public $readonly = FALSE;

}