<input class='input' name="<?=$field->path('_'); ?>_autocomplete" id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>__autocomplete" type="text"/>
<input class='input' type="text" id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>" name="<?=$field->get_model_name(); ?>__<?=$field->path('_'); ?>" ng-model="data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>" autocomplete="off" style="display: none" json="true"/>
<div id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_container" style="display:none">
    <h3>Add Field</h3>
    <div id='<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_form'></div>

</div>
<ul id='<?=$form_id; ?>__field__<?=$field->path('_'); ?>__list'></ul>

<script type="text/javascript">
$("#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__autocomplete").autocomplete({
            source: function(request,response) {
                var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>');  
                var scope = angular.element(jq[0]).scope();             
                var id = scope.data.<?=$field->get_model_name(); ?>._id;
                //@todo escape request.term so it doesn't break the json
                var url = getAPIPath()+'/field/query/?q={"where":{"name":{"\$regex":"/^'+request.term+'.*/i"},"$or":[{"model":null},{"model._id":"'+id+'"}]}}';
                $.getJSON( url, request, function( server_data, status, xhr ) {
                    var ui_data = [];
                    //allow user to create a new field for this model
                    ui_data.push({'_id': null,'label': 'Add '+request.term, 'field': {'name': request.term}, 'action': 'create'});      

                    var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>');  
                    var scope = angular.element(jq[0]).scope();
                    var arr = scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.'); ?>;
                    //if the stored data does not parse into a valid json object
                    if (!arr) {
                        //create empty object
                        arr = [];
                    }                   
                    for (var i = 0; i < server_data.length; i++) {
                        //ensure that this value isn't already set as a field
                        for (var fi = 0; fi < arr.length; fi++) {
                            if (arr[fi]._id == server_data[i]._id) {
                                continue;
                            }
                        }
                        if (server_data[i].model == null) {
                            //add this field as a valid selection to the autocomplete select options
                            ui_data.push({'_id': server_data[i]._id,'label': 'Extend '+server_data[i].name, 'field': server_data[i], 'action': 'extend'});                          
                        } else if (typeof server_data[i].model == 'object') {
                            ui_data.push({'_id': server_data[i]._id,'label': 'Use '+server_data[i].name, 'field': server_data[i], 'action': 'use'});                            
                        }

                    }

                    response(ui_data);
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                if (ui.item.action == 'extend' || ui.item.action == 'create') {
                    <?=$form_id; ?>__<?=$field->path('_'); ?>__showdetails(ui.item.field,ui.item.action);
                } else if (ui.item.action == 'use') {
                    <?=$form_id; ?>__<?=$field->path('_'); ?>__add({"model": "field", "_id": ui.item.field._id},ui.item.field.name);
                }
                $(this).val('');
                return false;

            }
        });

function <?=$form_id; ?>__<?=$field->path('_'); ?>__showdetails(field,action) {
    //disable this on creation. can only be used on update (since we need the valid pk field class name)
    var jq = $('#<?=$form_id; ?>__field__name');    

    //get the angular scope
    var scope = angular.element(jq[0]).scope();

    if (typeof scope.data.<?=$field->get_model_name(); ?>._id == 'undefined' || !scope.data.<?=$field->get_model_name(); ?>._id)
    {
        $('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_form').html('You must save this model before fields can be added.');
        $("#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_container").dialog("open");
        return false;
    }
    var id = scope.data.<?=$field->get_model_name(); ?>._id;

    //empty existing options
    $('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_form').empty();

    //preloaded form with model already selected
    var data = {"model":{"model":"model","_id":id}};

    //modify field parameters before form is loaded @todo make this work on the api side
    var fields = {"model": {"hidden": true}};

    //add the name to the preloaded form
    if (action == 'create') {
        data.name = field.name;
        var field_id = '*';
    } else if (action == 'extend') {
        var field_id = field._id;
    }

    //create a form for this field
    $.ajax({
        'url': '/supermodlr/api/field/form/'+field_id+'/'+action+'?data='+JSON.stringify(data),
    }).done(function(response) {

        //load the form
        $('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_form').html(response.html);

        angular.bootstrap($('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_form .angular_app_container')[0],window[response.form_id+'_angular_modules']);

        //force-fix model json @todo why do i have to do this hack?? cannot reproduce this problem on jsfiddle: http://jsfiddle.net/EckUe/
        var scope = angular.element($('#'+response.form_id+'__field__name')[0]).scope();
        for (prop in scope.data.field) {
            if (typeof scope.data.field[prop] == 'string' && scope.data.field[prop].indexOf('{') == 0) {
                //attempt to decode this potential json string
                try {
                    var obj = $.parseJSON(scope.data.field[prop]);
                    scope.data.field[prop] = obj;
                } catch (e) {

                }
            }
        }

        //hide the submit button
        $('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_form .form_submit_button').hide();

    }); 

    $("#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_container").dialog("open");


}

/*
options: 

1: add some sort of prefix to all id's and controllers
    pros: better future compatability
    cons: complicated and ugly form/field templates
          prefixes only required for models that can add themselves (example: field.fields) so lots of complication for something that won't be run into a lot
2: load sub-forms into an iframe and add an option to control what submit does or if it is shown
        pros: easier
        cons: seems hackish

*/

function <?=$form_id; ?>__<?=$field->path('_'); ?>__add(obj,label) {

    var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>');  

    //get the angular scope
    var scope = angular.element(jq[0]).scope();

    var exists = false;
    //if the scope value has not been set yet or is null
    if (typeof scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.'); ?> == 'undefined' || scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.'); ?> == null) {
        //get current data from field__field
        var json = jq.val();
        if (typeof json != 'array') {
            var arr = $.parseJSON(json);
        } else {
            var arr = json;

        }
    //if the scope already has a value
    } else {
        var arr = scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.'); ?>;
        for (var fi = 0; fi < arr.length; fi++) {
            if (arr[fi]._id == obj._id) {
                exists = true;
            }
        }   
    }
    //if the stored data does not parse into a valid json object
    if (!arr) {
        //create empty object
        arr = [];
    }

    if (!exists) {
        //add selected field
        arr.push(obj);
        
        //convert field data to string
        json = JSON.stringify(arr);

        //set the string value to the input
        jq.val(json);

        //trigger input so angular detects the change
        jq.trigger('input');

        //set the object as the model.fields value
        scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.'); ?> = arr;

    }
    if ($('#<?=$form_id; ?>__<?=$field->path('_'); ?>__listitem__'+obj._id).length == 0) {
        //add the ui element for this field
        $('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__list').append('<li id="<?=$form_id; ?>__<?=$field->path('_'); ?>__listitem__'+obj._id+'">'+label+' <a href=\'javascript:<?=$form_id; ?>__<?=$field->path('_'); ?>__remove("'+obj._id+'")\'>x</a></li>');
    }
}

function <?=$form_id; ?>__<?=$field->path('_'); ?>__remove(obj_id) {
    $('#<?=$form_id; ?>__<?=$field->path('_'); ?>__listitem__'+obj_id).remove();

    var jq = $('#<?=$form_id; ?>__field__<?=$field->path('_'); ?>');

    var json = jq.val();

    var scope = angular.element(jq[0]).scope();
    var arr = scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.'); ?>;

    var new_arr = [];
    //add all fields to array except the removed field
    for (var fi = 0; fi < arr.length; fi++) {
        if (arr[fi]._id != obj_id) {
            new_arr.push(arr[fi]);
        }
    }   

    //convert field data to string
    json = JSON.stringify(new_arr);

    //set the string value to the input
    jq.val(json);

    //trigger input so angular detects the change
    jq.trigger('input');

    //get the angular scope
    var scope = angular.element(jq[0]).scope();

    //set the object as the field.fields value
    scope.data.<?=$field->get_model_name() ?>.<?=$field->path('.'); ?> = new_arr; 


}

$("#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_container").dialog({
    autoOpen: false,
    height: 600,
    width: 600,
    modal: true,
    buttons: {
        "Add Field": function() {
            var jq = $('#<?=$form_id; ?>__field__name');    

            //get the angular scope
            var scope = angular.element(jq[0]).scope();

            if (typeof scope.data.<?=$field->get_model_name() ?>._id == 'undefined' || !scope.data.<?=$field->get_model_name() ?>._id)
            { 
                $("#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_container").dialog( "close" );   
                return false;       
            }

            var sub_field_scope = angular.element($("#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_container div.ng-scope")[0]).scope();
            sub_field_scope.modal_form = true;
            //when the sub field is saved
            sub_field_scope.$on('saved',function(e,response) {
                //push the new id into the scope
                //sub_field_scope.data.field.fields.push(response._id);
                if (typeof response.data.label != 'undefined') {
                    var label = response.data.label;
                } else if (typeof response.data.name != 'undefined') {
                    var label = response.data.name;
                } else {
                    var label = response.data._id;
                }
                
                <?=$form_id; ?>__<?=$field->path('_'); ?>__add({"_id": response.data._id,"model": "field"},label);    
                            
                //close the dialog
                $("#<?=$form_id; ?>__field__<?=$field->path('_'); ?>__add_container").dialog( "close" );
            });

            //submit the sub form
            sub_field_scope.submit();



        }
    },
});

<?php

if ($field->value_isset()) 
{
    echo '
    if (typeof window.'.$form_id.'_readyfunctions == "undefined") {
        window.'.$form_id.'_readyfunctions = [];
    }
    window.'.$form_id.'_readyfunctions.push(function() {
        var values = '.$field->raw_value.';
        var labels = '.$field->source['labels'].';
        for (var i = 0; i < values.length; i++) {
            '.$form_id.'__'.$field->name.'__add(values[i],labels[(values[i].model+values[i]._id)]);
        }       
    });
';
}

?></script>
