<?php

// ========================================================================
// Step 1: Setup key global constants
//
// ------------------------------------------------------------------------

// APP_TOPDIR must always be defined by the caller.
//
// We deliberately do not try to set APP_TOPDIR ourselves.  The caller must
// define APP_TOPDIR to prove that their PHP script is one that is meant
// to be accessed deliberately from a web browser.

if (!defined('APP_TOPDIR'))
{
        throw new Exception('APP_TOPDIR not defined');
}

// ========================================================================
//
// Cheat: we define this here so that it exists for everyone
//
// Should really move this out to a separate PHP patches file
//
// ------------------------------------------------------------------------

if (!function_exists('lcfirst'))
{
        function lcfirst($string)
        {
                return strtolower(substr($string, 0, 1)) . substr($string, 1);
        }
}

function __mf_init_module($module)
{

        static $loadedModules = array();

        // have we loaded this module before?
        if (isset($loadedModules[$module]))
        {
                // yes we have - bail
                return;
        }

        $filename = APP_LIBDIR . '/MF/' . $module . '/_init/' . $module . '.inc.php';
        $loadedModules[$module] = $filename;

        require($filename);
}

function __mf_require_once($file)
{
        static $loadedFiles = array();
        if (isset($loadedFiles[$file]))
        {
                return;
        }

        $loadedFiles[$file] = $file;
        __mf_require($file);
}

function __mf_require($file)
{
        $filename = APP_LIBDIR . '/' . $file;
        require($filename);
}

// minimal bootstrap for things to work
__mf_init_module('Obj');
__mf_init_module('PHP');
__mf_init_module('Language');
//__mf_init_module('App');

// MF_App::$debug->timer->markEvent('Framework bootstrapped');

?>