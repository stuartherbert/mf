<?php

// ========================================================================
//
// Theme/Theme.inc.php
//              Include file for the Theme component
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
// 2009-03-30   SLH     Created
// ========================================================================

// pull in our dependencies
//__mf_require_once('PHP');

// who we are
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Theme';

// load our files
require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.funcs.php');
require_once($componentDir . $componentName . '.classes.php');

// support for the user's chosen language
App::$languages->moduleSpeaks($componentName, $componentDir, 'en-us');

?>