<select id='<?=$form_id; ?>__field__validation__select' class='input input-medium' onchange="<?=$form_id; ?>__field__validation__showdetails()">
    <option value="">Select</option>
    <option value="not_empty">not_empty</option>
    <option value="regex">regex</option>
    <option value="min_length">min_length</option>
    <option value="max_length">max_length</option>
    <option value="exact_length">exact_length</option>
    <option value="email">email</option>
    <option value="url">url</option>
    <option value="ip">ip</option>
    <option value="phone">phone</option>
    <option value="alpha">alpha</option>
    <option value="alpha_numeric">alpha_numeric</option>
    <option value="alpha_dash">alpha_dash</option>
    <option value="digit">digit</option>
    <option value="numeric">numeric</option>
    <option value="range">range</option>
    <option value="decimal">decimal</option>
    <option value="color">color</option>
    <option value="matches">matches</option>
    <option value="callback">callback</option>  
</select>
<div id="<?=$form_id; ?>__field__validation__options_container" style="display:none">
    <ul id='<?=$form_id; ?>__field__validation__options'></ul>
</div>
<ul id='<?=$form_id; ?>__field__validation__rules'></ul>
<input type="hidden" ng-change="validate('<?=$field->path('_'); ?>')" 
ng-model="data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>" id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>" name="field__<?=$field->path('_'); ?>" autocomplete="off"/>
<script type="text/javascript">
var <?=$form_id; ?>__field__validation__details = {
    "not_empty": [],
    "regex": ['expression'],
    "min_length": ['min_length'],
    "max_length": ['max_length'],
    "exact_length": ['exact_length'],
    "email": [],
    "url": [],
    "ip": [],
    "phone": ['length'],
    "alpha": [],
    "alpha_numeric": [],
    "alpha_dash": [],
    "digit": [],
    "numeric": [],
    "range": ['min','max'],
    "decimal": ['decimal','digits'],
    "color": [], 
    "matches": ['field'],
    "callback": ['callback'],
}

function <?=$form_id; ?>__field__validation__showdetails() {
    //empty existing options
    $('#<?=$form_id; ?>__field__validation__options').empty();

    var rule_key = $('#<?=$form_id; ?>__field__validation__select').val();      

    //get options for the selected rule
    var rules = <?=$form_id; ?>__field__validation__details[rule_key];

    //if there are no options
    if (rules.length == 0) {
        //add rule
        <?=$form_id; ?>__field__validation__add();
    //there are rules, so display the options
    } else {
        //add a li with an input for each option
        for (var i = 0; i < rules.length; i++) {
            $('#<?=$form_id; ?>__field__validation__options').append('<li>'+rules[i]+': <input rule-option="'+rules[i]+'" type="text"/></li>');
        }
        $("#<?=$form_id; ?>__field__validation__options_container").dialog( "open" );

    }
}

function <?=$form_id; ?>__field__validation__add(options) {
    //scope
    var scope = angular.element($('#<?=$form_id; ?>__field__validation')[0]).scope();

    if (typeof scope.data.field.validation == 'undefined') {
        scope.data.field.validation = [];
    }

    //get existing array of rules
    var rules = scope.data.field.validation;    

    //get selected rule
    var rule_key = $('#<?=$form_id; ?>__field__validation__select').val();

    //create new rule
    var rule = [rule_key, null];


    if (typeof options == 'undefined') {
        rule[1] = [];
        //add all rule options
        $('#<?=$form_id; ?>__field__validation__options li input').each(function() {
            
            rule[1].push($(this).val());
        });     
        if (rule[1].length == 0) {
            rule[1] = null;
        }
    } else {
        rule[1] = [];
        for (option in options) {
            rule[1].push(options[option]);
        }
        if (rule[1].length == 0) {
            rule[1] = null;
        }
    }

    //generate a hash for this rule
    var rule_hash = CryptoJS.MD5(JSON.stringify(rule));

    //add new rule to existing rules array
    rules.push(rule);

    //convert rules data to string
    var rules_json = JSON.stringify(rules);

    //set the string value to the input
    $('#<?=$form_id; ?>__field__validation').val(rules_json);

    //force model update
    $('#<?=$form_id; ?>__field__validation').trigger('input');

    //update scope directly with array value
    scope.data.field.validation = rules;

    if (rule[1] && rule[1].length > 0) {
        var rule_title = '<a title="edit this rule" href="javascript: <?=$form_id; ?>__field__validation__edit(\''+rule_hash+'\')">'+rule_key+'</a>';
    } else {
        var rule_title = rule_key;
    }

    //add rule to rule list
    $('#<?=$form_id; ?>__field__validation__rules').append('<li id="'+rule_hash+'">'+rule_title+' <a href="javascript: <?=$form_id; ?>__field__validation__remove(\''+rule_hash+'\')">x</a></li>');

    //empty existing options
    $('#<?=$form_id; ?>__field__validation__options').empty();  

    //hide options box
    $("#<?=$form_id; ?>__field__validation__options_container").dialog( "close" );

    //reset select
    $('#<?=$form_id; ?>__field__validation__select').val($('#<?=$form_id; ?>__field__validation__select options:first').val());
}

function <?=$form_id; ?>__field__validation__edit(id) {
    //scope
    var scope = angular.element($('#<?=$form_id; ?>__field__validation')[0]).scope();

    //get existing array of rules
    var rules = scope.data.field.validation;

    //loop through all rules
    for (var i = 0; i < rules.length; i++) {
        //hash this rule
        var rule_hash = CryptoJS.MD5(JSON.stringify(rules[i]));

        //if this rule is the one we are editing
        if (rule_hash == id) {
            rule = rules[i];
            break;
        }
    }

    //force select value
    $('#<?=$form_id; ?>__field__validation__select').val(rule[0]);

    //force-select this
    <?=$form_id; ?>__field__validation__showdetails();

    //populate option inputs
    var opt_index = 0;
    $('#<?=$form_id; ?>__field__validation__options li input').each(function() {

        var option_value = rule[1][opt_index];
        $(this).val(option_value);
        opt_index++;
    });

    //remove the selected rule
    <?=$form_id; ?>__field__validation__remove(id);

}

function <?=$form_id; ?>__field__validation__remove(id) {
    //scope
    var scope = angular.element($('#<?=$form_id; ?>__field__validation')[0]).scope();

    //get existing array of rules
    var rules = scope.data.field.validation;

    var new_rules = [];

    //loop through all rules
    for (var i = 0; i < rules.length; i++) {
        //hash this rule
        var rule_hash = CryptoJS.MD5(JSON.stringify(rules[i]));

        //if this rule is not the one we are removing
        if (rule_hash != id) {
            new_rules.push(rules[i]);
        }
    }

    //remove the selected rule from the list display
    $('#'+id).remove();

    //convert rules data to string
    var rules_json = JSON.stringify(new_rules);

    //set the string value to the input
    $('#<?=$form_id; ?>__field__validation').val(rules_json);

    //force model update
    $('#<?=$form_id; ?>__field__validation').trigger('input');

    //update scope directly with array value
    scope.data.field.validation = new_rules;    
}

$("#<?=$form_id; ?>__field__validation__options_container").dialog({
    autoOpen: false,
    height: 300,
    width: 350,
    modal: true,
    buttons: {
        "Add Rule": function() {
            <?=$form_id; ?>__field__validation__add();
        }
    },
});

<?php

if ($field->value_isset())
{ ?>
    if (typeof window.<?=$form_id; ?>_readyfunctions == "undefined") {
        window.<?=$form_id; ?>_readyfunctions = [];
    }
    window['<?=$form_id; ?>_readyfunctions'].push(function() {
        var values = <?=$field->raw_value ?>;
        for (var i = 0; i < values.length; i++) {
            $('#<?=$form_id; ?>__field__validation__select').val(values[i][0]);
            <?=$form_id; ?>__field__validation__add(values[i][1]);
        }
    });
<?php
}

?>

</script>
