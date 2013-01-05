<?php

class Supermodlr_Status {
	protected $_status = NULL;
	protected $_message = NULL;
	protected $_messages = array();
	protected $_data = array();	

	/**
	 * create status object
	 * @param bool $status status to set
	 * @param string $message message to set	 
	 * @param mixed $data data to set	
	 * @param string|int $key key to set message and data against		
	 */
	public function __construct($status = TRUE, $message = '', $data = NULL, $key = NULL) 
	{
		$this->ok($status);
		$this->message($message,$data,$key);

	}
	
	/**
	 * send bool value to set status of this response, call with no argument to get the current status
	 * @param bool|null $status status to set
	 * @return mixed
	 */
	public function ok($status = NULL)
	{
		if ($status === NULL)
		{
			return (bool) $this->_status;
		} 
		else 
		{
			$this->_status = (bool) $status;
		}
		return $this;
	}
	
	/**
	 * sets a message or retrieves the last message sent to this status
	 * @param string|array|null $message message(s) to store
	 * @param mixed $data any data related to this message
	 * @param string|int $key used to store message
	 * @return mixed 
	 */
	public function message($message = NULL, $data = NULL, $key = NULL)
	{
		//if message was not sent, return the last set message
		if (is_null($message))
		{
			return (string) $this->_message;
		} 
		//if message was sent
		else 
		{
			//if message was sent as an empty array, set it to an empty string
			if (is_array($message) && empty($message))
			{
				$message = '';
			}
		
			//if there is more than one message 
			if (is_array($message)) 
			{
				//merge new messages with existing messages
				$this->_messages = array_merge($this->_messages,$message);
				
				//store all messages and reverse (so we can get the last element)
				$all_messages = array_reverse($this->_messages);
				
				//get last element in array and set it as last message
				$last_message = reset($all_messages);
				if (!is_string($last_message)) 
				{
					$this->_message = 'Invalid';//;var_export($last_message,TRUE);
				}
				else 
				{
					$this->_message = $last_message;
				}
				
				
				//if data was sent (must be array that lines up with messages?)
				if (!is_null($data) && is_array($data)) 
				{				
					$this->_data = array_merge($this->_data,$data);
				}
			}
			//there is one message			
			else 
			{
				//assign numeric key of no key for this message was sent
				if (is_null($key))
				{
					$key = count($this->_messages);
				}			
				$this->_message = (string) $message;
				$this->_messages[$key] = (string) $message;
				if (!is_null($data)) 
				{
					$this->_data[$key] = $data;				
				}
			}

		}
	}	
	
	/**
	 * retrieves all messages sent to this status
	 * @param string|int $key send $key to only retrieve the message stored for $key	 
	 * @return mixed 
	 */
	public function messages($key = NULL)
	{
		if (is_null($key))
		{
			return (array) $this->_messages;
		}
		else 
		{
			if (isset($this->_messages[$key]))
			{
				return $this->_messages[$key];
			}
			else 
			{
				return NULL;
			}
		}
	}	
	
	
	
	/**
	 * retrieves all data sent to this status
	 * @param string|int $key send $key to only retrieve data stored for $key
	 * @param string|int $value send $value to store against data (if no key), or on a specific key (if key was sent)
	 * @return mixed 
	 */
	public function data($key = NULL,$value = NULL)
	{
		if ($key === NULL && $value === NULL)
		{
			return (array) $this->_data;
		}
		else if ($key === NULL && $value !== NULL)
		{
			$this->_data = $value;
		}
		else if ($key !== NULL && $value !== NULL)
		{
			$this->_data[$key] = $value;
		}		
		else 
		{
			if (isset($this->_data[$key]))
			{
				return $this->_data[$key];
			}
			else 
			{
				return NULL;
			}
		}
	}		

	/**
	 * converts the enture object to a json string
	 * @return string 
	 */	
	public function to_json()
	{
		//setup status array
		$status = array(
			'status'=> $this->ok()
		);
		
		//add message if set
		$message = $this->message();
		if ($message !== NULL && $message != '')
		{
			$status['message'] = $message;
		}
		
		//add messages if there is more than one message
		$messages = $this->messages();
		if (is_array($messages) && count($messages) > 1)
		{
			$status['messages'] = $messages;
		}	
		
		//add data if set
		$data = $this->data();
		if (is_array($data) && count($data) > 0)
		{
			//if there is only one data entry, store data[0] at data root
			if (count($data) == 1 && isset($data[0]))
			{
				$status['data'] = $data[0];
			}
			else 
			{
				$status['data'] = $data;
			}
		}
		
		return json_encode($status);
	}
}