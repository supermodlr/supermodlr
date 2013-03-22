<?php

    $controller->js('lib/angularjs/angular.min.js','headertags'); 
    $controller->js('lib/angularui/build/angular-ui.min.js','headertags');  
    $controller->js('lib/angularjs/angular-resource.min.js','headertags');  
    $controller->js('lib/select2/select2.js','headertags');
    $controller->js('lib/crypto-js/md5.js','headertags');   
    $controller->css('/modules/supermodlr/lib/select2/select2.css');    
    $controller->css('/modules/supermodlr/lib/jqueryui/css/base/minified/jquery-ui.min.css');   
    $controller->css('/modules/supermodlr/lib/jqueryui/css/ui-lightness/jquery.ui.theme.css');

    //capture the text below
ob_start();
?>

function <?=$form_id ?>Ctrl($scope, $resource, $http) {
    $scope.form_id = '<?=$form_id ?>';
    $scope.model_name = '<?=$model->get_name() ?>';
    $scope.server = $resource('<?=$controller->api_path()?><?=$model->get_name() ?>/<?=$action ?>/<?=$model->pk_value() ?>');
    $scope.$http = $http;
    $scope.data = {};
    $scope.data[$scope.model_name] = <?php if ($model->to_array() === array()) { echo '{}'; } else { echo json_encode($model->export()); } ?>;
    $scope.submit = form_submit;
    $scope.serverError = {};
    $scope.modal_form = false;

    $scope.invalid = form_invalid;
    
    $scope.validate = form_validate;

    $scope.fbl = fbl;   

    $scope.ready = function() {
        if (typeof window[$scope.form_id+'_readyfunctions'] != 'undefined') {
            for (var i = 0; i < window[$scope.form_id+'_readyfunctions'].length; i++) {
                window[$scope.form_id+'_readyfunctions'][i]();
            }    
        }
    }
    
}

window.<?=$form_id ?>_angular_modules = ['ngResource','ui','<?=$form_id ?>'];
<?=$form_id ?> = angular.module('<?=$form_id ?>', window.<?=$form_id ?>_angular_modules);


<?=$form_id ?>.directive('runReady', function() {
  return function($scope, element, attrs) {
    $scope.ready();
  };
});


function form_submit() {
    $scope = this;
    //submit form
    save_response = $scope.server.save($scope.data[$scope.model_name],function() {
        //if save worked
        if (save_response.status == true) {
            $scope.$emit('saved',save_response);
            if (!$scope.modal_form) {
                //redirect user to previous page (@todo or close modal window)
                <?php
                if (isset($controller->form_redirect))
                {
?>                  window.location.href = '<?=$controller->form_redirect ?>';<?php
                } 
                else
                {
?>                  window.location.href = document.referrer;<?php
                } ?>

            }
        //if save failed
        } else {
            $scope.invalid(save_response);
        }
    },
    function() {
        $scope.invalid(save_response);
    });

}

function form_invalid(save_response) {
    $scope = this;  
    //get form object
    var form = $scope[$scope.form_id+'Form'];

    //invalidate form
    form.$setValidity('server',false);

    //invalidate all invalid fields
    if (save_response && typeof save_response.data != 'undefined') {
        for (field in save_response.messages) {
            //if this message is attached to a specific field
            if (typeof $scope.data[$scope.model_name][field] != 'undefined') {

            }
        }
    }

}

function form_validate(field_name) {
    
    $scope = this;  
    $scope.$http.post(getAPIPath()+'/'+$scope.model_name+'/validate_field/*/'+field_name,$scope.data[$scope.model_name]).
        success(function(data, status, headers, config) {
            var form = $scope[$scope.form_id+'Form'];
            delete($scope.serverError[field_name]);             
            form['field__'+field_name].$setValidity('server',true);
        }).
        error(function(data, status, headers, config) {
            var form = $scope[$scope.form_id+'Form'];
            $scope.serverError[field_name] = data.message;      
            form['field__'+field_name].$setValidity('server',false);
        });
}

function fbl(o)
{
    console.log(o);
}

function getModel() {
    return document.location.pathname.split('/')[2];
}

function getModelId() {
    return document.location.pathname.split('/')[4];
}

function getAPIPath() {
    return '/supermodlr/api';
}
<?php
$controller_js = ob_get_contents(); ob_end_clean();

$controller->js($controller_js,'headerinline');