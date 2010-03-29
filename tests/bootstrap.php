<?php

// ========================================================================
//
// _private/common.inc.php
//              Common include file for the app
//
//              Part of the Commute app
//              http://commuteapp.com/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2010 Stuart Herbert
//              All rights reserved
//
// ========================================================================

// step 1: setup some paths that we need to make our code
//         position-independant
//
// APP_TOPDIR is the path to the top folder of this app
// APP_LIBDIR is the path to where all the library code can be found

define('APP_TOPDIR', realpath(dirname(__FILE__) . '/..'));
define('APP_LIBDIR', APP_TOPDIR . '/library');

// step 2: setup the autoload functionality

function _app_require($classname, $fileToLoad = null)
{
        if (class_exists($classname) || interface_exists($classname))
        {
                return false;
        }

        if ($fileToLoad === null)
        {
                $fileToLoad = APP_LIBDIR . '/' . str_replace('_', '/', $classname . '.php');
        }

        require($fileToLoad);
}

spl_autoload_register('_app_require');

?>