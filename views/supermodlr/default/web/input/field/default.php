<?php
//select box
if (isset($field->values) && is_array($field->values) && count($field->values) > 0)
{
    //
?><select ui-select2 class='input input-medium' id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>" name="field__<?=$field->path('_'); ?>" 
        ng-model="data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>" 
        <?php if ($field->nullvalue !== FALSE || $field->value_isset() || $field->defaultvalue) 
        { ?>ng-init='data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>=<?php 
            if ($field->value_isset()) 
            { 
                echo json_encode($field->value); 
            } 
            else if ($field->defaultvalue) 
            { 
                echo json_encode($field->defaultvalue); 
            } 
            else if ($field->nullvalue !== FALSE) 
            {
                echo 'null';
            }
            ?>'<?php
        } ?>><?php
    if (($field->value_isset() && $field->value === '') || !$field->value_isset()) 
    {
        ?><option value="">Please Select</option><?php
    }
    foreach ($field->values as $k => $option_value)
    {
        //@todo support keys and multiple languages for the label and optgroups
        ?><option value="<?php echo $option_value; ?>"<?php if (($field->raw_value == $option_value) || (!$field->value_isset() && $option_value === $field->defaultvalue)) { echo 'selected="selected"'; }?>><?php echo ucfirst($option_value); ?></option>
        <?php
    }
    ?></select><?php
} 
else
{
?><input class='input' ng-change="validate('<?=$field->path('_'); ?>')" type="<?php 
if ($field->storage === 'single')
{
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
    else if ($field->name == 'password') //@todo better way to decide this or just make a password template
    {
        echo 'password';
        $type = 'password';
    }   
    //default to text
    else
    {
        echo 'text';
        $type = 'text';
    }
}
//arrays and objects should have their own custom templates and are hidden if they end up using this template
else 
{
    echo 'hidden';
    $type = 'hidden';
}
?>" ng-model="data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>" id="<?=$form_id; ?>__field__<?=$field->path('_'); ?>" name="field__<?=$field->path('_'); ?>" value="<?php 
if ($field->value_isset()) 
{
        echo $field->value; 
}?>" <?php
if ($field->nullvalue !== FALSE || $field->defaultvalue !== NULL || $field->value_isset()) 
{ 
    ?>ng-init='data.<?=$field->get_model_name(); ?>.<?=$field->path('.'); ?>=<?php 
    if ($field->value_isset()) 
    {    
        if (is_int($field->value) || is_float($field->value) || is_numeric($field->value)) //@todo figure out why numeric value here ends up as a string
        {
            echo $field->value; 
        }
        else
        {
            echo json_encode($field->value);    
        }
        
    } 
    else if ($field->defaultvalue) 
    { 
        echo json_encode($field->defaultvalue); 
    }
    else if ($field->nullvalue !== FALSE) 
    {
        echo 'null';
    }
    ?>'<?php
} ?> autocomplete="off"<?php 
if ($field->storage === 'single')
{
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
            else if (is_array($rule) && isset($rule[0]) && $rule[0] === 'regex' && isset($rule[1][1]))
            {               
                echo ' ngPattern="'.str_replace('"',"&quot;",$rule[1][1]).'"';
            }
        }
    }
}
//check for required
if ($field->required || (is_array($field->validation) && in_array(array('not_empty'),$field->validation))) 
{ 
    echo ' required="required"'; 
} 

//check for readonly
if ($field->readonly && $field->value_isset()) 
{ 
    echo ' readonly="readonly"'; 
} 

?>/>
<?php

}
