<?php 
foreach ($fields as $field)
{
	$view->set('field',$field);
	if (!$field->hidden)
	{
		echo $view->get('wrapper',$field);
	} 

}
