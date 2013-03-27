<?php defined('SYSPATH') or die('No direct script access.');
class Field_SupermodlrUser_UserAccessTags extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Array, Trait_FieldDataType_String;
    public $name = 'UserAccessTags';
    public $label = 'User Access Tags';
    public $description = '';
    public $datatype = 'string';
    public $charset = 'UTF-8';
    public $storage = 'array';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $nullvalue = FALSE;
    public $hidden = TRUE;
    public $private = TRUE;
	public $model = array ( 'model' => 'model', '_id' => 'Model_SupermodlrUser',);    
	public $readonly = FALSE;
}