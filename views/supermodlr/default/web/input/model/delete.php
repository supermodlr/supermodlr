<div ng-app="<?=$form_id; ?>" ng-controller="<?=$form_id; ?>Ctrl">
	<div id="form_container__<?=$form_id ?>">
		<form class="simple-form" ng-submit="submit()" name="<?php echo $form_id; ?>Form">
			<input type="submit" value="Yes"/> <input type="button" value="No" onclick="history.go(-1)"/> 
		</form>
	</div>
</div>
<?=$view->get_view('js/controller'); ?>