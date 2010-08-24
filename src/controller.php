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

class controller_base
{
    var $params = "";
    var $outer_template = null;
    var $outer_template_params = array();

/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Load an outer template from the theme dir
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function load_outer_template($template_name)
    {
        $this->outer_template = instance_view($template_name, 'theme/');
    }
    
/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Set the paramiter array for the outer template
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function set_template_paramiters($tpl_params)
    {
        $this->outer_template_params = $tpl_params;
    }

/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Display the outer template, eather to the browser or
 * to a returned variable
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function display_outer($v = false)
    {
        if($v == false)
        {
            if($this->outer_template != null)
                $this->outer_template->parse($this->outer_template_params);
        }
        else
        {
            if($this->outer_template != null)
                return $this->outer_template->parse_to_variable($this->outer_template_params);
        }
    }
}
