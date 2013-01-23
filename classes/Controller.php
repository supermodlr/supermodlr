<?php defined('SYSPATH') or die('No direct script access.');

class Controller extends Kohana_Controller {

    public $model_name = NULL;
    public $model_class = NULL;

    public function init_req_model()
    {
        //get model name from url
        $this->model_name = $this->request->param('model');

        //if model name was in url
        if ($this->model_name !== NULL)
        {

            $this->model_name = ucfirst(strtolower($this->model_name));

            //check if model exists
            if (!$this->model_class = Supermodlr::model_exists($this->model_name))
            {
                //404 if model doesn't exist
                throw new HTTP_Exception_404('The requested URL :uri was not found on this server.',
                                                        array(':uri' => $this->request->uri()));
            }
        }
    }

    public function model_list($Result_Set, $model_name, $type, $action, $theme = 'default', $template = 'default', $media = 'web', $ilang = 'en', $field_keys = NULL)
    {

        $entity_templates = array();

        foreach ($Result_Set as $Model)
        {
            $entity_templates[] = $this->model_view($Model, $type, $action, $theme, $template, $media, $ilang, $field_keys)->render();
        }

        //get model wrapper template paths
        $list_wrapper_template_paths = $this->get_list_template_paths($Model,$type,'wrapper',$template);
        
        //get template for model
        $list_Wrapper_View = $this->get_template($model_wrapper_template_paths, $theme, $media);

        //bind rendered fields
        $List_Wrapper_View->bind('entity_templates',$entity_templates);     
        
        //bind the model name
        $List_Wrapper_View->bind('model_name',$model_name); 

        //bind type, theme, template, media, ilang
        $List_Wrapper_View->bind('type',$type);     
        $List_Wrapper_View->bind('action',$action);     
        $List_Wrapper_View->bind('theme',$theme);   
        $List_Wrapper_View->bind('template',$template); 
        $List_Wrapper_View->bind('media',$media);           
        $List_Wrapper_View->bind('ilang',$ilang);       
        
        //bind the controller
        $List_Wrapper_View->bind('controller',$this);               
    
        //return view
        return $List_Wrapper_View;

    }


    /**
     * returns input or display html
     * @param string $Model required Instance of Supermodlr_Core   
     * @param string $type required (input|display)     
     * @param string $action required (create|read|update|delete)     
     * @param string $theme = 'default' theme template folder to look for the form template and field templates 
     * @param string $template = 'default' template folder to look for the form template and field templates. path looks like $theme/$media/$template
     * @param string $media = 'web' media name used to look up this template.  possible values are currently web, tablet, or moble
     * @param string $ilang = 'en' language used to generate interface
     * @param array $field_keys = all.  An array of field names to be used
     * @return string $form html returns the html used to display the entry/update form for this model based on the sent template and media
     */ 
    public function model_view($Model, $type, $action, $theme = 'default', $template = 'default', $media = 'web', $ilang = 'en', $field_keys = NULL) 
    {   
        $model_name = $Model->get_name();
        //assign a random id to this form generation (must start with a character though, so 'a' is added)
        $form_id = 'a'.uniqid().'_'.$model_name;

        //get fields
        $fields = $Model->get_fields();
        
        //if a custom list of fields to use was sent
        if ($field_keys !== NULL && is_array($field_keys))
        {
            //loop through all fields
            foreach ($fields as $i => $Field)
            {
                //remove all fields not in sent list
                if (!in_array($Field->name, $field_keys)) 
                {
                    unset($fields[$i]);
                }
            }

        }

        $field_templates = array();
        
        $model_data = $Model->to_array();

        //loop through all fields
        foreach ($fields as $Field)
        {
            //do not put pk in template ?? @todo think about this
            if ($Field->name == $Model->cfg('pk_name')) {
                continue;
            }

            //only users with admin access can see private fields. @todo move this logic to core model
            if ($Field->private === TRUE && !in_array('admin',$Model->get_user_access_tags()))
            {
                continue;
            }
        
            $field_key = $Field->name;

            //if a custom label is set for this lang @todo maybe move this to a field method
            if (isset($Field->label[$ilang]))
            {
                $Field->label = $Field->label[$ilang];
            }
            //else uccase the name
            else if (!isset($Field->label))
            {
                $Field->label = ucfirst($Field->name);
            }


            //if value is set on the current object
            if (isset($Model->$field_key))
            {
                $Field = $this->field_set_value($model_data,$Field);
            }
            
            //get field objects for all submodel.fields
            if ($Field->datatype == 'object' && isset($Field->submodel) && is_array($Field->submodel) && isset($Field->submodel['_id']) && class_exists($Field->submodel['_id'])) 
            {
                $Field->sub_fields = array();
                //get related model
                $submodel = new $Field->submodel['_id']();

                //get all submodel fields
                $submodel_fields = $submodel->get_fields();

                //loop through that model's field
                foreach ($submodel_fields as $sub_field_key => $sub_field) 
                {

                    //skip the pk field
                    if ($sub_field->name == $submodel->cfg('pk_name'))
                    {
                        continue;
                    }

                    //if a custom label is set for this lang @todo maybe move this to a field method
                    if (isset($sub_field->label[$ilang]))
                    {
                        $sub_field->label = $sub_field->label[$ilang];
                    }
                    //else uccase the name
                    else 
                    {
                        $sub_field->label = ucfirst($sub_field->name);
                    }
                    //if this field is set on the model
                    if (isset($model_data[$field_key]))
                    {                   
                        //set a pointer to the field object
                        $pointer = &$model_data[$field_key];

                        //check to see if this submodel field is set on the parent model
                        if (isset($pointer[$sub_field->name])) 
                        {
                            //set the value on the submodel field object to be sent to templates
                            $sub_field = $this->field_set_value($model_data,$sub_field,$pointer[$sub_field->name]);
                        }
                    }
                    

                    $Field->sub_fields[$sub_field->name] = $sub_field;
                }                   

            }

            //store rendered template for form template
            $field_templates[$field_key] = $Field;
        }
        
        //get model wrapper template paths
        $model_wrapper_template_paths = $this->get_model_template_paths($Model,$type,'wrapper',$template);
        
        //get template for model
        $Model_Wrapper_View = $this->get_template($model_wrapper_template_paths, $theme, $media);

        //bind rendered fields
        //$Framework->bind($Model_View,'fields',$field_templates);
        $Model_Wrapper_View->bind('fields',$field_templates);       
        
        //bind the model
        //$Framework->bind($Model_View,'model',$this);      
        $Model_Wrapper_View->bind('model',$Model);          
        $Model_Wrapper_View->bind('model_name',$model_name);    

        $Model_Wrapper_View->bind('form_id',$form_id);          

        //bind type, theme, template, media, ilang
        $Model_Wrapper_View->bind('type',$type);        
        $Model_Wrapper_View->bind('action',$action);        
        $Model_Wrapper_View->bind('theme',$theme);  
        $Model_Wrapper_View->bind('template',$template);    
        $Model_Wrapper_View->bind('media',$media);          
        $Model_Wrapper_View->bind('ilang',$ilang);      
        
        //bind the controller
        $Model_Wrapper_View->bind('controller',$this);              
    

        //return model view
        return $Model_Wrapper_View;
    }

    public function field_set_value($data,$Field, &$pointer = NULL) 
    {

        $field_key = $Field->name;

        if ($pointer === NULL)
        {
            $pointer = &$data[$field_key];
        }

        if ($Field->storage !== 'single' || $Field->datatype === 'object' || $Field->datatype == 'relationship')
        {
            $value = HTML::chars(json_encode($pointer));
            $raw_value = json_encode($pointer);
        }
        else
        {
            $value = HTML::chars($pointer);
            $raw_value = $pointer;
        }
    
        //bind value to view
        $Field->value = $value;
        $Field->raw_value = $raw_value;     

        //if this field is a relationship, we need to get all the labels for and existing values
        if ($Field->datatype == 'relationship') 
        {
            $labels = array();
            $items = array();
            if ($Field->storage == 'single')
            {
                $items[] = $pointer;
            }
            else
            {
                if (is_array($pointer)) 
                {
                    $items = $pointer;
                }                       
            }

            foreach ($items as $item) 
            {
                $rel_class = 'Model_'.Supermodlr::get_name_case($item['model']);
                $rel = new $rel_class($item['_id']);
                $label_field_key = 'name';
                foreach ($Field->source as $source) {
                    if ($source['model'] == $item['model']) {
                        $label_field_key = $source['search_field'];
                        break;
                    }
                }
                if (isset($rel->$label_field_key))
                {
                    $labels[$item['model'].$item['_id']] = $rel->$label_field_key;
                }
                else
                {
                    $labels[$item['model'].$item['_id']] = $rel_class;
                }
                
            }
            $Field->source['labels'] = json_encode($labels);                    
        }
        return $Field;
    }

    /**
     * returns form html
     * @param array $template_paths array of paths to use to look for a template file
     * @param string $theme = 'default' theme template folder to look for the template files
     * @param string $media = 'web' media name used to look up this template.  possible values are currently web, tablet, or moble
     * @return View $View returns the unrendered view class returned by the framework
     */     
    public function get_template(array $template_paths,$theme,$media) {

        //get field template for the sent theme and media
        $View = View::factory()->get_view($template_paths,$theme, $media);

        return $View;
    }


    /**
     * returns array of paths used to search for model templates
     * @param string $model_name required name of the model of which this list will consist   
     * @param string $template (input|display) the type of template we are looking for     
     * @param string $type (input|display) the type of template we are looking for
     * @param string $sub_type (''|wrapper) an additional level to look under for template files.
     * @return array $list_template_paths returns an array of paths to search in for a list template
     */ 
    public function get_list_template_paths($model_name,$type,$sub_type = '', $template = 'default') 
    {
        if ($sub_type !== '')
        {
            $sub_type = '/'.$sub_type;
        }

        $list_template_paths = array();
        
        //if sent template is not default
        if ($template !== 'default') 
        {
            //look first for template by sent name
            $list_template_paths[] = $type.$sub_type.'/list/'.$model_name.'/'.$template;
            $list_template_paths[] = $type.$sub_type.'/list/'.$template;            
        }

    
        //add in field name key
        $list_template_paths[] = $type.$sub_type.'/list/'.$model_name.'/default';
        $list_template_paths[] = $type.$sub_type.'/list/'.$model_name;
        
        //add in default template
        $list_template_paths[] = $type.$sub_type.'/list/default';

        return $list_template_paths;
    }

    /**
     * returns array of paths used to search for model templates
     * @param Object $Model required Instance of Supermodlr_Core       
     * @param string $template (input|display) the type of template we are looking for     
     * @param string $type (input|display) the type of template we are looking for
     * @param string $sub_type (''|wrapper) an additional level to look under for template files.
     * @return array $model_template_paths returns an array of paths to search in for a model template
     */ 
    public function get_model_template_paths($Model,$type,$sub_type = '', $template = 'default') 
    {
        if ($sub_type === 'model')
        {
            $sub_type = '';
        }
        if ($sub_type !== '')
        {
            $sub_type = '/'.$sub_type;
        }

        $model_name = $Model->get_name();       
        $model_template_paths = array();

        $model_templates = $Model->cfg('templates');
        
        //if sent template is not default
        if ($template !== 'default') 
        {
            //look first for template by sent name
            $model_template_paths[] = $type.$sub_type.'/model/'.$model_name.'/'.$template;
            $model_template_paths[] = $type.$sub_type.'/model/'.$template;          
        }

        //add in stored template if set
        if ($model_templates !== NULL && is_array($model_templates) && isset($model_templates[$type]) && $model_templates[$type] != 'default')
        {
            $model_template_paths[] = $type.$sub_type.'/model/'.$model_name.'/'.$model_templates[$type];
            $model_template_paths[] = $type.$sub_type.'/model/'.$model_templates[$type];
        }
        
        //add in field name key
        $model_template_paths[] = $type.$sub_type.'/model/'.$model_name.'/default';
        $model_template_paths[] = $type.$sub_type.'/model/'.$model_name;
        
        //add in default template
        $model_template_paths[] = $type.$sub_type.'/model/default';

        return $model_template_paths;
    }


    /**
     * returns array of template paths to search for field templates
     * @param Object $Field required Instance of Field          
     * @param string $template (input|display) the type of template we are looking for     
     * @param string $type (input|display) the type of template we are looking for
     * @param string $sub_type (''|wrapper) an additional level to look under for template files.
     * @return array $field_template_paths returns an array of paths to search in for a field template
     */ 
    public function get_field_template_paths($Field,$type,$sub_type = '',$template = 'default') 
    {
        if ($sub_type === 'field') 
        {
            $sub_type = ''; 
        }
        if ($sub_type !== '')
        {
            $sub_type = '/'.$sub_type;
        }

        if (isset($Field->model) && is_array($Field->model))
        {
            $model_name = model_model::get_name_from_class($Field->model['_id']);       
        }
        else
        {
            $model_name = model_field::scfg('core_prefix');
        }
        
        $field_template_paths = array();
        
        //if sent template is not default
        if ($template !== 'default') 
        {
            //look first for template by sent name
            $field_template_paths[] = $type.$sub_type.'/field/model/'.$model_name.'/'.$template;
            $field_template_paths[] = $type.$sub_type.'/field/'.$template;          
        }

        //add in stored template
        if ($Field->templates !== NULL && is_array($Field->templates) && isset($Field->templates[$type]) && $Field->templates[$type] != 'default')
        {
            $field_template_paths[] = $type.$sub_type.'/field/model/'.$model_name.'/'.$Field->templates[$type];
            $field_template_paths[] = $type.$sub_type.'/field/'.$Field->templates[$type];
        }

        //add in field name key
        $field_template_paths[] = $type.$sub_type.'/field/model/'.$model_name.'/'.$Field->name;
        $field_template_paths[] = $type.$sub_type.'/field/'.$Field->name;

        //add in field storage/type template
        $field_template_paths[] = $type.$sub_type.'/field/model/'.$model_name.'/'.$Field->storage.'/'.$Field->datatype;
        $field_template_paths[] = $type.$sub_type.'/field/model/'.$model_name.'/'.$Field->storage.'/default';
        $field_template_paths[] = $type.$sub_type.'/field/'.$Field->storage.'/'.$Field->datatype;
        $field_template_paths[] = $type.$sub_type.'/field/'.$Field->storage.'/default';     
        
        //add in default template
        $field_template_paths[] = $type.$sub_type.'/field/model/'.$model_name.'/default';
        $field_template_paths[] = $type.$sub_type.'/field/default';

        return $field_template_paths;
    }

    public static function get_rel_labels($field_id_array) {
        $label_array = array();
        foreach ($field_id_array as $_id) {
            $Field = new Model_Field($_id);
            if ($Field->loaded()) {
                $label_array[$_id] = $Field->name; //@todo change this to label once field labels are added             
            }
        }
        return $label_array; 

    }   

    /**
     *  ads js code to the template
     */
    public function js($js = NULL, $position = NULL, $weight = 10, $defer = FALSE) 
    {
        if ($position == 'readyinline') {
           $js = '$(document).ready(function(){'.$js.'});';
        } else if ($position == 'loadinline') {
           $js = '$(window).load(function(){'.$js.'});';
        }

        //if this is inline js (not a url for a script tag)
        if (strpos($position, 'inline') !== FALSE) {
            echo '
<script type="text/javascript">
//<![CDATA[
'.$js.'
//]]>
</script>
';          
        } else {
            echo '';
        }
    }   

    public function css() 
    {
        echo '';
    }

    /**
     *  returns string api path
     */ 
    public function api_path()
    {
        return '/supermodlr/api/';
    }   
}