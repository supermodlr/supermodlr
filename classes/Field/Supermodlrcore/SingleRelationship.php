<?php defined('SYSPATH') or die('No direct script access.');
class Field_Supermodlrcore_SingleRelationship extends Field implements Interface_FieldStorage, Interface_FieldDataType
{
    use Trait_FieldStorage_Single, Trait_FieldDataType_Relationship;
    public $name = 'SingleRelationship';
    public $label = 'SingleRelationship';
    public $description = 'SingleRelationship';
    public $datatype = 'relationship';
    public $charset = 'UTF-8';
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $maxlength = 510;
    public $nullvalue = FALSE;
    public $hidden = FALSE;
    public $validtestvalue = '';
    public $private = FALSE;
    public $readonly = FALSE;

}