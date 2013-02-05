<?php


trait Supermodlr_Trait_Supertrait {

    /**
     * get_traits gets all trait class names assigned directly to the sent class or that are on traits that are assigned to the sent class
     * 
     * @param string $class class name on which to search
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
    public static function get_traits($class = NULL) {

        // If no class was sent
        if ($class === NULL)
        {
            //get class name called
            $class = get_called_class();            
        }

        //if traits were already loaded, return them
        /*$traits = $class::$__tcfg['traits'];
        if ($traits !== NULL) 
        {
            return $traits;
        }*/

        //get all traits assigned directly to this class
        $traits = class_uses($class);  // only returns traits on the sent class, not inherited from parents or others included

        $all_traits = array_keys($traits);

        foreach ($traits as $trait)
        {
            $all_traits = array_merge($class::get_traits($trait),$all_traits);
        }

        //$class::$__tcfg['traits'] = $all_traits;
        return $all_traits;
   
    }

    /**
     * get_all_traits returns an array of trait class names assigned to this model, all parent models, and all traits on parent models
     * 
     * @access public
     * @static
     *
     * @return mixed Value.
     */
    public static function get_all_traits()
    {


    }
    
    public static function get_trait_name($trait = NULL)
    {
        if ($trait === NULL)
        {
            $trait = __CLASS__;
        }

        return strtolower(preg_replace('/^Trait_/','',$trait));
    }    
}
