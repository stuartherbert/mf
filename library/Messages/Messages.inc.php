<?php

// ========================================================================
//
// Messages/Messages.inc.php
//              Include file for the Messages component
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
// 2007-12-02   SLH     Created
// ========================================================================

// who we are
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Messages';

// load our files
//require_once($componentDir . $componentName . '.exceptions.php');
//require_once($componentDir . $componentName . '.funcs.php');
require_once($componentDir . $componentName . '.classes.php');

// support for the user's chosen language
// App::$languages->moduleSpeaks($componentName, $componentDir, 'en-us');

?>