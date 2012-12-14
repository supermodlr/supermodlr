<?=$view->get_view('js/controller'); ?>
<div class="angular_app_container" id="app_container__<?=$form_id ?>" ng-app="<?=$form_id; ?>" ng-controller="<?=$form_id; ?>Ctrl" run-ready>
	<div id="form_container__<?=$form_id ?>">
		<form class="simple-form" ng-submit="submit()" name="<?php echo $form_id; ?>Form">
			<?php 
			foreach ($fields as $field)
			{
				$view->set('field',$field);

				echo $view->get('wrapper',$field);

			}

			?><input class="form_submit_button" type="submit" ng-disabled="{{<?=$form_id ?>Form.$invalid || isUnchanged(<?=$form_id ?>Form)}}"/>
		</form>

	</div>

</div>

