<?php

// ========================================================================
//
// mf/mf.mainLoop.php
//              Contains the main processing for apps built using MF
//
//              Part of the Methodosity Framework for PHP
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-03-02   SLH     Created
// ========================================================================

try
{
        // convert the queryString into its individual components
        //
        // this call will create App::$user if one is required!!
        // (we could create App::$user before this call, which would be
        // a little cleaner, but why create a $user (which is expensive,
        // because it involves database access) if we sometimes do not
        // need one?
        //
        // one side-effect of this process is that both our API and
        // website share the same idea of what a user is.  This is probably
        // a good thing
        
        $route = App::$routes->matchUrl(App::$request->pathInfo);
}
catch (Routing_E_NoMatchingRoute $e)
{
        header('Status: 404');

        // for now, throw the exception
        throw $e;
}

// transfer control to the main loop
//
// TODO: we need to install the correct exception handler for
//       each of our mainLoop types, to ensure the exception is
//       sent back in the right format
//
// TODO: this switch statement goes away when PHP 5.3 comes out

switch ($route->mainLoop)
{
        case 'AnonApi':
                // AnonApi::installExceptionHandler();
                AnonApi::mainLoop($route);
                break;

        case 'Api':
                // Api::installExceptionHandler();
                Api::mainLoop($route);
                break;

        case 'WebApp':
        default:
                // WebApp::installExceptionHandler();
                WebApp::mainLoop($route);
                break;
}

?>