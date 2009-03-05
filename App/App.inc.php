<?php

// ========================================================================
//
// components/App/App.inc.php
//              Include file for the App component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-12-02   SLH     Created
// 2008-09-09   SLH     Pipeline is now called App
// ========================================================================

// who we are
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'App';

// load our files
//require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.funcs.php');
require_once($componentDir . $componentName . '.classes.php');

// special case:
// App must initialise itself before we can go any further
App::init();

// support for the user's chosen language
App::$languages->moduleSpeaks('App', $componentDir, 'en-us');

?>