<?php

// ========================================================================
//
// Api/Api.inc.php
//              Include file for the Api component
//
//              Part of the Modular Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-10-17   SLH     Created
// ========================================================================

$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Api';

// require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.classes.php');
// require_once($componentDir . $componentName . '.funcs.php');

// support for the user's chosen language
// require_once($componentDir . $componentName . '.lang.' . $GLOBALS['APP_LANG'] . '.php');

?>