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

define('APP_TOPDIR',  realpath(dirname(__FILE__) . '/..'));
define('APP_LIBDIR',  APP_TOPDIR . '/library');
define('APP_TESTDIR', APP_TOPDIR . '/tests/library');

// step 2: setup the autoload functionality

function __app_require($classname, $fileToLoad = null)
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

spl_autoload_register('__app_require');

// step 3: load the MF include file
require_once(APP_LIBDIR . '/MF/mf.inc.php');
__mf_init_module('App');

// step 4: helpers for loading test files
function __mf_init_tests($module)
{
        static $loadedFiles = array();
        if (isset($loadedFiles[$module]))
        {
                return;
        }

        $filename = 'MF/' . $module . '/_init/' . $module . '.inc.php';
        $loadedFiles[$module] = $filename;
        __mf_require_tests($filename);
}

function __mf_require_tests($file)
{
        $filename = APP_TESTDIR . '/' . $file;
        require($filename);
}

?>