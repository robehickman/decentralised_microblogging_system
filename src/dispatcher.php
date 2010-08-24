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

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Framework dispatcher, dispatches to classes and methods
* in the app/controllers directory based on the request URL.
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function dispatcher()
    {
    // Parse the URL paramiters
        $params = make_params();

    // Check paramiters
        if(isset($params[0]))
            $controller_class = $params[0];
        else
            $controller_class = DEFAULT_CONTROLLER; 

        if(isset($params[1]))
            $controller       = $params[1];
        else
            $controller = "index"; 

    // import the controller file
        $path = "app/controllers/$controller_class.php";

        if(file_exists($path))
            include $path;

    // import helper file
        $hlp_path = "app/helpers/$controller_class.php";

        if(file_exists($hlp_path))
            include $hlp_path;

        $name_with_prefix = "ctrl_" . $controller_class;

    // Check the controller class exits
        if(!class_exists($name_with_prefix))
            throw new exception("Controller class '$controller_class' does not exist\n");

    // Create an instance of the controller class
        $instance = new $name_with_prefix();

        if(!is_subclass_of($instance, "controller_base"))
            throw new exception("Controllers must extend 'controller_base'\n");

        $instance->params = $params; // pass paramiters through

    // Check that the controller exists
        if(!method_exists($instance, $controller))
            throw new exception("Error: Controller '$controller_class'".
                " does not define method: '$controller'\n");

        $instance->$controller();

        $instance->display_outer();
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Convert the application request URL into an array. Discards
 * any leading directories alowing the code to run regardless
 * of its location in the server file tree.
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function make_params()
    {
        $remove_path = get_app_root();

        $request_url = strtolower($_SERVER["REQUEST_URI"]);

    // Discard the trailing forward slash if there is one
        if($request_url[strlen($request_url) - 1] == "/")
            $request_url = substr($request_url, 0, strlen($request_url) - 1);

    // remove any leading directory names
        $remove_path = str_replace("/", "\\/", $remove_path);
        $request_url = preg_replace("/$remove_path/", "", $request_url, 1);

        $params = explode('/', $request_url); 

    // descard empty first element
        array_shift($params);

        return $params;
    }
