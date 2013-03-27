<?php


/*
when a model is created
    * create the _id field for this model
    * write the model class
*/

class Supermodlr_Model_Model extends Supermodlr {
        public static $__scfg = array(
                'name'=> 'model',
                'label'=> 'Model',
                'description' => 'This model defines how other models are stored in the database and how the model class code is generated',
                'field_keys'  => array(
                    '_id',
                    'name',
                    'label',
                    'description',
                    'fields',//this is an array of all field objects included in the saved model.  the field object would only contain key/value pairs for field properties that are changed
                    'extends',//what model file does this model extend.  should have a special auto-completer that searchs on all available models (except self). defaults to 'Supermodlr'
                         //'drivers OR cfg??',
                    'methods',
                    'traits',
                ),
                'core_models' => array('model','field','trait'),
        );

    //when a model is created/updated/deleted, we need to re-create/delete the generated class file
    public function event__model_model__save_end($params)
    {
        $pk_name = $this->cfg('pk_name');
        $pk_name_case = Supermodlr::get_name_case($pk_name);

        $pk_id = 'Field_'.Supermodlr::get_name_case($this->name).'_'.$pk_name_case;
        if (!class_exists($pk_id))
        {
            //write _id field file if it doesn't exist
            $PK_Field = new Model_Field();

            $PK_Field->extends = array("model"=> "field", "_id"=> 'Field_Supermodlrcore_'.$pk_name_case);
            $PK_Field->$pk_name = $pk_id;
            $PK_Field->name = $pk_name;
            $PK_Field->model = array("model"=> "model", "_id"=> $this->get_class_name());
            $PK_Field->save();            
        }

        $create_file = $this->cfg('create_file');

        if ($create_file === NULL || $create_file === TRUE)
        {
            // Get changes
            $changed = $this->changed();
            
            // If there were any changes, re-write the model class file
            if (count($changed) > 0 || $params['is_insert'] === TRUE)
            {
                $this->write_model_class_file();
            }

            // Write the controller class file if it doesn't already exist
            $this->write_controller_class_file();            
        }

    }

    //if this object is deleted, delete the file and all the field files
    public function event__model_model__delete_end($params)
    {
        //if the delete worked
        if ($params['result'] === TRUE)
        {
            //get file path
            $full_file_path = $this->get_class_file_path();
            
            if (file_exists($full_file_path))
            {
                //remove the model file
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
                //check model fields
                //check field fields

            if (!$deleted)
            {
                throw new Exception('model_model::event__model_model__delete_end DELETE FILE FAILED ');
            }           
            
            //@todo catch and handle error
        }
    }
    
    /**
     * 
     */
    public function write_controller_class_file() 
    {
        // Re-generate the file content
        $file_contents = $this->generate_controller_class_file_contents();
        
        $full_file_path = $this->get_controller_class_file_path();

        // If the file already exists, do not overwrite
        if (file_exists($full_file_path))
            return TRUE;
        
        // Re-save the field file
        return $this->save_class_file($full_file_path, $file_contents);  
    }
    
    /**
     * 
     */
    public function write_model_class_file() 
    {
        // Re-generate the file content
        $file_contents = $this->generate_class_file_contents();
        
        $full_file_path = $this->get_model_class_file_path();
        
        // Re-save the field file
        return $this->save_class_file($full_file_path, $file_contents);  
    }
    
    //@todo
    public function event__model_model__name__updated($params)
    {
        //remove old file

        //update all fields to have new name
    }


    /* need to make sure this doesn't remove a field if it was simply moved */
    public function event__model_model__fields_i__removed($params)
    {
        foreach ($params['data'] as $field)
        {
            //create mock field model so we can get the generated class name
            //$Model_Field = $this->create_field_from_data($field);

            //get the real entry from the db
            $Model_Field = new Model_Field($field['_id']);
            $deleted = NULL;
            if ($Model_Field->loaded()) {
                //if field has subfields, remove model fields for each of these recursively 
                //$this->delete_subfields($Model_Field);

                //remove the file
                $deleted = $Model_Field->delete();

            }

            if (!$Model_Field || $deleted->ok() !== TRUE)
            {
                $messages = ($deleted === NULL) ? 'Cannot find' : $deleted->messages();
                throw new Exception('event__model_model__fields_i__removed DELETE FAILED '.$field[$Model_Field->cfg('pk_name')].' '.var_export($messages,TRUE));
            }                       

        }
    }   

    //returns the class name and sets it as the primary key for the model
    public function event__model_model__get_new_pk(&$params)
    {
        $params['pk'] = $this->get_class_name();
    }

    // this model model is generating a model class file.
    public function generate_class_file_contents()
    {
        
        if (isset($this->extends) && $this->extends instanceof Supermodlr) 
        {
            $extends = $this->extends->pk_value();
        }
        else 
        {
            $extends = 'Supermodlr';
        }
        
        $model_class = $this->get_class_name();
        $pk_name = $this->cfg('pk_name');
        $file_contents = <<<EOF
<?php defined('SYSPATH') or die('No direct script access.');
/**
  * FileDescription: {$this->description}
  */
class {$model_class} extends {$extends} {

EOF;

        //set all traits as 'use' statements
        if (isset($this->traits) && is_array($this->traits)) 
        {
                foreach ($this->traits as $trait)
                {
                    $file_contents .= "    use ".$trait->get_pk_value().";".PHP_EOL;
                }  

        }
        $name = Field::generate_php_value($this->name);
        $label = Field::generate_php_value($this->label);
        $desc = Field::generate_php_value($this->description);
        $file_contents .= <<<EOF
    public static \$__scfg = array (
            'name' => {$name},
            'label' => {$label},
            'description' => {$desc},
            'field_keys' => array (
                '$pk_name',

EOF;

        //store all fields in model scfg
        if (isset($this->fields) && is_array($this->fields)) 
        {
            foreach ($this->fields as $field)
            {
                $field_class = $field->pk_value();
                $field_obj = $field_class::factory();
                $file_contents .= "                 '".$field_obj->name."',".PHP_EOL;
            }       
        }

        $file_contents .= "        ),".PHP_EOL;
        $file_contents .= "    );".PHP_EOL;

        //set all default values for each field on the model
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
    

    public function get_model_class_file_path()
    {
        $model_file_name = $this->get_class_name();
        
        //do not overwrite any core files
        if (in_array($model_file_name,$this->cfg('core_models')))
        {
            return FALSE;
        }

        $Framework = $this->get_framework();
        $Supermodlr_path = $Framework->saved_classes_root();
        //replace all underbars with / to build file path
        $model_file_name = str_replace('_',DIRECTORY_SEPARATOR, $model_file_name);
        return $Supermodlr_path.$model_file_name.EXT;
    }

    /**
     * 
     */
    public function generate_controller_class_file_contents()
    {

        $controller_class_name = $this->get_controller_class_name();

        // If this model extends another, get that model name
        $extends = (isset($this->extends) && $this->extends instanceof Supermodlr) ? preg_replace('/^Model_/', 'Controller_', $this->extends->pk_value()) : "Controller_Scaffold";

        $file_contents = <<<EOF
<?php defined('SYSPATH') or die('No direct script access.');
/**
 * {$controller_class_name}
 *
 * @uses     {$extends}
 */
class {$controller_class_name} extends {$extends} {

EOF;

        $file_contents .= "}".PHP_EOL;

        return $file_contents;
    }
    
    /**
     * 
     */
    public function get_controller_class_file_path()
    {
        $controller_class_name = $this->get_controller_class_name();
        
        // @todo do not overwrite any core files
        /*if (in_array($controller_class_name, $this->cfg('core_models')))
        {
            return FALSE;
        }*/

        $framework = $this->get_framework();
        $supermodlr_path = $framework->saved_classes_root();
        //replace all underbars with / to build file path
        $controller_class_name = str_replace('_',DIRECTORY_SEPARATOR, $controller_class_name);
        return $supermodlr_path.$controller_class_name.EXT;
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

    /**
     * 
     */
    public function get_controller_class_name()
    {
        return 'Controller_'.Supermodlr::get_name_case($this->name);
    }

    //generate and return the class name to be used for this model
    public function get_class_name()
    {
        //if a parent field is set, include the parent field name and the parent model name in the class name
        if (isset($this->parentfield) && is_array($this->parentfield))
        {
            $parentfield = '';//Supermodlr::get_name_case(Model_Field::get_name_from_class($this->parentfield['_id'])).'_';
            $parentmodel = '';//Supermodlr::get_name_case(Model_Field::get_modelname_from_class($this->parentfield['_id'])).'_';
        }
        else
        {
            $parentmodel = '';
            $parentfield = '';
        }       
        return 'Model_'.$parentmodel.$parentfield.Supermodlr::get_name_case($this->name);
    }

}


class Field_Model__Id extends Field_Supermodlrcore__Id {
    public $name = '_id';
    public $label = 'Id';
    public $datatype = 'string'; 
    public $multilingual = FALSE; 
    public $storage = 'single';
    public $required = TRUE;
    public $unique = TRUE;
    public $searchable = TRUE;
    public $filterable = TRUE;
    public $nullvalue = FALSE; 
    public $templates = array('input'=> 'hidden');      
    public $hidden = TRUE; 
    public $pk = TRUE;  
    public $model = array('model' => 'model', '_id'=> 'Model_Model');
}
class Field_Model_Name extends Field_Supermodlrcore_Name {
    public $name = 'name';
    public $label = 'Name';
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
    public $model = array('model' => 'model', '_id'=> 'Model_Model');
}

class Field_Model_Label extends Field_Supermodlrcore_Label {
    public $name = 'label';
    public $label = 'Label';
    public $multilingual = TRUE; 
    public $charset = 'UTF-8'; 
    public $required = FALSE;
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $validation = NULL;
    public $templates = NULL;   
    public $hidden = FALSE; 
    public $model = array('model' => 'model', '_id'=> 'Model_Model');
}



class Field_Model_Description extends Field_Supermodlrcore_Description {
    public $name = 'description';
    public $label = 'Description';
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
    public $model = array('model' => 'model', '_id'=> 'Model_Model');
}

class Field_Model_Fields extends Field_Supermodlrcore_ArrayRelationship {
    public $name = 'fields';
    public $label = 'Fields';
    public $datatype = 'relationship'; 
    public $source = array(array('model'=> 'field','search_field'=> 'name', 'where'=> array('model'=> NULL)));
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $storage = 'array';
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $defaultvalue = NULL;
    public $nullvalue = FALSE;
    public $validation = NULL;
    public $templates = array('input' => 'model_fields');   
    public $hidden = FALSE; 
    public $filters = array('model_model::filter_fields');
    public $model = array('model' => 'model', '_id'=> 'Model_Model');
}

class Field_Model_Methods extends Field_Supermodlrcore_ArrayMixed {
    public $name = 'methods';
    public $label = 'Methods';
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $unique = FALSE;
    public $searchable = TRUE;
    public $filterable = FALSE;
    public $defaultvalue = NULL;
    public $nullvalue = FALSE;
    public $validation = NULL;
    public $hidden = TRUE; 
    public $model = array('model' => 'model', '_id'=> 'Model_Model');
}


class Field_Model_Extends extends Field_Supermodlrcore_SingleRelationship {
    public $name = 'extends';
    public $label = 'Extends';
    public $datatype = 'relationship';
    public $source = array(array('model'=> 'model','search_field'=> 'name'));
    public $multilingual = FALSE;
//    public $charset = 'UTF-8';
    public $storage = 'single';
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $defaultvalue = NULL;
    public $nullvalue = TRUE;
    public $validation = NULL;
    public $templates = array('input' => 'model_extends');
    public $hidden = FALSE;
    public $model = array('model' => 'model', '_id'=> 'Model_Model');
}


class Field_Model_Parentfield extends Field_Supermodlrcore_SingleRelationship {
    public $name = 'parentfield';
    public $label = 'Parent Field';
    public $description = 'If this model is assigned to a model via an "object" field and this is the "model" specific copy, this field stores the relationship to that field.';
    public $datatype = 'relationship'; 
    public $source = array(array('model'=> 'field','search_field'=> 'name'));
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $storage = 'single';
    public $required = FALSE;
    public $unique = FALSE;     
    public $searchable = FALSE;
    public $filterable = TRUE;
    public $nullvalue = FALSE;  
    public $hidden = TRUE;
    public $model = array('model' => 'model', '_id'=> 'Model_Model');
    //public $conditions = array('$hidden'=> TRUE, '$showif'=> array('datatype'=> 'object'));       
}

class Field_Model_Traits extends Field_Supermodlrcore_ArrayRelationship {
    public $name = 'traits';
    public $label = 'Traits';
    public $datatype = 'relationship'; 
    public $source = array(array('model'=> 'trait','search_field'=> 'name'));
    public $multilingual = FALSE; 
    public $charset = 'UTF-8'; 
    public $storage = 'array';
    public $unique = FALSE;
    public $searchable = FALSE;
    public $filterable = FALSE;
    public $defaultvalue = NULL;
    public $nullvalue = FALSE;
    public $validation = NULL;
    public $hidden = FALSE; 
    public $model = array('model' => 'model', '_id'=> 'Model_Model');
}



/*
if a model is a submodel for a different model, it must be attached via a model specific field
    - i don't NEED to have parentmodel if I have parentfield which will tell me what model

*/