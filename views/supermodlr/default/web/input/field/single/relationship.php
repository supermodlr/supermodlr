<input class='input' name="<?=$field->get_model_name() ?>__<?=$field->path('_') ?>__autocomplete" id="<?=$form_id; ?>__<?=$field->path('_') ?>__autocomplete" type="text"/>
<input class='input' type="text" id="<?=$form_id; ?>__field__<?=$field->path('_') ?>" name="<?=$field->get_model_name() ?>__<?=$field->path('_') ?>" ng-model="data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?>" autocomplete="off" style="display: none"/>
<ul id='<?=$form_id; ?>__<?=$field->path('_') ?>__list'></ul>
<script type="text/javascript">
$("#<?=$form_id; ?>__<?=$field->path('_') ?>__autocomplete").autocomplete({
            source: function(request,response) { /*@todo  support field->source as an array to search multiple models and convert $regex to a standard "like" syntax */
                var url = getAPIPath()+'/<?=$field->get_model_name() ?>/relsearch/<?=$field->path('.') ?>?q='+request.term;
                $.getJSON( url, request, function( server_data, status, xhr ) {
                    var ui_data = [];
                    var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>'); 
                    var scope = angular.element(jq[0]).scope();
                    var arr = scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?>;
                    //if the stored data does not parse into a valid json object
                    if (!arr) {
                        //create empty object
                        arr = [];
                    }                   
                    for (var i = 0; i < server_data.length; i++) {
                        //ensure that this value isn't already set as a field
                        for (var fi = 0; fi < arr.length; fi++) {
                            if (arr[fi]._id == server_data[i]._id && arr[fi].model == server_data[i].model) {
                                continue;
                            }
                        }
                        //add this field as a valid selection to the autocomplete select options
                        ui_data.push({'label': server_data[i].search_field, '_id': server_data[i]._id, 'model': server_data[i].model});
                    }
                    response(ui_data);
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                <?=$form_id; ?>__<?=$field->path('_') ?>__add({'_id': ui.item._id, 'model': ui.item.model}, ui.item.label);
                $(this).val('');
                return false;

            }
        });

function <?=$form_id; ?>__<?=$field->path('_') ?>__add(obj,label) {

    var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>'); 

    //get the angular scope
    var scope = angular.element(jq[0]).scope();

    //convert field data to string
    json = JSON.stringify(obj);

    var obj_hash = CryptoJS.MD5(json);

    //set the string value to the input
    //jq.val(json);

    //trigger input so angular detects the change
    //jq.trigger('input');

    //set the object as the model.fields value
    scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?> = obj;
    //add the ui element for this field
    $('#<?=$form_id; ?>__<?=$field->path('_') ?>__list').append('<li id="<?=$form_id; ?>__<?=$field->path('_') ?>__listitem__'+obj_hash+'">'+label+'<?php if ($model->is_new()) {?> <a href=\'javascript:<?=$form_id; ?>__<?=$field->path('_') ?>__remove("'+obj_hash+'")\'>x</a><?php } ?></li>');

    $('#<?=$form_id; ?>__<?=$field->path('_') ?>__autocomplete').hide();        

}

function <?=$form_id; ?>__<?=$field->path('_') ?>__remove(obj_hash) {
    $('#<?=$form_id; ?>__<?=$field->path('_') ?>__listitem__'+obj_hash).remove();

    var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>');

    //var json = jq.val('');

    //trigger input so angular detects the change
    //jq.trigger('input');

    //get the angular scope
    var scope = angular.element(jq[0]).scope();

    //set the object as the model.fields value
    delete(scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?>);  

    $('#<?=$form_id; ?>__<?=$field->path('_') ?>__autocomplete').show();

}


<?php
if ($field->value_isset()) {

    echo '
    if (typeof window.'.$form_id.'_readyfunctions == "undefined") {
        window.'.$form_id.'_readyfunctions = [];
    }
    window["'.$form_id.'_readyfunctions"].push(function() {
        var value = '.json_encode($field->raw_value).';
        if (typeof value == "string") {
            value = $.parseJSON(value);
        }
        var label = '.$field->source['labels'].';
        '.$form_id.'__'.$field->name.'__add(value,label[(value.model+value._id)]);
    });
';
}
?>

</script>
