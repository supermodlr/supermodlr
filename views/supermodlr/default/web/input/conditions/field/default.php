<?php
	if ($field->conditions !== NULL && is_array($field->conditions)) 
	{ ?>
		<script type="text/javascript">
function form_container__<?=$form_id ?>__field__<?=$field->path('_'); ?>__conditions() {
	var angular_scope = angular.element($('#<?=$form_id ?>__field__<?=$field->path('_'); ?>')[0]).scope();		
	//bring all scope vars into the local scope
	var scope = angular_scope.data.<?=$field->model_name; ?>;

<?php echo $field->generate_conditions_javascript($form_id.'__field__'); ?>
}		
	
	if (typeof window.<?=$form_id; ?>_readyfunctions == "undefined") {
		window.<?=$form_id; ?>_readyfunctions = [];
	}
	window.<?=$form_id; ?>_readyfunctions.push(function() {
			$('#form_container__<?=$form_id ?> .input').on('change',form_container__<?=$form_id ?>__field__<?=$field->path('_'); ?>__conditions);
			form_container__<?=$form_id ?>__field__<?=$field->path('_'); ?>__conditions();
		});

		</script><?php 
	}