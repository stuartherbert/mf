<?php

// ========================================================================
//
// Routing/Routing.inc.php
//              Include file for the Routing component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-11-19   SLH     Created
// 2009-05-19   SLH     Added support for routing-related functions
// ========================================================================

// pull in our dependencies
__mf_require_once('PHP');

// who we are
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Routing';

// load our files
require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.classes.php');
require_once($componentDir . $componentName . '.funcs.php');

// support for the user's chosen language
App::$languages->moduleSpeaks($componentName, $componentDir, 'en-us');

?>