<?php 
/*
 * Copyright 2010 Robert Hickman
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

class database 
{
/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Convert a mysql resource into an array
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    protected function sql_to_array($sql_result)
    {
        $array = array();

        if($sql_result != NULL)
        {
            while($row = mysql_fetch_assoc($sql_result))
            {
                array_push($array, $row);
            }
        }

        return $array;
    }

/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Variable argument function, takes an SQL query string
 * containing the `@v' character pair and substitutes
 * eatch occourance with the next avalable argument.
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    protected function query($query)
    {
    // split query
        $str_array = str_split($query);

    // Get arguments and create an arg counter
        $args = func_get_args();
        $arg = 1;

        $query = "";

        for($i = 0; $i < count($str_array); $i ++)
        {
            if($str_array[$i] == '@' &&
                $str_array[$i + 1] == 'v')
            {
                if($arg <= count($args) - 1)
                {
                    $query .= mysql_real_escape_string($args[$arg]);
                    $arg ++;
                    $i ++; // skip `v' charicter
                }
                else
                {
                    throw new query_error_exception("Too few arguments for query");
                }
            }
            else
                $query .= $str_array[$i];
        }

        $result = mysql_query($query);

        if($result === false)
            throw new query_error_exception();

        return $result;
    }
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Create an instance of a model
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function instance_model($model_name)
{
    $model_path = "app/models/$model_name.php";

// prevent re-inclusion
    if(file_exists($model_path) && !class_exists("mdl_$model_name"))
        include $model_path;

    $name_with_prefix = "mdl_$model_name";

    if(!class_exists($name_with_prefix))
        die("Error: model class '$model_name' does not exist");

    return new $name_with_prefix;
}

// exceptions
class query_error_exception extends exception { }
