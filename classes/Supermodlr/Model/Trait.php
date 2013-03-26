<?php


/*
when a trait is created
    * create the _id field for this trait
    * write the trait class
*/

class Supermodlr_Model_Trait extends Supermodlr {
        public static $__scfg = array(
                'name'=> 'trait',
                'label'=> 'Trait',
                'description' => 'This model defines how traits are stored in the database and how the trait class code is generated',            
                'field_keys'  => array(
                    '_id',
                    'name',
                    'label',
                    'description',
                    'fields',//this is an array of all field objects included in the saved trait.  the field object would only contain key/value pairs for field properties that are changed
                         //'drivers OR cfg??',
                    'methods',
                    'traits',
                ),
            'core_models' => array('model','field','trait'), 
        );

    //when a trait is created/updated/deleted, we need to re-create/delete the generated class file
    public function event__model_trait__save_end($params)
    {
        $create_file = $this->cfg('create_file');

        if ($create_file === NULL || $create_file === TRUE)
        {        
            // Get changes
            $changed = $this->changed();
            
            // If there were any changes, re-write the trait class file
            if (count($changed) > 0 || $params['is_insert'] === TRUE)
            {
                $this->write_trait_class_file();
            }
        }

    }

    //if this object is deleted, delete the file and all the field files
    public function event__model_trait__delete_end($params)
    {
        //if the delete worked
        if ($params['result'] === TRUE)
        {
            //get file path
            $full_file_path = $this->get_class_file_path();
            
            if (file_exists($full_file_path))
            {
                //remove the trait file
                $deleted = unlink($full_file_path); 
            } 
            else 
            {
                $deleted = TRUE;
            }

            //remove pk field
            $pk_name = $this->cfg('pk_name');       
            $pk_name_case = Supermodlr::get_name_case($pk_name);
            $PK_Field = new Model_Field('Field_'.Supermodlr::get_name_case($this->name).'_'.$pk_name_case);
            $PK_Field->delete();

            //@todo remove all references to this field from the db
                //check extends
                //check trait fields
                //check field fields

            if (!$deleted)
            {
                throw new Exception('model_trait::event__model_trait__delete_end DELETE FILE FAILED ');
            }           
            
            //@todo catch and handle error
        }
    }
    
    /**
     * 
     */
    public function write_trait_class_file() 
    {
        // Re-generate the file content
        $file_contents = $this->generate_class_file_contents();
        
        $full_file_path = $this->get_trait_class_file_path();
        
        // Re-save the field file
        return $this->save_class_file($full_file_path, $file_contents);  
    }
    

    //@todo
    public function event__model_trait__name__updated($params)
    {
        //remove old file

        //update all fields to have new name
    }


    /* need to make sure this doesn't remove a field if it was simply moved */
    public function event__model_trait__fields_i__removed($params)
    {
        foreach ($params['data'] as $field)
        {
            //create mock field model so we can get the generated class name
            //$Model_Field = $this->create_field_from_data($field);

            //get the real entry from the db
            $Trait_Field = new Model_Field($field['_id']);
            $deleted = NULL;
            if ($Trait_Field->loaded()) {
                //if field has subfields, remove model fields for each of these recursively 
                //$this->delete_subfields($Model_Field);

                //remove the file
                $deleted = $Trait_Field->delete();

            }

            if (!$Trait_Field || $deleted->ok() !== TRUE)
            {
                $messages = ($deleted === NULL) ? 'Cannot find' : $deleted->messages();
                throw new Exception('event__model_trait__fields_i__removed DELETE FAILED '.$field[$Trait_Field->cfg('pk_name')].' '.var_export($messages,TRUE));
            }                       

        }
    }   

    //returns the class name and sets it as the primary key for the trait
    public function event__model_trait__get_new_pk(&$params)
    {
        $params['pk'] = $this->get_class_name();
    }

    // this model trait is generating a trait class file.
    public function generate_class_file_contents()
    {
                
        $trait_class = $this->get_class_name();
        $name = $this->name;
        $pk_name = $this->cfg('pk_name');
        $file_contents = <<<EOF
<?php defined('SYSPATH') or die('No direct script access.');
/**
  * FileDescription: {$this->description}
  */
trait {$trait_class} {

EOF;
        //set all traits as 'use' statements
        if (isset($this->traits) && is_array($this->traits)) 
        {
                foreach ($this->traits as $trait)
                {
                    $file_contents .= "    use ".$trait->pk_value().";".PHP_EOL;
                }  

        }
        $name = Field::generate_php_value($this->name);
        $label = Field::generate_php_value($this->label);
        $desc = Field::generate_php_value($this->description);
        $file_contents .= <<<EOF
    public static \$__{$name}__scfg = array(
            'traits__{$name}__name'=> {$name},
            'traits__{$name}__label'=> {$label},
            'traits__{$name}__description' => {$desc},            
            'field_keys' => array(

EOF;

        //store all fields in trait scfg
        if (isset($this->fields) && is_array($this->fields)) 
        {
            foreach ($this->fields as $field)
            {
                $field_class = $field->pk_value();
                $field_obj = $field_class::factory();
                $file_contents .= "                 '".$field_obj->name."',".PHP_EOL;
            }       
        }

        $file_contents .= "                )".PHP_EOL;
        $file_contents .= " );".PHP_EOL;

        //set all default values for each field on the trait
        if (isset($this->fields) && is_array($this->fields)) 
        {
            foreach ($this->fields as $field)
            {
                $field_class = $field->pk_value();
                $field_obj = $field_class::factory();
                //if the default value should not be set to null and defaultvalue is null
                if ($field_obj->defaultvalue() === NULL && $field_obj->nullvalue === FALSE)
                {
                    //skip defining this field so it has no default value
                    continue;
                }
                else
                {
                    $file_contents .= "   public \$".$field_obj->name." = ".Field::generate_php_value($field_obj->export_value($field_obj->defaultvalue())).";".PHP_EOL;
                }
                
            }

        }

        //loop through all stored methods
        if (isset($this->methods) && is_array($this->methods))
        {
            foreach ($this->methods as $method)
            {
                $file_contents .= PHP_EOL."    ".$method['comment'];
                $file_contents .= $method['source'].PHP_EOL;

            }

        }

        $file_contents .= "}".PHP_EOL;

        return $file_contents;
    }
    

    public function get_trait_class_file_path()
    {
        $trait_file_name = $this->get_class_name();
        
        //do not overwrite any core files
        if (in_array($trait_file_name,$this->cfg('core_models')))
        {
            return FALSE;
        }

        $Framework = $this->get_framework();
        $Supermodlr_path = $Framework->saved_classes_root();
        //replace all underbars with / to build file path
        $trait_file_name = str_replace('_',DIRECTORY_SEPARATOR, $trait_file_name);
        return $Supermodlr_path.$trait_file_name.EXT;
    }

    /**
     * 
     */
    public function save_class_file($full_file_path, $file_contents)
    {
        $file_info = pathinfo($full_file_path);
        if (!is_dir($file_info['dirname']))
        {
            $dir_created = mkdir($file_info['dirname'],(int) 0777,TRUE);//@todo fix server issues at server level
        }   
        $saved = file_put_contents($full_file_path,$file_contents);
        return $saved;
    }

    //generate and return the class name to be used for this trait
    public function get_class_name()
    {      
        return 'Trait_'.Supermodlr::get_name_case($this->name);
    }

}


class Field_Trait__Id extends Field_Supermodlrcore__Id {
    public $name = '_id'; 
    public $multilingual = FALSE; 
    public $required = TRUE;
    public $unique = TRUE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $nullvalue = FALSE; 
    public $templates = array('input'=> 'hidden');      
    public $hidden = TRUE; 
    public $pk = TRUE;  
}
class Field_Trait_Name extends Field_Supermodlrcore_Name {
    public $name = 'name'; 
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $required = TRUE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $values = NULL;
    public $defaultvalue = NULL;
    public $nullvalue = FALSE; 
    public $validation = NULL;
    public $messages = NULL;
    public $templates = NULL;
    public $hidden = FALSE; 
    public $extends = NULL;
    public $fields = NULL;
    public $validtestvalue = 'test field name'; 
    public $invalidtestvalues = NULL; 
}

class Field_Trait_Label extends Field_Supermodlrcore_Label {
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


class Field_Trait_Description extends Field_Supermodlrcore_Description {
    public $name = 'description'; 
    public $multilingual = TRUE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $defaultvalue = NULL;
    public $validation = NULL;
    public $templates = NULL;   
    public $hidden = FALSE; 
}

class Field_Trait_Fields extends Field_Supermodlrcore_ArrayRelationship {
    public $name = 'fields'; 
    public $source = array(array('model'=> 'field','search_field'=> 'name', 'where'=> array('model'=> NULL)));
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $defaultvalue = NULL;
    public $nullvalue = FALSE;
    public $validation = NULL;
    public $templates = array('input' => 'trait_fields');   
    public $hidden = FALSE; 
}

class Field_Trait_Methods extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'methods'; 
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $defaultvalue = NULL;
    public $nullvalue = FALSE;
    public $validation = NULL;
    public $hidden = TRUE; 
}

class Field_Trait_Traits extends Field_Supermodlrcore_ArrayRelationship {
    public $name = 'traits'; 
    public $source = array(array('model'=> 'trait','search_field'=> 'name'));
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $defaultvalue = NULL;
    public $nullvalue = FALSE;
    public $validation = NULL;
    public $hidden = FALSE; 
}
