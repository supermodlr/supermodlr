<?php defined('SYSPATH') or die('No direct script access.');

class View extends Kohana_View {

	public function get_view($template = 'default', $theme = NULL, $media = NULL)
	{
		//if theme was not sent and if theme is set on the parent view
		if ($theme === NULL && isset($this->theme))
		{
			//set the theme to same as parent
			$theme = $this->theme;
		}
		//if a theme was not sent and the theme is not set on the parent
		else if ($theme === NULL)
		{
			//set the sent theme to default
			$theme = 'default';
		}

		//if media was not sent and if media is set on the parent view
		if ($media === NULL && isset($this->media))
		{
			//set the media to same as parent
			$media = $this->media;
		}
		//if a media was not sent and the media is not set on the parent
		else if ($media === NULL)
		{
			//set the sent media to default
			$media = 'web';
		}

		$template_paths = array();
		//if template was sent as a string
		if (!is_array($template))
		{
			$template_paths[] = $template;
		}
		//if template is an array
		else
		{
			$template_paths = $template;
		}
		
		$template_path = NULL;
		
		//loop through all paths
		foreach ($template_paths as $path)
		{
			if ($theme != 'default')
			{
				//look for non default media if sent
				if ($media != 'web')
				{
					$file_path = 'supermodlr/'.$theme.'/'.$media.'/'.$path;
					$template_path = Kohana::find_file('views', $file_path);
					
					//if we found a template, stop the loop
					if ($template_path !== NULL) break;
				}

				//look for default media
				$file_path = 'supermodlr/'.$theme.'/web/'.$path;

				//if a file wasn't found for this path for the sent media, use default media of web
				$template_path = Kohana::find_file('views', $file_path);

				//if we found a template, stop the loop
				if ($template_path) break;
				
			}
			
			//look for default theme with non default media if sent
			if ($media != 'web')
			{
				$file_path = 'supermodlr/default/'.$media.'/'.$path;
				$template_path = Kohana::find_file('views', $file_path);
				
				//if we found a template, stop the loop
				if ($template_path !== NULL) break;
			}
			
			//look for default theme and default media
			$file_path = 'supermodlr/default/web/'.$path;
			//if a file wasn't found for this path for the sent media, use default media of web
			$template_path = Kohana::find_file('views', $file_path);

			//if we found a template, stop the loop
			if ($template_path) break;
			
		}

		//if the template was found
		if ($template_path)
		{
			//create the view object
			$View = new View($file_path,$this->_data);
			
			//bind view to itself
			$View->set('view',$View);

			//return the view
			return $View;
		}
		//if no view file was found
		else
		{
			return FALSE;
		}
	}

	public function get($path = '',$object = NULL)
	{
		if ($object === NULL) 
		{
			$object = $this->model;
		}
		if (isset($this->template))
		{
			$template = $this->template;
		}
		else
		{
			$template = NULL;
		}
		
		$paths = NULL;
		if ($object instanceof Field) 
		{
			$paths = $this->controller->get_field_template_paths($object,$this->type,$path, $template);
		} 
		else if ($object instanceof supermodlr_core)
		{
			$paths = $this->controller->get_model_template_paths($object,$this->type,$path, $template);
		}

		if ($paths !== NULL)
		{
			return $this->get_view($paths);	
		}
		else 
		{
			return NULL;
		}
		
	}

	/**
	 * Captures the output that is generated when a view is included.
	 * The view data will be extracted to make local variables. This method
	 * is static to prevent object scope resolution.
	 *
	 *     $output = View::capture($file, $data);
	 *
	 * @param   string  $kohana_view_filename   filename
	 * @param   array   $kohana_view_data       variables
	 * @return  string
	 */
	protected static function capture($kohana_view_filename, array $kohana_view_data)
	{
		// Import the view variables to local namespace
		extract($kohana_view_data, EXTR_SKIP);

		if (View::$_global_data)
		{
			// Import the global view variables to local namespace
			extract(View::$_global_data, EXTR_SKIP | EXTR_REFS);
		}

		// Capture the view output
		ob_start();

		try
		{
			if (isset($_GET['debug']) || isset($_COOKIE['debug'])) echo "<!-- START $kohana_view_filename -->";
			// Load the view within the current scope
			include $kohana_view_filename;
			if (isset($_GET['debug']) || isset($_COOKIE['debug'])) echo "<!-- END $kohana_view_filename -->";
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();

			// Re-throw the exception
			throw $e;
		}

		// Get the captured output and close the buffer
		return ob_get_clean();
	}
}