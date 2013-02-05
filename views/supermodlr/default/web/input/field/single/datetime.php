<input class="input" type="datetime" ui-date id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>" name="field__<?=$field->path('_'); ?>" 
ng-model="data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>" autocomplete="off"/>
