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
 * If the framework is not installed in the base directory of your domain (e.g example.com/subdir/)
 * then you should follow the instructions in /htdocs/.htaccess to get the mod_rewrite rules working
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

    function main()
    {
        $get    = new Vector($_GET);
        $config = new IniFile('config.ini');
        
        // First figure out if we need to load the default controller name
        if(!$get->Exists('controller'))
            $get->Set('controller', $config->GetVector('bootstrap')->AsString('defaultcontroller'));
        
        // Now figure out if our controller is a valid virtual, and replace it by the actual if it is
        if($config->GetVector('bootstrap.virtuals')->Exists($get->AsString('controller')))
            $controller = $config->GetVector('bootstrap.virtuals')->AsString($get->AsString('controller'));
        else
            $controller = $get->AsString('controller');

        // Check if our actual is a valid controller, die if it isn't
        if(!IsController($controller))
            throw new FramelessException('', ErrorCodes::E_404);
        $page = new $controller($config);
        
        // First verify if we need a default
        if($get->Exists('action'))
            $action = $get->AsString('action');
        else
            $action = 'default';

        // Now as the controller what function is responsible for this action
        $action = $page->ActionToFunction($action);
        if($action == false)
        {
            unset($pagfe);
            throw new FramelessException('', ErrorCodes::E_404);
        }
        
        $page->$action();
    }
    
    spl_autoload_register('BootstrapAutoload');
    try
    {
        main();
    }
    catch (Exception $e)
    {
        $error = new Error($e);
        $error->Handle();
    }
?>
