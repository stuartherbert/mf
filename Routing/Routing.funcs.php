<?php

// ========================================================================
//
// Routing/Routing.funcs.php
//              Helper functions for dealing with routes
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
// 2009-05-19   SLH     Created
// ========================================================================

function require_app_routes($appModule)
{
        $routeFile = APP_TOPDIR . '/app/' . $appModule . '/' . $appModule . '.routes.php';
        App::$routes->addRoutesFile($routeFile);

        // require_once($routeFile);
}

?>
