<div id="<?php echo $field->get_model_name(); ?>__field__<?php echo $field->path('_'); ?>__container"<?php 
if ($field->hidden || (isset($field->conditions) && is_array($field->conditions) && isset($field->conditions['$hidden']) && $field->conditions['$hidden'])) 
{ 
    ?> style="display:none"<?php } 
?>>
<?php 
echo $view->get('label',$field); 
echo $view->get('field',$field);
echo $view->get('errors',$field);
echo $view->get('conditions',$field);
?>
</div>