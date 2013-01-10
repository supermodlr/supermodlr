<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_Useraccesstags extends Field 
{
    public $name = 'useraccesstags';
    public $datatype = 'string';
    public $multilingual = FALSE;
    public $charset = 'UTF-8';
    public $storage = 'array';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $private = TRUE;
    public $readonly = FALSE;
    public $core = TRUE;
    public $defaultvalue = array('auth');

}