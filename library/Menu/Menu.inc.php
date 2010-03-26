<?php

// ========================================================================
//
// Menu/Menu.inc.php
//              Component to manage the main menu, and any sub menus,
//              that need to be displayed
//
//              Part of the Methodosity Framework for PHP applications
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
// 2007-08-07   SLH     Created
// 2009-04-16   SLH     Fixed license in header
// ========================================================================


// load our dependencies
// __mf_require_once('PHP');

// where are we?
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Menu';

// pull in our files
require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.classes.php');
// require_once($componentDir . $componentName . '.funcs.php');

// support for the user's chosen language
App::$languages->moduleSpeaks($componentName, $componentDir, 'en-us');

?>
