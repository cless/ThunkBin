<?php
/*! \mainpage Minimalist php application framework
 *
 * \section intro_sec Introduction
 *
 * Frameless is a very php framework to develop applications using the Model-View-Controler
 * architecture.  It is not meant to compete with other large and established frameworks.
 * Instead it is more of a research project for the author to become familiar with MVC, OO-PHP,
 * git and doxygen.  That being said, it is released in the hope that someone will find a use for
 * it.  
 *
 * \section install_sec Installation
 * 
 * For the most basic functionality you only need to copy the /htdocs/ directory an create your own
 * controllers, views and models.  Please make sure you delete the example models, views and controllers.
 * Some features require additional libraries to be installed, these
 * libraries are not covered by the Frameless license. Please see COPYING for more information.
 * 
 * \subsection smarty Smarty Template Engine
 * Download the latest smarty and place it in /htdocs/library/smarty/
 *
 */


    // This proxy behaves much like include, but it does not search several dirs
    // and it does not throw warnings when the file does not exist
    function IncludeProxy($file)
    {
        $path = realpath(dirname(__FILE__) . '/' . $file);
        if(file_exists($path))
            return include($file);
    }

    function BootstrapAutoload($classname)
    {
        if(
              IncludeProxy('./controller/' . strtolower($classname) . '.php')   != 1 &&
              IncludeProxy('./library/' . strtolower($classname) . '.php')      != 1 &&
              IncludeProxy('./model/' . strtolower($classname) . '.php')        != 1
          );
    }
    
    // Prevents LFI, prevents loading models/views and helps decide when to 404
    function IsController($name)
    {
        return in_array($name . '.php', scandir('./controller'));
    }

    // Checks if the action is a valid public member function
    function IsAction($page, $name)
    {
        $list = array_map('strtolower', get_class_methods($page));
        return in_array(strtolower($name), $list);
    }

    // 404 not found loader
    function notfound()
    {
        $page = new error404();
        $page->Handle404();
    }
    
    // TODO: 404 handling has to be completely redone, it sucks!
    function main()
    {
        $get    = new Vector($_GET);
        $config = new IniFile('config.ini');

        // Set controller (or default)
        if($get->Exists('controller'))
        {
            $controller = $get->AsString('controller');
        }
        else
        {
            $controller = $config->GetVector()->AsString('defaultcontroller');
        }

        // If its not a valid controller page, terminate early
        if(!IsController($controller))
        {
            $controller = $config->GetVector()->AsString('errorcontroller');
            notfound();
            exit(0);
        }
        $page = new $controller($config->GetVector());

        // Set action (or default)
        if($get->Exists('action') && strtolower($get->AsString('action')) != 'defaultaction')
        {
            $action = $get->AsString('action');
        }
        else
        {
            $action = $page->DefaultAction();
        }

        
        // if its not a valid contcoller action terminate with 404
        if (!IsAction($page, $action))
        {
            unset($page);
            notfound();
            exit(0);
        }
        
        $page->$action();
    }
    
    spl_autoload_register('BootstrapAutoload');
    main();
?>
