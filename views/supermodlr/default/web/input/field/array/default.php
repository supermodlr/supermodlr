<?php
//select box
if (isset($field->values) && is_array($field->values) && count($field->values) > 0)
{
    //
?><select ui-select2 class='input input-medium' id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>" 
name="field__<?=$field->path('_'); ?>" ng-model="data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>" 
ng-init="<?=$field->get_model_name(); ?>.<?=$field->name; ?>='<?php 
if ($field->value_isset()) 
    { 
        echo $field->value; 
    } 
    else if ($field->defaultvalue()) 
    { 
        echo $field->defaultvalue(); 
    }?>'" multiple="multiple"><?php
    if (($field->value_isset() && $field->value === '') || !$field->value_isset()) 
    {
        ?><?php
    }
    foreach ($field->values as $k => $option_value)
    {
        //@todo support keys and multiple languages for the label and optgroups
        ?><option value="<?=$option_value; ?>"<?php 
        if (($field->raw_value == $option_value) || (!$field->value_isset() && $option_value === $field->defaultvalue()) || (is_array($field->php_value) && in_array($option_value, $field->php_value))) 
        { 
            echo 'selected="selected"'; 
        }?>><?php echo ucfirst($option_value); ?></option>
        <?php
    }
    ?></select><?php
} 
else
{
    ?><input class='input' type="<?php 

        //if this field needs to be a number
        if ($field->datatype === 'int' || $field->datatype === 'float' || $field->datatype === 'timestamp' || (is_array($field->validation) && (in_array(array('numeric'),$field->validation) || in_array(array('decimal'),$field->validation) || in_array(array('digit'),$field->validation)))) 
        {
            echo 'number';
            $type = 'number';
        }
        //check for email
        else if (is_array($field->validation) && in_array('email',$field->validation) )
        {
            echo 'email';
            $type = 'email';
        }
        //check for url 
        else if (is_array($field->validation) && in_array('url',$field->validation) )
        {
            echo 'url';
            $type = 'url';
        }
        //default to text
        else
        {
            echo 'text';
            $type = 'text';
        }
    ?>" id="<?=$form_id ?>__field__<?php echo $field->name; ?>__input" name="field__<?php echo $field->name; ?>__input"<?php 

    $maxlen_added = FALSE;
    //add max length
    if ($field->maxlength !== NULL && is_numeric($field->maxlength))
    {
        echo " ng-maxlength='".$field->maxlength ."'";
        $maxlen_added = TRUE;
    }

    //add step. set to any if this should be a decimal number
    if ($type === 'number' && $field->datatype === 'float')
    {
        echo " step='any'";
    }

    //loop through validation to add client side validation where possible
    if (is_array($field->validation)) 
    {
        foreach ($field->validation as $rule)
        {
            //add min/max value
            if (is_array($rule) && isset($rule[0]) && $rule[0] === 'range' && isset($rule[1]) && is_numeric($rule[1]) && isset($rule[2]) && is_numeric($rule[2]))
            {
                echo " min='".$rule[1]."' max='".$rule[2]."'";
            }
            //add min length
            else if (is_array($rule) && isset($rule[0]) && ($rule[0] === 'min_length' || $rule[0] === 'exact_length') && isset($rule[1]) && is_numeric($rule[1]))
            {
                echo " ng-minlength='".$rule[1]."'";
            }
            //add max value
            else if ($maxlen_added === FALSE && is_array($rule) && isset($rule[0]) && ($rule[0] === 'max_length' || $rule[0] === 'exact_length') && isset($rule[1]) && is_numeric($rule[1]))
            {
                echo " ng-maxlength='".$rule[1]."'";
            }
            //add regexp
            else if (is_array($rule) && isset($rule[0]) && $rule[0] === 'regex' && isset($rule[1]))
            {               
                echo ' ngPattern="'.str_replace('"',"&quot;",$rule[1]).'"';
            }
        }
    }

    ?>/> <a href='javascript: <?=$form_id; ?>__<?=$field->path('_'); ?>__add()'>Add</a>
    <input type="hidden" ng-change="validate('<?=$field->path('_'); ?>')" ng-model="data.<?php echo $field->get_model_name(); ?>.<?php echo $field->path('.'); ?>" 
    id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>" 
    name="field__<?=$field->path('_'); ?>"value="<?php 
    if ($field->value_isset()) echo $field->value; ?>" <?php
    if ($field->nullvalue !== FALSE || $field->defaultvalue() !== NULL) { 
        ?>ng-init="<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>='<?php if ($field->value_isset()) { echo $field->value; } else if ($field->defaultvalue()) { echo $field->defaultvalue(); }?>'"<?php
    } ?> autocomplete="off"<?php
    //check for required
    if ($field->required || (is_array($field->validation) && in_array(array('not_empty'),$field->validation))) 
    { 
        echo ' required'; 
    } ?>/>
    <?php


    ?><ul id='<?=$form_id ?>__field__<?=$field->path('_'); ?>__list'></ul>
    <script type="text/javascript">
    function <?=$form_id ?>__<?=$field->path('_'); ?>__add(name) {

        if (typeof name == 'undefined') {
            var name = $('#<?=$form_id ?>__field__<?=$field->path('_'); ?>__input').val();
            if (name == '') {
                return null;
            }
        }

        var jq = $('#<?=$form_id ?>__field__<?=$field->path('_'); ?>'); 

        //get the angular scope
        var scope = angular.element(jq[0]).scope();

        var exists = false;

        //if the scope value has not been set yet or is null
        if (typeof scope.data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?> == 'undefined' || scope.data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?> == null) {
            //get current data from field__field
            var jsonstr = jq.val();
            if (typeof jsonstr != 'array') {
                var arr = $.parseJSON(jsonstr);
            } else {
                var arr = jsonstr;
            }
        //if the scope already has a value
        } else {
            var arr = scope.data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>;
            for (var fi = 0; fi < arr.length; fi++) {
                if (arr[fi] == name) {
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
            arr.push(name);
            
            //convert field data to string
            jsonstr = JSON.stringify(arr);

            //set the string value to the input
            jq.val(jsonstr);

            //trigger input so angular detects the change
            jq.trigger('input');

            //set the object as the model.fields value
            scope.data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?> = arr;

        }

        if ($('#<?=$form_id ?>__<?=$field->path('_'); ?>__listitem__'+name).length == 0) {
            //add the ui element for this field
            $('#<?=$form_id ?>__field__<?=$field->path('_'); ?>__list').append('<li id="<?=$form_id ?>__<?=$field->path('_'); ?>__listitem__'+name+'">'+name+' <a href=\'javascript:<?=$form_id ?>__<?=$field->path('_'); ?>__remove("'+name+'")\'>x</a></li>');

            $('#<?=$form_id ?>__field__<?=$field->path('_'); ?>__input').val('');       
        }


    }

    function <?=$form_id ?>__<?=$field->path('_'); ?>__remove(name) {
        $('#<?=$form_id ?>__<?=$field->path('_'); ?>__listitem__'+name).remove();

        var jq = $('#<?=$form_id ?>__field__<?=$field->path('_'); ?>');

        var jsonstr = jq.val();
        var arr = $.parseJSON(jsonstr);

        var new_arr = [];
        //add all fields to array except the removed field
        for (var fi = 0; fi < arr.length; fi++) {
            if (arr[fi] != name) {
                new_arr.push(arr[fi]);
            }
        }   

        //convert field data to string
        jsonstr = JSON.stringify(new_arr);

        //set the string value to the input
        jq.val(jsonstr);

        //trigger input so angular detects the change
        jq.trigger('input');

        //get the angular scope
        var scope = angular.element(jq[0]).scope();

        //set the object as the model.fields value
        scope.data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?> = new_arr;    
    }

    <?php

    if ($field->value_isset()) 
    {
        echo '
        if (typeof window.'.$form_id.'_readyfunctions == "undefined") {
            window.'.$form_id.'_readyfunctions = [];
        }
        window["'.$form_id.'_readyfunctions"].push(function() {
            var values = '.$field->raw_value.';
            for (var i = 0; i < values.length; i++) {
                '.$form_id.'__'.$field->name.'__add(values[i]);
            }
        });
    ';
    }

    ?></script>

<?php

}