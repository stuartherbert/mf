<?php

// ========================================================================
//
// mf.inc.php
//              Main include file for the Methodosity Framework
//
//              Part of the Methodosity Framework for PHP Applications
//              http://blog.stuartherbert.com/php/mf/
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
// 2009-02-12   SLH     Created from previously separate include files
// 2009-03-01   SLH     Made App the global place holder for everything else
// 2009-03-18   SLH     Added support for loading mock objects for unit
//                      testing
// 2009-05-24   SLH     Added lcfirst() as a temporary workaround
// 2009-07-09	SLH	Stage 4 is now the generic bootstrap stage
// 2009-07-15	SLH	Added Firebug support to bootstrap stage
// 2009-07-26   SLH     Added debug timing info
// ========================================================================

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

// where are the app-specific modules to be found?
define('APP_LIBDIR', APP_TOPDIR . '/app');

// MF_TOPDIR should be defined by the caller
//
// If MF_TOPDIR is not defined, we assume that the Methodosity Framework is
// installed in a sub directory underneath APP_TOPDIR
//
// In general, we strongly recommend against having multiple apps share
// a single copy of MF on the same server at the same time, as this leaves
// each app vulnerable to being broken when new versions of MF are released

if (!defined('MF_TOPDIR'))
{
	define('MF_TOPDIR', APP_TOPDIR . '/mf');
}

// for now, the folder holding the modules is the same as MF_TOPDIR
// it has been different before, and might be different again
define('MF_LIBDIR', MF_TOPDIR);

// ========================================================================
// Step 2: Autoloader for classes
//
// After much experimentation with large sets of include files, I've
// decided that the way to go is to take advantage of PHP's class
// autoloader support.

function __mf_autoload($classname)
{
        // step 1: work out the name of the module holding the class
        $classPrefixPos = strpos($classname, '_');
        if ($classPrefixPos > 0)
        {
                $classPrefix    = substr($classname, 0, $classPrefixPos);
        }
        else
        {
                $classPrefix = $classname;
        }

        __mf_include_once($classPrefix);
}

function __app_autoload($classname)
{
        // step 1: work out the name of the module holding the class
        $classPrefixPos = strpos($classname, '_');
        if ($classPrefixPos > 0)
        {
                $classPrefix    = substr($classname, 0, $classPrefixPos);
        }
        else
        {
                $classPrefix = $classname;
        }

        app_include_once($classPrefix);        
}

function __test_autoload($classname)
{
        // step 1: work out the name of the module holding the class
        $classPrefixPos = strpos($classname, '_');
        if ($classPrefixPos > 0)
        {
                $classPrefix    = substr($classname, 0, $classPrefixPos);
        }
        else
        {
                $classPrefix = $classname;
        }

        __test_require_once($classPrefix);
}

// stack the autoloaders
//
// non-framework code gets the priority, so that developers can completely
// replace MF modules if they so choose
//
// it is slower, but that's the price of trying to Do The Right Thing

if (defined('UNIT_TEST'))
        spl_autoload_register('__test_require_once');
        
spl_autoload_register('__app_autoload');
spl_autoload_register('__mf_autoload');

// ========================================================================
//
// Step 3: define the MF module loader functions
//
// These helper functions save time when chosing to explicitly load a
// module from MF.
//
// You are free to call these functions from outside MF too, if you wish.
//
// ------------------------------------------------------------------------

function app_include_once($moduleName)
{
        // step 1: determine the possible path to the include file
        $include_file = APP_LIBDIR  . '/' . $moduleName . '/' . $moduleName . '.inc.php';

        // step 2: include the file if it exists
        if (file_exists($include_file))
        {
                include_once($include_file);
                return;
        }
}

function app_require_once($moduleName)
{
        // step 1: determine the possible path to the include file
        $include_file = APP_LIBDIR  . '/' . $moduleName . '/' . $moduleName . '.inc.php';

        // step 2: include the file if it exists
        if (file_exists($include_file))
        {
                require_once($include_file);
                return;
        }
}

function __mf_include_once($moduleName)
{
        // step 1: determine the possible path to the include file
        $include_file = MF_LIBDIR  . '/' . $moduleName . '/' . $moduleName . '.inc.php';

        // step 2: include the file if it exists
        if (file_exists($include_file))
        {
                include_once($include_file);
                return;
        }
}

function __mf_require_once($moduleName)
{
        // step 1: determine the possible path to the include file
        $include_file = MF_LIBDIR  . '/' . $moduleName . '/' . $moduleName . '.inc.php';

        // step 2: include the file if it exists
        if (file_exists($include_file))
        {
                require_once($include_file);
                return;
        }
}

function __test_require_once($moduleName)
{
        if (!defined(TEST_DIRS))
                return;
                
        foreach (TEST_DIRS as $testDir)
        {
                $include_file = $testDir . '/' . $moduleName . '.inc.php';

                if (file_exists($include_file))
                {
                        require_once($include_file);
                        return;
                }
        }

        // if we get to here, there is no special mock object
        __mf_require_once($moduleName);
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

// ========================================================================
//
// Step 4: bootstrap the framework
//
// ------------------------------------------------------------------------

__mf_require_once('FirePHP');
__mf_require_once('Language');
__mf_require_once('App');

App::$debug->timer->markEvent('Framework bootstrapped');

?>