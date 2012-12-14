<?php defined('SYSPATH') or die('No direct script access.');

class Controller_supermodlr_Api extends Controller {

	public function before()
	{
		parent::before();
		$this->init_req_model();
	}

	public function action_index()
	{

	}
	
	public function action_create()
	{
		//get model name and class
		$model_name = $this->model_name;
		$model_class = $this->model_class;
		
		//create new model
		$Model = new $model_class();
		
		//get data
		$data = $this->get_data();
		
		if ($data === FALSE)
		{
			throw new HTTP_Exception_400('The data was not posted or formated properly.');			
		}

		//load posted data
		$Model->load($data);

		//save changes		
		$Saved_status = $Model->save();
		
		//set content type header
		$this->response->headers('content-type','application/json');
		
		//set bad status if request failed
		if (!$Saved_status->ok()) 
		{
			$this->response->status(400);
		}
		else
		{
			$Saved_status->data(NULL,$Model->to_array());
		}
		
		

		//return result		
		$this->response->body($Saved_status->to_json());
	}
	
	public function action_read()
	{
		//get model name and class
		$model_name = $this->model_name;
		$model_class = $this->model_class;
		
		//get id from the url
		$id = $this->request->param('id');
		
		//load model by id
		$Model = new $model_class($id);
		
		//ensure model loaded
		if ($Model->loaded() === FALSE)
		{
			//404 if model by this id doesn't exist
			throw new HTTP_Exception_404('The requested :model with id :id was not found.',
													array(':model' => $model_name, ':id'=> $id));
		}
		
		//set content type header
		$this->response->headers('content-type','application/json');		
		
		//return result		
		$this->response->body(json_encode($Model->to_array()));
	}	
	
	public function action_update()
	{
		//get model name and class
		$model_name = $this->model_name;
		$model_class = $this->model_class;
		
		//get id from the url
		$id = $this->request->param('id');
		
		//load model by id
		$Model = new $model_class($id);
		
		//ensure model loaded
		if ($Model->loaded() === FALSE)
		{
			//404 if model by this id doesn't exist
			throw new HTTP_Exception_404('The requested :model with id :id was not found.',
													array(':model' => $model_name, ':id'=> $id));
		}		
		
		//get data
		$data = $this->get_data();
		
		if ($data === FALSE)
		{
			throw new HTTP_Exception_400('The data was not posted or formated properly.');			
		}
		
		//load posted data
		$Model->load($data);
		
		//save changes		
		$Saved_status = $Model->save();
		
		//set content type header
		$this->response->headers('content-type','application/json');
		
		//set bad status if request failed
		if (!$Saved_status->ok()) 
		{
			$this->response->status(400);
		}		
		else
		{
			$Saved_status->data(NULL,$Model->to_array());	
		}
		
		//return result		
		$this->response->body($Saved_status->to_json());	
	
	}	
	
	/**
	 * deletes a model entry by id
	 * @returns array $data or FALSE
     */			
	public function action_delete()
	{
		//get model name and class
		$model_name = $this->model_name;
		$model_class = $this->model_class;
		
		//get id from the url
		$id = $this->request->param('id');
		
		//load model by id
		$Model = new $model_class($id);
		
		//ensure model loaded
		if ($Model->loaded() === FALSE)
		{
			//404 if model by this id doesn't exist
			throw new HTTP_Exception_404('The requested :model with id :id was not found.',
													array(':model' => $model_name, ':id'=> $id));
		}		
		
		//delete model
		$Deleted_status = $Model->delete();
		
		//set content type header
		$this->response->headers('content-type','application/json');
		
		//set bad status if request failed
		if (!$Deleted_status->ok()) 
		{
			$this->response->status(400);
		}		
		else
		{
			$Deleted_status->data(NULL,$Model->to_array());	
		}
		
		//return result		
		$this->response->body($Deleted_status->to_json());	
	}			

	/**
	 * validates a specific field value
	 * @returns array $status or FALSE
     */			
	public function action_validate_field()
	{	
		//get model name and class
		$model_name = $this->model_name;
		$model_class = $this->model_class;
		
		//get id from the url
		$id = $this->request->param('id');

		//get id_action from the url
		$field_name = $this->request->param('id_action');
		
		if ($id !== '*')
		{
			//load model by id
			$Model = new $model_class($id);
			
			//ensure model loaded
			if ($Model->loaded() === FALSE)
			{
				//404 if model by this id doesn't exist
				throw new HTTP_Exception_404('The requested :model with id :id was not found.',
														array(':model' => $model_name, ':id'=> $id));
			}				
		}
		//if no id was specified
		else
		{
			//create blank object
			$Model = new $model_class();
		}
		
		//get posted data
		$data = $this->get_data();
		
		//load posted data onto model
		$Model->load($data);	
		
		//get the fields
		$fields = $Model->get_fields();
		$single_field_array = array($fields[$field_name]);
		
		$Validate_status = $Model->validate(NULL,$single_field_array);
		
		//set content type header
		$this->response->headers('content-type','application/json');
		
		//set bad status if request failed
		if (!$Validate_status->ok()) 
		{
			$this->response->status(400);
		}
		
		//return result		
		$this->response->body($Validate_status->to_json());			
		
	
	}
	

	/**
	 * queries a model
	 * @param $q string GET PARAM a json string that can be sent directly to the db::read method. must at least contain a 'where' clause
	 * @returns array $data or FALSE
     */			
	public function action_query()
	{	
		//get model name and class
		$model_name = $this->model_name;
		$model_class = $this->model_class;
	
		//parse ?q query
		$query = $this->request->query('q');

		if ($query === NULL || !($query_array = json_decode($query,TRUE)))
		{
			throw new HTTP_Exception_400('The query was not formated properly.');		
		}
	
		//
		$results = $model_class::query($query_array);
		
		//set content type header
		$this->response->headers('content-type','application/json');
		
		//set bad status if request failed
		if (!is_array($results)) 
		{
			$this->response->status(400);
			$this->response->body(array('status'=> FALSE,'message'=> 'There was a problem running this query'));
		}
		else 
		{
			$this->response->body(json_encode($results));
		}
		
	}
			
	/**
	 * queries all models associated with a relationship field
	 * @param $field required URL PARAM <id> the name of the field to search
	 * @param $q string required a search string
	 * @param $limit=10 int the max number of items to return
	 * @returns array $data or FALSE
     */			
	public function action_relsearch()
	{	

		//set content type header
		$this->response->headers('content-type','application/json');		

		//get relationship field to use from the url <id> part
		$field_name = $this->request->param('id');

		//build field class name
		$field_class = 'field_'.$this->model_name.'_'.$field_name;

		//get the field
		$Field = new $field_class();

		//get text query
		$query = $this->request->query('q');

		//get sent limit
		$limit = $this->request->query('limit');

		//if limit was not sent
		if ($limit === NULL)
		{
			//default to 10
			$limit = 10;
		}

		$response_data = array();

		//loop through each possible source and run a "like" query to find valid value's for this field
		foreach ($Field->source as $source)
		{

			//build model class
			$model_class = 'model_'.$source['model'];

			$model_label = $model_class::scfg('label');

			//get additional where params if sent
			if (isset($source['where'])) 
			{
				$model_where = $source['where'];
			}
			else
			{
				$model_where = array();
			}

			//get field name to search
			if (isset($source['search_field']))
			{
				$search_field = $source['search_field'];
			} 
			else
			{
				$search_field = 'name';
			}

			//add in sent query [@todo change $regex to "$like" or something similar]
			$model_where[$search_field] = array('$regex' => '/^'.$query.'.*/i');
			
			$results = $model_class::query(array(
				'from' => $source['model'],
				'where' => $model_where,
				'limit'=> $limit,
			));
			//set bad status if request failed
			if (!is_array($results)) 
			{
				$this->response->status(400);
				$this->response->body(json_encode(array('status'=> FALSE,'message'=> 'There was a problem running this query')));
				return false;
			}
			foreach ($results as $obj)
			{
				$response_data[] = array('_id'=> $obj->_id, 'model'=> $source['model'], 'model_label'=> $model_label, 'search_field'=> $obj->$search_field, 'obj'=> $obj);
				//if we've readhed the limit, break out of both loops
				if (count($response_data) == $limit) 
				{
					//break out of $obj loop and $source loop
					break 2;
				}

			}
		}

		$this->response->body(json_encode($response_data));

	}
	
	/**
	 * returns an html form to edit/extend a model
	 * @param $_id required URL PARAM <id> the _id of the model to load
	 * @param $action=edit URL PARAM <id_action> (edit|extend)
	 * @param string $type = 'input' GET (input|display)     
	 * @param string $theme = 'default' GET theme template folder to look for the form template and field templates 
	 * @param string $template = 'default' GET template folder to look for the form template and field templates. path looks like $theme/$media/$template
	 * @param string $media = 'web' GET media name used to look up this template.  possible values are currently web, tablet, or moble
	 * @param string $ilang = 'en' GET language used to generate interface
	 * @returns json (status,html)
     */			
	public function action_form()
	{	
		//set content type header
		$this->response->headers('content-type','application/json');		


		$action = $this->request->param('id_action');

		if ($action === NULL) {
			$action = 'update';
		}

		//get type
		$type = $this->request->query('type');
		if ($type === NULL) 
		{
			$type = 'input';
		}

		//get theme
		$theme = $this->request->query('theme');
		if ($theme === NULL) 
		{
			$theme = 'default';
		}

		//get template
		$template = $this->request->query('template');
		if ($template === NULL) 
		{
			$template = 'default';
		}

		//get media
		$media = $this->request->query('media');
		if ($media === NULL) 
		{
			$media = 'web';
		}

		//get lang
		$lang = $this->request->query('lang');
		if ($lang === NULL) 
		{
			$lang = 'en';
		}

		$model_class = $this->model_class;

		//we want to load an edit form for this model
		if ($action === 'update')
		{

			//get the model id to use from the url <id> part
			$_id = $this->request->param('id');

			//@todo come up with better way to validate this id?
			if (!valid::alpha_dash($_id))
			{
				$this->response->status(400);
				$this->response->body(json_encode(array('status'=> FALSE,'message'=> 'Invalid id')));
				return false;
			}			
			$form_action = 'update';
			$Model = new $model_class($_id);
			$View = $this->model_view($Model,$type,$form_action, $theme, $template, $media, $lang);

		}
		//we want to create a new model that extends this model
		else if ($action === 'create')
		{
			$form_action = 'create';
			$New_Model = new $model_class();

			//look for additional data in query string so the form is preloaded
			$data_json = $this->request->query('data');
			if ($data_json !== NULL)
			{
				//attempt to decode it
				$data = json_decode($data_json,TRUE);
				if ($data !== NULL) 
				{
					//add it to the new model
					foreach ($data as $field_key => $value)
					{
						$New_Model->set($field_key,$value);
					}
				}
			}

			//generate a form for the new model
			$View = $this->model_view($New_Model,$type,$form_action,$theme, $template, $media, $lang);			
		}
		//we want to create a new model that extends this model
		else if ($action === 'extend')
		{
			//get the model id to use from the url <id> part
			$_id = $this->request->param('id');

			//@todo come up with better way to validate this id?
			if (!valid::alpha_dash($_id))
			{
				$this->response->status(400);
				$this->response->body(json_encode(array('status'=> FALSE,'message'=> 'Invalid id')));
				return false;
			}

			$form_action = 'create';
			$New_Model = new $model_class();

			//load all inheireted fields and set their values
			$inherited = $model_class::scfg('inherited');

			$Model = new $model_class($_id);
			foreach ($inherited as $field_key)
			{
				$New_Model->$field_key = $Model->$field_key;
			}

			//@todo ensure that the model and _id are valid from the source of the extends fields for this model
			/*$fields = $Model->get_fields();
			$extends_field = $fields['extends'];*/

			$New_Model->extends = array("model"=> $this->model_name, "_id"=> $_id);

			//look for additional data in query string
			$data_json = $this->request->query('data');
			if ($data_json !== NULL)
			{
				//attempt to decode it
				$data = json_decode($data_json,TRUE);
				if ($data !== NULL) 
				{
					//add it to the new model
					foreach ($data as $field_key => $value)
					{
						$New_Model->set($field_key,$value);
					}
				}
			}

			//generate a form for the new model
			$View = $this->model_view($New_Model,$type,$form_action,$theme, $template, $media, $lang);
		}

		$response_data = array('status'=> TRUE, 'form_id'=> $View->form_id, 'html'=> $View->render());

		$this->response->body(json_encode($response_data));
	}

	/**
	 * detects how the data was sent, extracts the data from the request, and returns it as an array
	 * @returns array $data or FALSE
     */		
	public function get_data()
	{
		//if data was sent to this api from a form submit, extract the data
		if ($this->request->method() == 'POST' && $this->request->headers('content-type') == 'application/x-www-form-urlencoded' && is_array($this->request->post()))
		{
			//get posted data
			$post = $this->request->post();
			$model_class = $this->model_class;
			$data = $model_class::extract_post_data($post);
			return $data;
		}
		//if data was posted in the body
		else if ($this->request->method() == 'POST' && $this->request->body() != '')
		{
			//fix the string and decode the json
			$data = json_decode($this->prepare_json($this->request->body()),TRUE);
			
			//if jseon_decode worked and we have an array data
			if ($data !== NULL && is_array($data))
			{
				return $data;
			}
		}
		//data was sent sent as expected
		return FALSE;
	}
	
	/**
     * This will convert ASCII/ISO-8859-1 to UTF-8.
	 * Be careful with the third parameter (encoding detect list), because
	 * if set wrong, some input encodings will get garbled (including UTF-8!)
	 * FROM: http://us2.php.net/manual/en/function.json-decode.php#107107
	 * @param string $json_str 
	 * @returns string in utf-8 formatting with fixed formatting so it will be read properly by json_decode
     */	
	public function prepare_json($json_str)
	{
		$json_str = mb_convert_encoding($json_str, 'UTF-8', 'ASCII,UTF-8,ISO-8859-1');
    
		//Remove UTF-8 BOM if present, json_decode() does not like it.
		if(substr($json_str, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF)) $json_str = substr($json_str, 3);
    
		return $json_str;
	}
	
} // End Welcome
