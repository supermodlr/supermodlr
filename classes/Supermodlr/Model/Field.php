<?php

class Supermodlr_Model_Field extends Supermodlr {
    public static $__scfg = array(
        'name'=> 'field',
        'label'=> 'Field',
        'description' => 'This model defines how fields are stored in the database and how the field class code is generated',        
        'field_keys'  => array(
            '_id',
            'label',
            'name',
            'extends',          
            'description',
            'datatype',
            'source',
            'storage',
            'multilingual',
            'charset',
            //'submodel',
            //'submodeladd',
            'required',
            'unique',
            'searchable',
            'filterable',
            'values',
            'filters',
            'defaultvalue',
            'nullvalue',
            'validation',
            'messages',
            'templates',
            'hidden',
            'validtestvalue',
            'invalidtestvalues',
            'access',
            'private',
            'model',
            'maxlength',
            'conditions',
            'readonly',

        ),
        'overall_required' => array(
            '_id' => TRUE,
            'name' => TRUE,
            'datatype' => TRUE,
            'multilingual'=> TRUE,
            //'charset' => array('datatype'=> 'string'),
            'storage' => TRUE,
            'required' => TRUE,
            'unique' => TRUE,
            'searchable' => TRUE,
            'filterable' => TRUE,
            'nullvalue' => TRUE,
            //'model' => TRUE,
        ),
        'uninherited' => array(
            '_id',
            'name',
            'extends',
            'model',
        ),
        'inherited' => array(
            'datatype',
            'storage',
            'multilingual',
            'unique',
        ),
        'core_prefix'=> 'Supermodlrcore',       
    );

    /*choices
        - allow validate event to hack required values in from parent classes
            pros - easy
            cons - data stored in db doesn't reflect an accurate model (major issue)
            
        - remove required prop from all fields
            pros - easier
            cons - how do i ensure that fields are not entered that don't have the proper data when merged in classes and extensions?
                    a   - create a cfg option on model_field that lists all overall_required fields.  
                        - add a special validate event that ensures all these values are set amoung the class and all its parents
    
        create new field model "test" which by default extends 'field'
        overall required enforces all its rules on the first child class of 'test'
        if i make a new field model "test1 extends test", it will always at least inheiret the valid set from the first child
        so does this validation only need to happen on the first child? no because any other child could attempt to unset any of the already set and validated fields
    
    
    */
        //$params = array('this'=> $this, 'drivers'=> &$drivers, 'is_insert'=> &$is_insert, 'set'=> &$set, 'result', &$saves_result, 'messages'=> &$messages);
/*  public function event__model_field__save($params)
    {       
        //echo 'event__model_field__save';
        //if this field can have sub fields, it is specific to a model, and fields were sent (meaning they were expected to inheirit) and we are extending another object field
        if ($this->datatype == 'object' && $this->storage == 'keyed_array' && !isset($this->fields) && !$this->is_core() && isset($this->extends) && $this->extends !== NULL && isset($this->extends['_id'])) 
        {
            $parent_class = $this->extends['_id'];
            $parent = new $parent_class();
            $subfields = $parent->fields;

//var_dump($subfields);

            $new_subfields = array();

            //create a model copy for each sub field
            foreach ($subfields as $subfield)
            {
                //create a new model specific version for this field
                $Model_Field = $this->create_subfield_from_data($subfield);

//var_dump($Model_Field->to_array());

                if ($Model_Field->get_class_name() != $subfield['_id'])
                {
                    $pk_name = $this->cfg('pk_name');
                    $Model_Field->$pk_name = $Model_Field->get_class_name();
                    //save the model_field object and create the file
                    $saved = $Model_Field->save();      
                    //var_dump($saved);
                    if ($saved->ok() !== TRUE)
                    {
                        throw new Exception('event__model_field__save SAVE FAILED '.var_export($Model_Field->to_array(),TRUE).var_export($saved->messages(),TRUE));
                    }   


                    $new_subfields[] = array("model"=> "field", "_id"=> $Model_Field->$pk_name);
                }
                else
                {
                    //echo 'skipped';
                }

            }
            //set new model-specific field ids as the fields property for this parent field
            $params['set']['fields'] = $new_subfields;
            $this->fields = $new_subfields;

        }
        //var_dump($params['set']);

    }*/

    //load all default field values from the class(es) this field extends
    public function event__model_field__validate_end($params)
    {
        //do not run this event if validate was called from this method
        if ($params['self'] === 'Model_Field::event__model_field__validate_end')
        {
            return ;
        }
        //get data for this field model
        $data = $params['data'];
        
        //if this field extends at least one other field
        if (isset($this->extends) && $this->extends instanceOf Supermodlr)
        { 
            //get the direct parent class name
            $class_name = $this->extends->pk_value();

            //ensure the class exists
            if (class_exists($class_name))
            {
                //create a dummy version of this field to get all the properties
                $parent_field = new $class_name();

                //loop through all set values on the parent
                foreach ($parent_field as $field_prop => $value)
                {
                    //if the value is not set on this model 
                    if (!array_key_exists($field_prop,$data) && $value !== NULL)
                    {
                        //set the defaulted value from its parent
                        $data[$field_prop] = $value;
                    }
                }               
            }
                
        }
        
        //get sent fields
        $fields = $params['fields'];
        $overall_required = $this->cfg('overall_required');
        foreach ($overall_required as $field_key => $required)
        {
            if (isset($fields[$field_key])) {
                $fields[$field_key]->required = $required;  
            }
            
        }
        
        $Status = $this->validate($data,$fields,'Model_Field::event__model_field__validate_end');
        
        $params['result'] = $Status->ok();
        $params['messages'] = $Status->messages();
    }
    
    
    
    //if this object is deleted, delete the file
    public function event__model_field__delete_end($params)
    {
        //if the delete worked
        if ($params['result'] === TRUE)
        {
            //get file path
            $full_file_path = $this->get_class_file_path();
            
            if (file_exists($full_file_path))
            {
                //remove the field file
                $deleted = unlink($full_file_path);
            }
            else
            {
                $deleted = true;
            }

            
            if (!$deleted)
            {
                throw new Exception('model_field::event__model_field__delete_end DELETE FILE FAILED ');
            }                       
            
            //get dir
            $file_info = pathinfo($full_file_path);
            
            //if we can read this dir
            if (is_dir($file_info['dirname']) && is_readable($file_info['dirname'])) 
            {
                //figure out if this dir is empty
                $empty = (count(scandir($file_info['dirname'])) == 2);  
                if ($empty)
                {
                    //remove dir if its empty
                    $dir_removed = rmdir($file_info['dirname']);
                    if (!$dir_removed)
                    {
                        throw new Exception('model_field::event__model_field__delete_end DELETE DIR FAILED ');
                    }                       
                }               
            }               
            
            //@todo catch and handle error
        }
    }


    //if we want to "auto-add" the submodel based on the parent model stored in submodeladd
    /*public function event__model_field__submodeladd__added($params) {
        //if a sub model isn't already set
        if (!isset($this->sudmodel) || $this->sudmodel === NULL)
        {
            $submodel_class = $params['data'][0]['_id']; //address

            //create a new company_address model that extends address
            $Submodel = new Model_Model();

            $Submodel->name = Model_Model::get_name_from_class($submodel_class);

            $Submodel->parentfield = array('model'=> 'field', '_id'=> $this->get_class_name()); //link to "company" parent field
            $Submodel->extends = array('model'=> 'model', '_id'=> $submodel_class);//extends address
            
            $submodel_saved = $Submodel->save();

            $this->sudmodel = array('model'=> 'model', '_id'=> $Submodel->_id);
            //@todo is a re-save here a good idea or should this be done before the initial save in a different event function?? since submodel would be set, we'd need to use some sort of cfg flag to trigger the code in this function
            $this->save();
        }

    }*/

    //when a field is created/updated/deleted, we need to re-create/delete the generated class file
    public function event__model_field__save_end($params)
    {

        $create_file = $this->cfg('create_file');

        if ($create_file === NULL || $create_file === TRUE)
        {        
            //get changes
            $changed = $this->changed();

            //if there were any changes
            if (count($changed) > 0)
            {
                $this->write_class_file();
            }
        }
    }
    
    //returns the class name
    public function event__model_field__get_new_pk(&$params)
    {

        $params['pk'] = $this->get_class_name();

    }

    public function get_class_name()
    {
        //if pk is set, than it is the class name
        if (isset($this->_id)) 
        {
            return $this->_id;
        }
        //if there is no model
        if ($this->is_core())
        {
            $model = Supermodlr::get_name_case(self::scfg('core_prefix')).'_';
        }
        //if this field is for a specific model, get the model name from its class name
        else
        {
            $model = Supermodlr::get_name_case($this->model->name).'_'; 
        }
        $field = '';
        //@todo convet below code to use submodels
        //if this is a field on a field (keyed array) example: field_company_address_line1
        /*if ($this->parentfield !== NULL)
        {
            $parentfield = $this->parentfield['_id'];
            $parents = array();
            while ($parentfield !== NULL) 
            {
                $Parent_Field = new model_field($parentfield);
                if ($Parent_Field->loaded()) 
                {
                    //add this parent to the list of parents
                    $parents[] = $Parent_Field->name;

                    //if this parent also has a parent
                    if ($Parent_Field->parentfield !== NULL)
                    {
                        //populate same variable for next time through loop
                        $parentfield = $Parent_Field->parentfield['_id'];
                        unset($Parent_Field);
                    }
                    //this parent does not have a parent. we have found all parents
                    else
                    {
                        $parentfield = NULL;
                    }                   
                }
                //if parent field wasn't loaded for some reason
                else
                {
                    $parentfield = NULL;
                }

            }

            //if we have at least 1 parent
            if (count($parents) > 0)
            {
                $field = implode('_',$parents).'_';     
            }
            //no valid parents found
            else
            {
                $field = '';
            }
        }
        //if this is a root field, and not a field on a field
        else
        {
            $field = '';
        }*/     

        return 'Field_'.$model.Supermodlr::get_name_case($this->name);
    }

    public function write_class_file()
    {
        //re-generate the file content
        $file_contents = $this->generate_class_file_contents();
        
        $full_file_path = $this->get_class_file_path();
        
        //re-save the field file
        $result = $this->save_class_file($full_file_path,$file_contents);   
        return $result;
    }
    
    //if this field has no model, it is a core field
    public function is_core() 
    {
        return (!isset($this->model) || $this->model === NULL || $this->model === '');
    }

    public function get_extends()
    {
        //if we are generating a direct field class
        if ($this->is_core() && (!isset($this->extends) || $this->extends === NULL))
        {
            $extends = 'Field';
        }
        //if we are generating a field class for a specific model, than we are extending an existing core model
        else
        {
            //look for manually set extends
            if (isset($this->extends) && $this->extends instanceOf Supermodler) 
            {
                $extends = $this->extends->pk_value();   
            }
            //extend a core field by the same name
            else if (class_exists('Field_'.self::scfg('core_prefix').'_'.$this->name))
            {
                $extends = 'Field_'.self::scfg('core_prefix').'_'.$this->name;
            }
            else
            {
                $extends = 'Field';
            }
        }
        return $extends;
    }

    // this field model is generating a field class.
    public function generate_class_file_contents()
    {

        $field_class = $this->get_class_name();
        
        $extends = $this->get_extends();
        
        // If this field extends the core Field class, implement datatype and storage interfaces and use the datatype and storage traits
        if ($extends === 'Field')
        {
            $storage = Supermodlr::get_name_case($this->storage);        
            $datatype = Supermodlr::get_name_case($this->datatype);            
            $use = "    use Trait_FieldStorage_{$storage}, Trait_FieldDataType_{$datatype};".PHP_EOL;
            $implements = ' implements Interface_FieldStorage, Interface_FieldDataType';
        }
        else
        {
            $use = '';
            $implements = '';
        }

        $Field = Field::factory();
        $file_contents = <<<EOF
<?php defined('SYSPATH') or die('No direct script access.');
class {$field_class} extends {$extends} {$implements}
{
{$use}
EOF;
        foreach ($Field as $col => $val) 
        {
            if (isset($this->$col))
            {
                $Field_field_class = 'Field_Field_'.Supermodlr::get_name_case($col);
                $Field_field_object = $Field_field_class::factory();
                $file_contents .= "    public \$$col = ".Field::generate_php_value($Field_field_object->export_value($this->$col)).";".PHP_EOL;
            }
        }
        $file_contents .= PHP_EOL."}";


        return $file_contents;
    }
    
    public function get_class_file_path()
    {
        $field_file_name = $this->get_class_name();

        $Framework = $this->get_framework();
        /*if ($this->is_core())
        {
            $Supermodlr_path = $Framework->Supermodlr_root().'classes'.DIRECTORY_SEPARATOR;
        }
        else 
        {
            $Supermodlr_path = $Framework->saved_classes_root();
        }*/
        $Supermodlr_path = $Framework->saved_classes_root();
        
        $field_file_name = str_replace('_',DIRECTORY_SEPARATOR,$field_file_name);
        return $Supermodlr_path.$field_file_name.'.php';
    }   
    
    public function save_class_file($full_file_path, $file_contents)
    {
        $file_info = pathinfo($full_file_path); 

        if (!is_dir($file_info['dirname']))
        {

            $dir_created = mkdir($file_info['dirname'],(int) 0777,TRUE);//@todo fix permissions issues on server level

        }

        $saved = file_put_contents($full_file_path,$file_contents);

        if (!$saved)
        {
            throw new Exception('model_field::save_class_file FAILED ');
        }       
        return $saved;
    }       

    //if a property is accessed, but it isn't set on the model, check for extends and recursively load parents until no parent is found, or until the property is found.
    public function __get($var)
    {
        //if this field extends another field
        if (isset($this->extends) && $this->extends instanceOf Supermodlr)
        {
            //get the parent
            $parent_class = $this->extends->pk_value();
            $has_parent = TRUE;
            //loop until we run out of parents to check
            while ($has_parent)
            {
                //get parent field model from db
                $Parent_Model_Field = new Model_Field($parent_class);
                //if this model has the var, return it
                if (isset($Parent_Model_Field->$var))
                {
                    $has_parent = FALSE;
                    return $Parent_Model_Field->$var;
                }
                //if this parent doesn't have the var, but it also extends a field
                else if (isset($Parent_Model_Field->extends) && $Parent_Model_Field->extends instanceOf Supermodlr)
                {
                    //get the parent
                    $parent_class = $Parent_Model_Field->extends->pk_value();
                    //continue the loop
                    $has_parent = TRUE;
                }
                //no more parents to check
                else
                {
                    $has_parent = FALSE;
                }
            }
        }
    }   

    //returns the name of a field from a class name
    public static function get_name_from_class($class) 
    {
        $parts = explode('_',$class);
        return array_pop($parts);
    }

    //returns the name of a model on a field from a field class name
    public static function get_modelname_from_class($class) 
    {
        $parts = explode('_',$class);
        //if a field has at least field_{$model}_{$field}
        if (count($parts) >= 3) 
        {
            return $parts[1];
        }
        //if this class name only has 2 parts, it doesn't have a model
        else
        {
            return NULL;
        }
    }   
}


class Field_Field__Id extends Field_Supermodlrcore__Id {
    public $name = '_id'; 
    public $required = TRUE;
    public $unique = TRUE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $nullvalue = FALSE; 
    public $templates = array('input'=> 'hidden');      
    public $hidden = TRUE; 
    public $pk = TRUE;
    public $readonly = TRUE;
}

class Field_Field_Label extends Field_Supermodlrcore_Label {
    public $name = 'label'; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $values = NULL;
    public $nullvalue = FALSE; 
    public $validation = array(); //array('alpha_numeric',array('regex',array(':value','/^[a-z][^\s]*$/i'))); @todo fix this for _id field
    public $messages = NULL;
    public $templates = NULL;
    public $hidden = FALSE; 
    public $extends = NULL;
    public $fields = NULL;
    public $invalidtestvalues = NULL; 
    public $readonly = FALSE;
}

class Field_Field_Description extends Field_Supermodlrcore_SingleMultilingualText {
    public $name = 'description'; 
    public $description = 'This text is displayed as help text on data entry forms.';
    public $multilingual = TRUE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $validation = NULL;
    public $templates = NULL;   
    public $hidden = FALSE; 
    public $nullvalue = FALSE; 
}


class Field_Field_Name extends Field_Supermodlrcore_Name {
    public $name = 'name'; 
    public $charset = 'UTF-8'; 
    public $required = TRUE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $values = NULL;
    public $nullvalue = FALSE; 
    public $validation = array(); //array('alpha_numeric',array('regex',array(':value','/^[a-z][^\s]*$/i'))); @todo fix this for _id field
    public $messages = NULL;
    public $templates = NULL;
    public $hidden = FALSE; 
    public $extends = NULL;
    public $fields = NULL;
    public $validtestvalue = 'testfieldname'; 
    public $invalidtestvalues = NULL; 
    public $readonly = TRUE;
}

class Field_Field_Datatype extends Field_Supermodlrcore_SingleText {
    public $name = 'datatype'; 
    public $description = 'Controls how the data is stored.';
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $values = array('string','int','float','timestamp','datetime','boolean','relationship','binary','resource','object','mixed');
    public $filters = array('strtolower');
    public $nullvalue = FALSE; 
}

class Field_Field_Storage extends Field_Supermodlrcore_SingleText {
    public $name = 'storage'; 
    public $description = 'Single means one value per object (per language if multilingual).  Array means multiple values are stored ordered in a numerical index array. keyed_array means multiple values are stored and keyed with numbers or strings';
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $values = array('single','array','keyed_array','false');
    public $filters = array('strtolower');
    public $nullvalue = FALSE; 
}

class Field_Field_Charset extends Field_Supermodlrcore_SingleText {
    public $name = 'charset'; 
    public $description = 'Controls how this string is stored.';
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;     
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $values = array('UTF-8','');
    public $nullvalue = FALSE;  
    public $conditions = array('$hidden'=> TRUE, '$showif'=> array('datatype'=> 'string'));     
}


/*
field model

if datatype == "object", this means that this field stores all the values from another model as an embedded object

Examples:
storage=single
address:{city:'',state:'',zip:''}

storage=array
address:[
    {city:'',state:'',zip:''},
    {city:'',state:'',zip:''}   
]

storage=keyed_array
address:{
    "key1": {city:'',state:'',zip:''},
    "key2": {city:'',state:'',zip:''}   
]



if i store the "submodel" as a direct relationship (ie model_address) to "address"
    - cannot override the "address" submodel for "company"

if i store the "submodel" as a model specific relationship (ie model_company_address), what happens when I extend company?

field_company_address.submodel = model_company_address

bettercompany extends company
    fields
        field_company_address


create address
    fields
        - line1
        - city

create company
    fields
        "add billaddress" add address field opens the field add screen for the company model
        - select object
        - submodel is available
            - i want to tie this to the address model, but when saved, this field will be a model specific field: company_address
            - "use model_address" would add a model_company_address class?
                - if 2 fields in company both were object fields and extended the same model, then this name is not unique enough
            - "use model_address" could create a "model_company_billaddress_address" (model_{$parentmodel}_{parentfield}_{submodel})

class model_company_billaddress_address extends model_address {
    parentfield: field_company_billaddress
    parentmodel: model_company
}




when a model extends another model ( modelB extends modelA)
    - fields is overridden by the child model so we don't inheirit the fields from the parent
        - when 'extends' is first populated, we'd have to pull in all fields from the direct parent and make modelB versions of all these fields
            - this means that changes to field order and add/removes of a field on the parent modelA won't be reflected in modelB
                - a model save will have to recursivly find any models that extend it and remove a field if it is removed on the parent
                - adds? added as the last field on child models?
                - order changed? can be ignored since the child controls the field order




*/

/*class Field_Field_Submodel extends Field {
    public $name = 'submodel'; 
    public $description = 'If datatype = "object", this is a relationship to the model that should be included as a sub/embedded model';
    public $datatype = 'relationship'; 
    public $source = array(array('model'=> 'model','search_field'=> 'name'));
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;     
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE;  
    public $conditions = array('$hidden'=> TRUE, '$showif'=> array('datatype'=> 'object', '_id'=> array('$ne'=> NULL)));        
    public $templates = array('input' => 'field_submodel'); 
    /*
    this template needs to autocomplete on models that have no parentfield and return a link that opens an "add model" window, prepopulating "extends" with the selected model 
    and "parentfield" with the parentfield
        - this means that this field must be saved before "submodel" can be added
            - add a field that only stores the parent model _id relationship and is used to auto-create the submodel relationship if populated on-save end

    */
//}

/*class Field_Field_Submodeladd extends Field {
    public $name = 'submodeladd'; 
    public $description = 'If datatype = "object", this is a relationship to the parent of the model that should be included as a sub/embedded model. This field can be set even if the field is not saved.  It tells the save_end event to create the submodel class based on the details of the saved field.';
    public $datatype = 'relationship'; 
    public $source = array(array('model'=> 'model','search_field'=> 'name','where'=> array('parentfield'=> NULL)));
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;     
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE;  
    public $conditions = array('$hidden'=> TRUE, '$showif'=> array('datatype'=> 'object', 'submodel'=> NULL));      
}*/

class Field_Field_Required extends Field_Supermodlrcore_SingleBoolean {
    public $name = 'required'; 
    public $description = 'The model will not save if this field is not populated and valid.';
    public $required = FALSE;
    public $unique = FALSE;
    public $templates = array('input'=> 'single/boolean');
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE;  
    public $conditions = array('$hideif'=> array('storage'=> 'false'));     
}

class Field_Field_Unique extends Field_Supermodlrcore_SingleBoolean {
    public $name = 'unique'; 
    public $description = 'The model will not save if the value matches an existing entry';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE;
    public $conditions = array('$hideif'=> array('storage'=> 'false'));     
}

class Field_Field_Searchable extends Field_Supermodlrcore_SingleBoolean {
    public $name = 'searchable'; 
    public $description = 'If true, this field will be indexed in the text search provider (solr by default) and used in text queries.';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE;
    public $conditions = array('$hidden'=> TRUE, '$hideif'=> array('storage'=> 'false'), '$showif'=> array('datatype'=> 'string'));             
}

class Field_Field_Filterable extends Field_Supermodlrcore_SingleBoolean {
    public $name = 'filterable'; 
    public $description = 'If true, this field will be indexed in all database providers (mongo and mysql by default).';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE;
    public $conditions = array('$hideif'=> array('storage'=> 'false'));             
}

class Field_Field_Values extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'values'; 
    public $description = 'A list of possible values.  A sent value must exist in this list if it is set.';
    public $datatype = 'mixed'; 
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $storage = 'array';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE; 
}

class Field_Field_Filters extends Field_Supermodlrcore_ArrayText {
    public $name = 'filters'; 
    public $description = 'A list of functions to call to modify the value before it is saved.';
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $validation = array('is_callable');
    public $nullvalue = FALSE;
    public $conditions = array('$hideif'=> array('storage'=> 'false'));          
}

class Field_Field_Defaultvalue extends Field_Supermodlrcore_SingleMixed {
    public $name = 'defaultvalue'; 
    public $description = 'This value is initially displayed on entry forms and/or stored if no value is sent for this field.';
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE; 
}


class Field_Field_Nullvalue extends Field_Supermodlrcore_SingleBoolean {
    public $name = 'nullvalue'; 
    public $description = 'Means null is a valid value for this field.  If set to false and there is no default value set, and no value is sent for this field, it will not be set at all.';
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE;
    public $conditions = array('$hideif'=> array('storage'=> 'false'));          
}


class Field_Field_Validation extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'validation'; 
    public $description = 'A list of validation rules that must pass before this field will be allowed to be saved.';
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $validation = NULL; //@todo figure out how to validate entries for this
    public $messages = NULL; //@todo figure out how to validate entries for this
    public $nullvalue = FALSE; 
}


class Field_Field_Messages extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'messages'; 
    public $description = 'A lit of custom messages for the custom validation rules.';
    public $multilingual = TRUE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE; 
    public $hidden = TRUE;  
}


class Field_Field_Templates extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'templates'; 
    public $description = 'A list of templates that this field should use for display and input forms.';
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $validation = NULL; //@todo write custom function to validate entries for this. entries should be in format: array('input'=> 'input_template', 'display'=> 'display_template')
    public $messages = NULL;
    public $nullvalue = FALSE; 
    public $hidden = TRUE;  
}


class Field_Field_Hidden extends Field_Supermodlrcore_SingleBoolean {
    public $name = 'hidden'; 
    public $description = 'If true, this field will be hidden on all entry forms and will not be displayed on display views.';
    public $required = FALSE;
    public $unique = FALSE;
    public $templates = array('input'=> 'single/boolean');    
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE; 
}

class Field_Field_Extends extends Field_Supermodlrcore_SingleRelationship {
    public $name = 'extends'; 
    public $description = 'A list of fields that this field extends.  The first in the list is the direct parent and any not set options on this field will be inheireted from that parent field.';
    public $source = array(array('model'=> 'field','search_field'=> 'name', 'where'=> array('model'=> NULL)));    
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $templates = array('input'=> 'field_extends');
    public $readonly = TRUE;
}


class Field_Field_Validtestvalue extends Field_Supermodlrcore_SingleMixed {
    public $name = 'validtestvalue'; 
    public $description = 'Enter a valid test value which will be used for automated testing.';
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE; 
}


class Field_Field_Invalidtestvalues extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'invalidtestvalues'; 
    public $description = 'Enter a list of invalid test values which will be used for automated testing.';  
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE; 
    public $hidden = TRUE;  
}

class Field_Field_Model extends Field_Supermodlrcore_SingleRelationship {
    public $name = 'model'; 
    public $description = 'Assigns this field to a specific model or trait.  If not set, this field will be a global field available to assign to any model or trait.';   
    public $source = array(array('model'=> 'model','search_field'=> 'name'), array('model'=> 'trait','search_field'=> 'name'));
    public $multilingual = FALSE; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $defaultvalue = NULL;
    public $nullvalue = TRUE; 
    public $readonly = TRUE;
}

class Field_Field_Access extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'access'; 
    public $description = 'A list of actions and user/group/everyone permissions for each action.'; 
    public $multilingual = FALSE; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE; 
    public $hidden = TRUE;  
}

class Field_Field_Private extends Field_Supermodlrcore_SingleBoolean {
    public $name = 'private'; 
    public $description = 'If true, this field will never be viewable on display views and will not be on entry forms, unless within a admin interface.  Example: password, salt.'; 
    public $multilingual = FALSE; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE; 
}

class Field_Field_Maxlength extends Field_Supermodlrcore_SingleNumber {
    public $name = 'maxlength'; 
    public $description = 'The max length for this field.  For strings, it is the max number of single byte characters (1,max).  For int, it controls the size of the integer field in bytes (1,2,3,4,8).  For float, it controls precision (1 to 18). ';   
    public $multilingual = FALSE; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE;
    public $conditions = array('$hideif'=> array('storage'=> 'false'));          
}

class Field_Field_Conditions extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'conditions'; 
    public $description = 'A list of rules that can control if this field is displayed on views and forms. (Example: only display this field if field1=value1.  The first matched condistion takes precidence'; 
    public $multilingual = FALSE; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE; 
    public $hidden = TRUE;  
}

class Field_Field_Readonly extends Field_Supermodlrcore_SingleBoolean {
    public $name = 'readonly'; 
    public $description = 'If true, this field cannot be changed once it has been set once, except by an admin';    
    public $multilingual = FALSE; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE; 
}

//format array('models'=> array(array('model'=> 'model1', where'=> array([conditions used to select an entry for this model]),'search_field'=> 'name')]) )
class Field_Field_Source extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'source'; 
    public $description = 'If datatype == "relationship", this field describes how to select a valid value.';   
    public $multilingual = FALSE; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $nullvalue = FALSE; 
    public $conditions = array('$hidden'=> TRUE, '$showif'=> array('datatype'=> 'relationship'));           
}



/*

db.getCollection("field").insert({
  "_id": "Field_Supermodlrcore_Singlenumber",
  "datatype": "int",
  "filterable": true,
  "hidden": false,
  "maxlength": 4,
  "multilingual": false,
  "name": "singlenumber",
  "nullvalue": false,
  "private": false,
  "readonly": false,
  "required": false,
  "searchable": false,
  "storage": "single",
  "unique": false,
  "validation": [
    [
      "numeric",
      [
        
      ]
    ]
  ]
});
db.getCollection("field").insert({
  "_id": "Field_Supermodlrcore_Singletext",
  "name": "singletext",
  "datatype": "string",
  "multilingual": false,
  "storage": "single",
  "required": false,
  "unique": false,
  "searchable": true,
  "filterable": true,
  "maxlength": 255,
  "nullvalue": false,
  "hidden": false,
  "private": false,
  "readonly": false
});
db.getCollection("field").insert({
  "_id": "Field_Supermodlrcore_Name",
  "name": "name",
  "datatype": "string",
  "multilingual": true,
  "charset": "UTF-8",
  "storage": "single",
  "required": false,
  "unique": false,
  "searchable": true,
  "filterable": false,
  "maxlength": 255,
  "nullvalue": false,
  "hidden": false,
  "private": false,
  "readonly": false
});
db.getCollection("field").insert({
  "_id": "Field_Supermodlrcore_Description",
  "name": "description",
  "datatype": "string",
  "multilingual": true,
  "charset": "UTF-8",
  "storage": "single",
  "required": false,
  "unique": false,
  "searchable": true,
  "filterable": false,
  "maxlength": 1000,
  "nullvalue": false,
  "hidden": false,
  "private": false,
  "readonly": false
});
db.getCollection("field").insert({
  "_id": "Field_Supermodlrcore__Id",
  "name": "_id",
  "datatype": "string",
  "multilingual": false,
  "charset": "UTF-8",
  "storage": "single",
  "required": true,
  "unique": true,
  "searchable": false,
  "filterable": true,
  "maxlength": 1000,
  "nullvalue": false,
  "hidden": true,
  "private": false,
  "readonly": true
});


*/