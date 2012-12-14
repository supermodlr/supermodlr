<input class="input" type="checkbox" id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>" name="field__<?=$field->path('_'); ?>" 
ng-model="data.<?=$field->model_name; ?>.<?=$field->path('.'); ?>"<?php 
if ($field->value_isset() && $field->raw_value === TRUE) { 
	echo ' checked="checked" ng-init="data.'.$field->model_name.'.'.$field->name.'=true"'; 
} else {
	echo ' ng-init="data.'.$field->model_name.'.'.$field->name.'=false"'; 
}
	?> autocomplete="off"/>
<input type="hidden" name="checkbox__<?=$field->path('_'); ?>" value="true" autocomplete="off"/>