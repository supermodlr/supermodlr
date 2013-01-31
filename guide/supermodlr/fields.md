# Supermodlr Fields

## What are Fields?
* Represent a value, like a column in a database
* Can store a single value or multiple values
* Have a set of rules that define how the field value is stored, retrieved, and queried.
* Can be fully represented by an instance of the Supermodlr_Field Class
* Can Extend other fields

## Properties & Rules 

* **name** 
    * machine name of field (alpha-numeric)
    * type: single string
* **label**
    * Display name of field.
    * type: multilingual single string
* **description**
    * Display description of field.
    * type: multilingual single string
* **datatype**  
    * Defines the type of data
    * type: single string
    * values
        * string
        * int
        * float
        * timestamp
        * datetime
        * boolean 
        * relationship
        * binary
        * resource
        * object.   
            * datatype of object means that this field is related to a model and expects to embed the fields (based on storage value)
* **multilingual**
    * If True, this field value is stored keyed by language so any language can have a unique value.
    * type: single bool
* **charset**  
    * Defines the character set to use. 
    * type: single string 
    * conditions: only valid if datatype = string
    * default: UTF-8
    * values: See www.iana.org/assignments/character-sets/character-sets.xml
* **storage** 
    * Defines how the datatype value is stored in combination with datatype and multilingual.  
    * values
        * single
        * array
        * keyed_array 
* **required** 
    * If True, or evaluated as True, then the model will not pass validation or save without a value set for this field.
    * type: single boolean OR array of conditions
    * (not implemented yet) array indicates a conditional that determines TRUE or FALSE.   
    * format: 
        * array('{$field_key'=> '{$value}')
        * array('$callback'=> array('method1','method2')) - method1/method2 must be methods on the model class and are sent 1 param containing the value or field::NOT_SET and expect a boolean response
* **unique** 
    * indicates if the value of this field can not be the same as any other value of the same field in the same data set.  
    * type: single boolean
* **searchable** 
    * indicates if this field value should be available in text searches (solr, elastic search)
    * type: single boolean
    * conditions: only valid if datatype = 'string'
* **filterable** 
    * Indicates if this field value should be filterable in queries (meaning it is available for indexes)
    * type: single boolean
* **values**
    * Defines a static list of available values.  This field will not pass validation if a value is set that is not in this list, unless this list is NULL
    * type: array mixed OR NULL
* **filters**
    * Defines a list of php functions that should be run on any values before storage.
    * type: array string
    * conditions: must map to a valid php function or class::method call that accepts 1 parameter and returns 1 value
* **maxlength**
    * Defines a maxlength in bytes @todo needs more clarification
    * type: single int
* **defaultvalue** 
    * Defines the value that will be set for this field by default
    * type: mixed
* **nullvalue** 
    * If True and the $default_value is null then the value should be set to NULL if no other value is set.  if False and $default_value is null, than a default value will not be set for this field on an object 
    * type: single boolean
    * default: TRUE 
* **validation** 
    * array of validation rules
    * type: array of validation rules
    * @todo define format and options for validation rules
* **templates** 
    * Definds input & display templates 
    * format: array('input'=> 'input_template', 'display'=> 'display_template')
* **hidden** 
    * indicates if this field is hidden on forms or not
    * type: single boolean
    * default: FALSE
* **extends** 
    * relationship to field that this field extends.  When a FieldB extends FieldA, FieldB inherits all properties from FieldA except for those explicitly defined for FieldB 
    * type: single relationship
* **validtestvalue**  
    * a value that should be valid.  used for unit tests if there is no default
    * type: mixed
* **invalidtestvalues**  
    * a set of values that should fail validation. used for unit test validation
    * type: mixed
* **pk**
    * indicates that this field is the primary key for the model
    * type: single boolean
    * default: FALSE
* **access**  
    * (not implemented yet) set to array to control access to this field. 
    * type: array of actions => array of access tags required for each operation
    * format: array('create'=>array(),'read'=>array(),'update'=>array(),'delete'=>array(), 'query'=> array()).  admins are always allowed for all operations.
* **private**  
    * if true this field is only visible and modifiable by an admin or by the system
    * type: single boolean
* **model**  
    * stores the model pk/class name for which model this field belongs to, or null if it is a core/abstract field
    * type: single relationship
* **conditions**  
    * controls conditional display for input forms and display. @todo show lots of examples
    * type: array formated like a mongo query object
    * format: array('input'=> array('field1'=> 'value'), 'display'=> array()).  input/display array format is same as mongodb query syntax (supports: $and, $or, $ne, $not, $gt, $lt, $gte, $lte, $regex)
* **readonly**  
    * if true, after set for the first time, it can not be changed unless by admin via admin interface
    * type: single boolean
* **source**  
    * array of models to look in for a valid entry.  
    * type: array
    * conditions: only valid if dataype == 'relationship' 
    * format: array('models'=>array('model1','model2'), 'where'=> array(/* additional where clauses used when looking for valid values*/),'name_column' = 'name')
* **stored**
    * Indicates if this field should actually be stored. If FALSE. this field can be used and displayed like all other fields, but is never stored in any database
    * type: single boolean
    * default: TRUE
* **owner**   
    * If True, this indicates that this field relates the entire object to a user owner.  Ownership is part of the model and field level access controls
    * type: single boolean
    * conditions: Only valid on a PK field on an instance of Supermodlruser or a relationship field that relates to an instance of Supermodlruser or a model that has the field called "useraccesstags"
