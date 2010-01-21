<?php

// ========================================================================
//
// AnonApi/AnonApi.classes.php
//              Support for an application that handles API requests
//              instead of browser requests, and that never authenticates
//              users
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-10-17   SLH     Created
// 2009-03-02   SLH     Added mainLoop()
// 2009-03-24   SLH     Routes now go to page scripts inside modules
// ========================================================================

class AnonApi
{
        // cannot be instantiated
        private function __construct()
        {

        }

        public static function mainLoop($route)
        {
                // we do not need to setup a user, because this is an
                // anonymous API

                // set the default format, if required
                if (!isset($route->matchedParams[':format']))
                {
                        // set a default format
                        $route->matchedParams[':format'] = 'xml';
                }

                // load the right theme
                switch($route->matchedParams[':format'])
                {
                        case 'json':
                                App::$theme = new AnonApi_Theme_Json();
                                break;

                        case 'php':
                                App::$theme = new AnonApi_Theme_PHP();
                                break;
                        
                        case 'xml':
                        default:
                                App::$theme = new AnonApi_Theme_Xml();
                }

                // call the controller
                try
                {
                        $page = APP_TOPDIR . '/app/' . $route->routeToMethod
                                . '/pages/' . $route->routeToPage . '.page.php';
                        
                        require_once($page);
                }
                catch (Exception_Process $e)
                {
                        // we pass the exception on
                        throw $e;
                }
                catch (Exception $e)
                {
                        // we have an error that was not expected
                        // we will throw a generic internal server error
                        // at this point

                        var_dump($e);
                        throw new App_E_InternalServerError($e);
                }

                // call the theme to finish
        }
}

class AnonApi_Theme_Json
{

}

class AnonApi_Theme_PHP
{

}

class AnonApi_Theme_Xml
{

}

?>