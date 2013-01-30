<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Controller_Scaffold
 *
 * @uses     Controller_Page
 *
 * @category Category
 * @package  Package
 * @author    <Brandon Krigbaum> brandonbk@gmail.com
 * @license  
 * @link     
 */
abstract class Controller_Scaffold extends Controller_Page {


	public $model_name = NULL;
	public $model_class = NULL;

	
	/**
	 * 
	 */
	public function before()
	{
		parent::before();

	}
	
	/**
	 * 
	 */
	public function after()
	{
		parent::after();
	}
	
	/**
	 * 
	 */
	public function model_name()
	{
		if (is_null($this->model_name))
		{
			$class_name = get_class($this);
			$class_name_array = explode('_', $class_name);
			$this->model_name = array_pop($class_name_array);
		}
		return $this->model_name;
	}

	/**
	 * 
	 */
	public function model_class()
	{
		if (is_null($this->model_class))
		{
			$this->model_class = Supermodlr::name_to_class_name($this->model_name());
		}
		return $this->model_class;
	}

	/**
	 * 
	 */
	public function action_index()
	{

		// If no model param
		if ($this->model_name() === NULL)
		{
		   // List all models that can be modified and link to their pages
		   $this->template = 'list';
		   
		   $models = Supermodlr::get_models();
		   $this->bind('models',$models);
		}

		// If model was sent in url
		else
		{
			$this->template = 'view';

			// Provide link to create new entry
			$model_name = $this->model_name();
			$this->bind('model_name', $model_name);

			// Show create form
			$model_class = $this->model_class();
			$model = new $model_class();

			// Bind the data model
			$this->bind('model', $model);

			// Bind all the fields
			$fields = $model->get_fields();
			$this->bind('fields', $fields);

			// Read list of 10 entries with links to read, update, and delete
			$model_rows = $model_class::query(array(
				'limit'=> 20,
				'array'=> TRUE,
			));
			$this->bind('model_rows', $model_rows);  
		}
	}
    
	/**
	 * 
	 */
	public function action_create()
	{
		// Bind model name
		$model_name = $this->model_name();
		$this->bind('model_name', $model_name);

		// Show create form
		$model_class = $this->model_class();
		$model = new $model_class();

		// Bind the data model
		$this->bind('model', $model);

		// Get form      
		$view = $this->model_view($model, 'input', 'create');
		$form = $view->render();
		$this->bind('form', $form);
		$form_id = $view->form_id;
		$this->bind('form_id', $form_id);
	   
	}

	/**
	 * 
	 */
	public function action_read()
	{
		// Bind model name
		$model_name = $this->model_name();
		$this->bind('model_name', $model_name);

		// Load model by id
		$id = $this->request->param('id');

		// 404 if model doesn't exist
		if ($id === NULL)
		   throw new HTTP_Exception_404('The requested URL :uri was not found on this server.',
		                                           array(':uri' => $this->request->uri()));

		$model_class = $this->model_class();
		$model = new $model_class($id);

		// 404 if model doesn't exist
		if ($model->loaded() === FALSE)
		   throw new HTTP_Exception_404('The requested URL :uri was not found on this server.',
                                       array(':uri' => $this->request->uri()));

		// Show object data
		$view = $this->model_view($model,'display','read');
		$display = $view->render();
		$this->bind('display', $display);    
		$form_id = $view->form_id;
		$this->bind('form_id', $form_id);            

		// Bind the data model
		$this->bind('model', $model);

	}

	/**
	 * 
	 */
	public function action_update()
	{
		// Bind model name
		$model_name = $this->model_name();
		$this->bind('model_name', $model_name);        
        
		// Load model by id
		$id = $this->request->param('id');
		
		// 404 if model doesn't exist
		if ($id === NULL)
			throw new HTTP_Exception_404('The requested URL :uri was not found on this server.',
		                                        array(':uri' => $this->request->uri()));

		$model_class = $this->model_class();
		$model = new $model_class($id);

		// 404 if model doesn't exist
		if ($model->loaded() === FALSE)
			throw new HTTP_Exception_404('The requested URL :uri was not found on this server.',
		                                        array(':uri' => $this->request->uri()));

		// Show update form
		$view = $this->model_view($model,'input','update');
		$form = $view->render();
		$this->bind('form', $form);  
		$form_id = $view->form_id;
		$this->bind('form_id', $form_id);

		// Bind the data model
		$this->bind('model', $model);
    
	}

	/**
	 * 
	 */
	public function action_delete()
	{
		// Bind model name
		$model_name = $this->model_name();
		$this->bind('model_name', $model_name);

		// Load model by id
		$id = $this->request->param('id');
		
		// 404 if model doesn't exist
		if ($id === NULL)
			throw new HTTP_Exception_404('The requested URL :uri was not found on this server.',
	                                              array(':uri' => $this->request->uri()));

		$model_class = $this->model_class();
		$model = new $model_class($id);
		
		// 404 if model doesn't exist
		if ($model->loaded() === FALSE)
			throw new HTTP_Exception_404('The requested URL :uri was not found on this server.',
                                                 array(':uri' => $this->request->uri()));

		// Show delete form
		$View = $this->model_view($model, 'input', 'delete', 'default', 'delete');
		$form = $View->render();
		$this->bind('form', $form);
		$form_id = $View->form_id;
		$this->bind('form_id', $form_id);

		// Bind the data model
		$this->bind('model', $model);

	}

}