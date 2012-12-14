<span class="error" ng-show="<?=$form_id ?>Form.field__<?=$field->path('_') ?>.$error.server">{{ serverError.<?=$field->path('.') ?> }}</span>
<?php 
	if ($field->required) 
	{ ?>
		<span class="error" ng-show="<?=$form_id ?>Form.field__<?php echo $field->name; ?>.$error.REQUIRED">Required!</span><?php 
	} 
	//@todo echo all possible field messages for so angular validator can display errors