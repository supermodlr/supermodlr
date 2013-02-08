<input class='input' name="<?=$field->get_model_name() ?>__<?=$field->path('_') ?>__autocomplete" id="<?=$form_id; ?>__field__<?=$field->path('_') ?>__autocomplete" type="text"/>
<input class='input' type="text" id="<?=$form_id; ?>__field__<?=$field->path('_') ?>" name="<?=$field->get_model_name() ?>__<?=$field->path('_') ?>" ng-model="data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?>" autocomplete="off" style="display: none"/>
<ul id='<?=$form_id; ?>__field__<?=$field->path('_') ?>__list'></ul>
<script type="text/javascript">
$("#<?=$form_id; ?>__field__<?=$field->path('_') ?>__autocomplete").autocomplete({
            source: function(request,response) { /*@todo  support field->source as an array to search multiple models and convert $regex to a standard "like" syntax */
                var url = getAPIPath()+'/<?=$field->get_model_name() ?>/relsearch/<?=$field->path('.') ?>?q='+request.term;
                $.getJSON( url, request, function( server_data, status, xhr ) {
                    var ui_data = [];
                    var fields_json = $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>').val();
                    var fields_array = $.parseJSON(fields_json);
                    //if the stored data does not parse into a valid json object
                    if (!fields_array) {
                        //create empty object
                        fields_array = [];
                    }                   
                    for (var i = 0; i < server_data.length; i++) {
                        //ensure that this value isn't already set as a field
                        for (var fi = 0; fi < fields_array.length; fi++) {
                            if (fields_array[fi]._id == server_data[i]._id && fields_array[fi].model == server_data[i].model) {
                                continue;
                            }
                        }
                        //add this field as a valid selection to the autocomplete select options
                        ui_data.push({'label': server_data[i].search_field, 'value': server_data[i]._id, 'model': server_data[i].model});
                    }
                    response(ui_data);
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                <?=$form_id; ?>__<?=$field->path('_') ?>__add({'_id': ui.item.value, 'model': ui.item.model}, ui.item.label);
                $(this).val('');
                return false;

            }
        });

function <?=$form_id; ?>__<?=$field->path('_') ?>__add(obj,label) {

    var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>'); 

    //get the angular scope
    var scope = angular.element(jq[0]).scope();

    var exists = false;

    //if the scope value has not been set yet or is null
    if (typeof scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?> == 'undefined' || scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?> == null) {
        //get current data from field__fields
        var json = jq.val();
        if (typeof json != 'array') {
            var arry = $.parseJSON(json);
        } else {
            var arry = json;
        }
    //if the scope already has a value
    } else {
        var arry = scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?>;

        for (var fi = 0; fi < arry.length; fi++) {
            //convert field data to string
            rowjson = JSON.stringify(arry[fi]);
            rowjson_hash = CryptoJS.MD5(rowjson);          

            objjson = JSON.stringify(obj);
            objjson_hash = CryptoJS.MD5(objjson);

            if (rowjson_hash.toString() == objjson_hash.toString()) {
                exists = true;
            }
        }           
    }

    //if the stored data does not parse into a valid json object
    if (!arry) {

        //create empty object
        arry = [];
    }

    if (!exists) {

        //add selected field
        arry.push(obj);
        
        //convert field data to string
        json = JSON.stringify(arry);

        var obj_hash = CryptoJS.MD5(json);

        //set the string value to the input
        jq.val(json);

        //trigger input so angular detects the change
        //jq.trigger('input');

        //set the object as the model.fields value
        scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?> = arry;

    }

    if ($('#<?=$form_id; ?>__field__<?=$field->path('_') ?>__listitem__'+obj_hash).length == 0)
    {
        //add the ui element for this field if it doesn't exist
        $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>__list').append('<li id="<?=$form_id; ?>__field__<?=$field->path('_') ?>__listitem__'+obj_hash+'">'+label+'<?php if ($model->is_new()) {?> <a href=\'javascript:<?=$form_id; ?>__<?=$field->path('_') ?>__remove("'+obj_hash+'")\'>x</a><?php } ?></li>');            
    }





}

function <?=$form_id; ?>__<?=$field->path('_') ?>__remove(obj_hash) {
    $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>__listitem__'+obj_hash).remove();

    var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>');

    var json = jq.val();
    var arry = $.parseJSON(json);

    var new_arry = [];
    //add all fields to array except the removed field
    for (var fi = 0; fi < arry.length; fi++) {
        var this_json = JSON.stringify(arry[fi]);
        var this_hash = CryptoJS.MD5(this_json);
        if (this_hash.toString() != obj_hash.toString()) {
            new_arry.push(arry[fi]);
        }
    }   

    //convert field data to string
    json = JSON.stringify(new_arry);

    //set the string value to the input
    jq.val(json);

    //trigger input so angular detects the change
    jq.trigger('input');

    //get the angular scope
    var scope = angular.element(jq[0]).scope();

    //set the object as the model.fields value
    scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.') ?> = new_arry;   

    $('#<?=$form_id; ?>__field__<?=$field->path('_') ?>__autocomplete').show();

}


<?php
if ($field->value_isset()) {

    echo '
    if (typeof window.'.$form_id.'_readyfunctions == "undefined") {
        window.'.$form_id.'_readyfunctions = [];
    }
    window["'.$form_id.'_readyfunctions"].push(function() {
        var value = '.$field->raw_value.';
        var label = '.$field->source['labels'].';
        for (var i = 0; i < value.length; i++) {
            '.$form_id.'__'.$field->name.'__add(value[i],label[(value[i].model+value[i]._id)]);
        }

        
    });
';
}
?>

</script>
