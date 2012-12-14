<?php defined('SYSPATH') or die('No direct script access.');
class field_supermodlrcore_singletext extends field 
{
	public $name = 'singletext';
	public $datatype = 'string';
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