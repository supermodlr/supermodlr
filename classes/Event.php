<?php

/**
 * This class implements an event/event calling system.  functions or class/methods can be assigned to a event 'key' and will be run when that point in the code is called.  
 * Variables are passed by reference so that external functions can modify internal variables directly
 * event::register('user_login','my_custom_function');
 * event::call('user_login',$User);
 * function my_custom_function($User) {
 *    set_cookie('custom',$User->id);
 *    $User->custom_cookie_set = TRUE;
 * }
 */

class event 
{
	public static $events = array();

	/**
	 * registers a callback to a event key
	 * @param $key (string) event identifyer
	 * @param $callback (string|function) if this is a string, it must exist as a function.  if it is an array, then it must be callable (passed as [$object,'method'] or ['class','method'])
	 * @param $args (array|NULL) variable(s) passed by reference as args to the callback.  these args override any $args sent to callback by the 'call' method	 
	 */
	public static function register($key, $callback, &$args = NULL)
	{
		if (!isset(self::$events[$key])) self::$events[$key] = array();
		self::$events[$key][] = array('callback'=> $callback, 'args'=> &$args);
	}
	
	/**
	 * calls all callbacks for a given key with $args passed by reference
	 * @param $key (string) event identifyer
	 * @param $callback (string|function) if this is a string, it must exist as a function.  if it is an array, then it must be callable (passed as [$object,'method'] or ['class','method'])
	 */	
	public static function call($key,&$args = array())
	{
		//return null if there are no callbacks assigned to this key
		if (!isset(self::$events[$key]))
		{
			return NULL;
		}
		
		//loop through each callback assigned to thhis key
		foreach (self::$events as $i => $event)
		{
			//get call type for callback (function, object, or static
			$call_type == self::callable($event['callback']);
			
			//use 'call' args if no args were sent to the initial registration
			if (!empty($event['args'])) $args = &$event['args']; 
			
			try {
				if ($call_type == 'function') 
				{
					$event['callback']($args);
				}
				else if ($call_type == 'static')
				{
					$event['callback'][0].'::'.$event['callback'][1]($args);
				} 
				else if ($call_type == 'object')
				{
					$event['callback'][0]->$event['callback'][1]($args);
				}
			} catch (Exception $e) 
			{
				throw new Kohana_Exception('event call for key "'.$key.'" caused exception: '.$e->getMessage());
			}			
		}
	}

	/**
	 * calls all callbacks for a given key with $args passed by reference
	 * @param $callback (string|function) if this is a string, it must exist as a function.  if it is an array, then it must be callable (passed as [$object,'method'] or ['class','method'])
	 * @return (mixed) returns (string) of 'function', 'static', or 'object' if it is callable or (bool) FALSE if not callable
	 */		
	public static function callable($callback)
	{
		//if callback is an object,method or class,method call
		if (is_array($callback))
		{
			if (is_object($callback[0])
			{
				if (is_callable($callback[0],$callback[1]))
				{
					return 'object';
				}
			}
			else if (is_string($callback[0])
			{
				if (is_callable($callback[0].'::'.$callback[1]))
				{
					return 'static';
				}
			}
			else
			{
				return FALSE;
			}
		}
		//if callback is a string, ensure it is a call to a valid function
		else if (is_string($callback) && function_exists($callback))
		{
			return 'function';
		}
		return FALSE;
	}
	
}