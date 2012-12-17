<?php

class mongodbQuery2js {

    public $commands = array('$or','$nor','$and');
    public $operators = array('$in','$nin','$gt','$gte','$lt','$lte','$mod','$all','$exists','$ne','$size','$type','$regex','$elemMatch','$not');
    public $var_prefix = '';

    public function __construct(array $mongo_query)
    {
            $this->query = $mongo_query;
    }

    public function set_var_prefix($prefix) {
            $this->var_prefix = $prefix;
    }

    public function parse($query = NULL, $key = NULL)
    {
        $js = array();
        if ($query === NULL)
        {
                $query = $this->query;
        }
        foreach ($query as $k => $v)
        {
                if ($k === '$or')
                {
                        $ors = array();
                        foreach ($v as $or_v)
                        {
                                        $ors[] = $this->parse($or_v,$key);
                        }
                        $js[] = ' ( '.implode(' || ',$ors).' ) ';
                }
                else if ($k === '$nor')
                {
                        $nors = array();
                        foreach ($v as $nor_v)
                        {
                                        $nors[] = $this->parse($nor_v,$key);
                        }
                        $js[] = ' !( '.implode(' || ',$nors).' ) ';
                }
                else if ($k === '$and')
                {
                        $ands = array();
                        foreach ($v as $and_v)
                        {
                                        $ands[] = $this->parse($and_v,$key);
                        }
                        $js[] = ' ( '.implode(' && ',$ands).' ) ';
                }
                else if ($k === '$not')
                {
                                $js[] = '!'.$this->parse($v,$key);
                }
                else if (in_array($k,$this->operators))
                {
                        if ($k === '$gt')
                        {
                                $condition = $this->parse_condition($v);
                                $js[] = ' ( typeof '.$this->var_prefix.$key.' != \'undefined\' && '.$this->var_prefix.$key.' > '.$condition.' ) ';
                        }
                        else if ($k === '$gte')
                        {
                                $condition = $this->parse_condition($v);
                                $js[] = ' ( typeof '.$this->var_prefix.$key.' != \'undefined\' && '.$this->var_prefix.$key.' >= '.$condition.' ) ';
                        }
                        else if ($k === '$lt')
                        {
                                $condition = $this->parse_condition($v);
                                $js[] = ' ( typeof '.$this->var_prefix.$key.' != \'undefined\' && '.$this->var_prefix.$key.' < '.$condition.' ) ';
                        }
                        else if ($k === '$lte')
                        {
                                $condition = $this->parse_condition($v);
                                $js[] = ' ( typeof '.$this->var_prefix.$key.' != \'undefined\' && '.$this->var_prefix.$key.' <= '.$condition.' ) ';
                        }
                        else if ($k === '$exists')
                        {
                                if ($v === TRUE || $v === 1)
                                {
                                                $js[] = ' ( typeof '.$this->var_prefix.$k.' != \'undefined\') ';
                                }
                                else
                                {
                                        $js[] = ' ( typeof '.$this->var_prefix.$k.' == \'undefined\') ';
                                }

                        }
                        else if ($k === '$ne')
                        {
                                $condition = $this->parse_condition($v);
                                $js[] = ' ( typeof '.$this->var_prefix.$k.' != \'undefined\' && '.$this->var_prefix.$k.' != '.$condition.' ) ';
                        }
                        else if ($k === '$regex')
                        {
                                //@todo test regex to make sure it's valid and won't cause a js syntax error
                                $js[] = ' ( typeof '.$this->var_prefix.$k.' != \'undefined\' && ('.$v.').test('.$this->var_prefix.$k.');) ';
                        }

                        //@todo $type

                        //@todo $size

                        //@todo $mod

                        //@todo $all

                        //@todo $in

                        //@todo $nin

                        //@todo $elemMatch

                }
                //this must be a field key
                else
                {
                        //if the value is a non-empty array
                        if (is_array($v) && count($v) > 0)
                        {
                                        $js[] = $this->parse($v,$k);
                        }

                        //value is not an array, must be a raw value
                        else
                        {
                                $condition = $this->parse_condition($v);
                                $js[] = ' ( typeof '.$this->var_prefix.$k.' != \'undefined\' && '.$this->var_prefix.$k.' == '.$condition.' ) ';           

                        }


                }
        }
        return ' ( '.implode(' && ',$js).' ) ';
    }

    public function is_command(string $key)
    {

    }

    public function is_operator(string $key)
    {

    }

    public function parse_condition($v)
    {
        $condition = '';
                if (is_int($v))
                {
                        $condition = ' '.$v.' ';
                }
                else if ($v === NULL)
                {
                        $condition = ' null ';
                }
                else if (is_bool($v))
                {
                        if ($v === TRUE)
                        {
                                $condition = ' true ';
                        }
                        else
                        {
                                $condition = ' false ';
                        }
                }
                else if (is_array($v) || is_object($v))
                {
                        $condition = ' '.json_encode($v).' ';
                }
                else if (is_string($v))
                {
                        $condition = ' \''.$this->prepare_string($v).'\' ';
                }

                return $condition;
    }


    //escapes all non-escaped single quotes
    public function prepare_string($string)
    {
        return preg_replace("#(?<!\\\\)(?:\\\\{2})*\K'#", "\'", $string);
    }

}
