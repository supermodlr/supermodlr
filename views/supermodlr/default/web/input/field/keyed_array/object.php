<?php

$orig_field = $field;
$fields = $field->sub_fields;
foreach ($fields as $field) {

	$view->set('field',$field);

	echo $view->get('wrapper',$field);
}
$field = $orig_field;
$view->set('field',$field);