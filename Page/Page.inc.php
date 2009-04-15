<?php

// ========================================================================
//
// Page/Page.inc.php
//              Include file for the Page module
//
//              Part of the Methodosity Framework for PHP
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
// 2009-04-15   SLH     Created
// ========================================================================

// where are we?
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Page';

// pull in our files
require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.classes.php');
// require_once($componentDir . $componentName . '.funcs.php');

// support for the user's chosen language
App::$languages->moduleSpeaks($componentName, $componentDir, 'en-us');

?>
