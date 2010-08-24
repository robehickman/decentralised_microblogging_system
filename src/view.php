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

class view
{
    var $filename;

/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Load in template file
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    public function __CONSTRUCT($tplname, $path = 'app/views/')
    {
        $filename = $path . $tplname . '.php'; 

        if(!file_exists($filename))
            die("template $tplname.php does not exist");

        $this->filename = $filename;
    }

/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Display the template directily
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    public function parse($array = array())
    {
        extract($array);
        include $this->filename;
    }

/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Parse the template into a variable and return it
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    public function parse_to_variable($array = array())
    {
        extract($array);

        ob_start();
        include $this->filename;
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}

/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Create an instance of the view
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function instance_view($view_name, $path = 'app/views/')
{
    return new view($view_name, $path);
}

/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Display a template with one line of code, alows easy
 * display of templates within templates
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function display_template($name, $params)
{
    $content = new view($name);
    $content->parse($params);
}
