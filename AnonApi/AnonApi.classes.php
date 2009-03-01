<?php

// ========================================================================
//
// AnonApi/AnonApi.classes.php
//              Support for an application that handles API requests
//              instead of browser requests, and that never authenticates
//              users
//
//              Part of the Modular Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-10-17   SLH     Created
// ========================================================================

class AnonApi
{
        // cannot be instantiated
        private function __construct()
        {

        }

        public static function mainLoop()
        {
                // we do not need to setup a user, because this is an
                // anonymous API

                // convert the queryString into its individual components
                $route = App::$routes->matchUrl(App::$request->pathInfo);

                // create the controller
                $controller = new $route['routeToClass'];

                // work out which theme we need

                // call the controller
                $method = $route['routeToMethod'];
                $controller->$method($route['params']);

                // call the theme to finish
        }
}

?>