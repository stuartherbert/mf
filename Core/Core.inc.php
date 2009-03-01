<?php

// ========================================================================
//
// Core/Core.inc.php
//              Include file for the Core component
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
// 2008-07-19   SLH     Created
// 2008-07-25	SLH	Added Core.funcs.php
// ========================================================================

$componentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;

require_once($componentDir . 'Core.funcs.php');
require_once($componentDir . 'Core.classes.php');

// support for the user's chosen language
// require_once($componentDir . 'Core.lang.' . APP_LANG . '.php');
// $GLOBALS['oConfig']->addLanguageFor('Core', $lang);

?>
