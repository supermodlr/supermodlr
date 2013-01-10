<input class='input' name="extends_autocomplete" id="<?=$form_id; ?>__field__extends__autocomplete" type="text"/>
<input class='input' type="text" id="<?=$form_id; ?>__field__extends" name="field__extends" ng-model="data.model.extends" autocomplete="off" style="display: none" json="true"/>
<ul id='<?=$form_id; ?>__field__extends__list'></ul>
<script type="text/javascript">
$("#<?=$form_id; ?>__field__extends__autocomplete").autocomplete({
            source: function(request,response) {
                var url = getAPIPath()+'/model/query/?q={"where":{"name":{"\$regex":"/^'+request.term+'.*/i"}}}';
                $.getJSON( url, request, function( server_data, status, xhr ) {
                    var ui_data = [];
            
                    for (var i = 0; i < server_data.length; i++) {
                        //add this field as a valid selection to the autocomplete select options
                        ui_data.push({'label': server_data[i].name, '_id': server_data[i]._id});
                    }
                    response(ui_data);
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                <?=$form_id; ?>__extends__add({"_id": ui.item._id, "model": "model"},ui.item.label);
                $(this).val('');
                return false;

            }
        });

function <?=$form_id; ?>__extends__add(obj,label) {

    var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>'); 

    //get the angular scope
    var scope = angular.element(jq[0]).scope();

    //convert field data to string
    json = JSON.stringify(obj);

    //set the string value to the input
    jq.val(json);

    //set the object as the model.fields value
    scope.data.<?=$field->model_name ?>.<?=$field->path('.') ?> = obj;

    //add the ui element for this field
    $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>__list').html('<li id="<?=$form_id; ?>__<?=$field->path('_') ?>__listitem__'+obj._id+'">'+label+'<?php if ($model->is_new()) {?> <a href=\'javascript:<?=$form_id; ?>__<?=$field->path('_') ?>__remove("'+obj._id+'")\'>x</a><?php } ?></li>');

    $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>__autocomplete').hide();     


}

function <?=$form_id; ?>__extends__remove(field_id) {
    $('#<?=$form_id; ?>__extends__listitem__'+field_id).remove();

    var extends_jq = $('#<?=$form_id; ?>__field__extends');

    //set the string value to the input
    extends_jq.val('');

    //trigger input so angular detects the change
    extends_jq.trigger('input');

    //get the angular scope
    var scope = angular.element(extends_jq[0]).scope();

    //set the object as the model.fields value
    delete(scope.data.model.extends);   

    $('#<?=$form_id; ?>__field__extends__autocomplete').show();

<?php /*
foreach ($model->cfg('inherited') as $inherited_field)
{ ?>
    $('#<?=$form_id; ?>__field__<?=$inherited_field ?>__container').show();
    $('#<?=$form_id; ?>__field__<?=$inherited_field ?>__inheritcb').attr('checked',null);
    $('#<?=$form_id; ?>__field__<?=$inherited_field ?>__inheritcb').attr('disabled',null);
    $('#<?=$form_id; ?>__field__<?=$inherited_field ?>__inherit').hide();
<?php
}*/ ?>


    //$('.<?=$form_id; ?>inherit_container').hide();
}


<?php
if ($field->value_isset()) {
    echo '
    if (typeof window.'.$form_id.'_readyfunctions == "undefined") {
        window.'.$form_id.'_readyfunctions = [];
    }
    window["'.$form_id.'_readyfunctions"].push(function() {
        var value = '.$field->raw_value.';
        var labels = '.$field->source['labels'].';      
        '.$form_id.'__extends__add('.$field->raw_value.',labels[(value.model+value._id)]);
    });

';
}
?>

</script>
