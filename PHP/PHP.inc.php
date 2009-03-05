<?php

// ========================================================================
//
// PHP/PHP.inc.php
//              Include file for the PHP component
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
// 2007-08-11   SLH     Created
// ========================================================================

// set who we are
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'PHP';

// include our files
require_once($componentDir . 'PHP.exceptions.php');
require_once($componentDir . 'PHP.funcs.php');
require_once($componentDir . 'PHP.classes.php');

// support for the user's chosen language
App::$languages->moduleSpeaks($componentName, $componentDir, 'en-us');

?>