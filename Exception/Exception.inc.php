<?php

// ========================================================================
//
// Exception/Exception.inc.php
//              Include file for the Exception component
//
//              Part of the Modular Framework for PHP applications
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

$componentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;

require_once($componentDir . 'Exception.classes.php');

// support for the user's chosen language
// require_once($componentDir . 'Exceptions.lang.' . APP_LANG . '.php');
// $GLOBALS['oConfig']->addLanguageFor('Exceptions', $lang);

?>