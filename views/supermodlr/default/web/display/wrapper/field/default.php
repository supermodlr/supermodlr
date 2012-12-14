<div id="<?php echo $field->model_name; ?>__field__<?php echo $field->name; ?>__container"<?php 
if ($field->hidden || (isset($field->conditions) && is_array($field->conditions) && $field->conditions['$hidden'])) 
{ 
	?> style="display:none"<?php } 
?>>
<?php

echo $view->get('label',$field); 
echo $view->get('field',$field);
?>
</div>