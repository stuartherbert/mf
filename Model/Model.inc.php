<?php

// ========================================================================
//
// Model/Model.inc.php
//              Include file for the Model component
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
// 2008-07-28   SLH     Added Model.funcs.php
// ========================================================================

// load our dependencies
__mf_require_once('PHP');

// where are we?
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Model';

// pull in our files
require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.classes.php');
require_once($componentDir . $componentName . '.funcs.php');

// support for the user's chosen language
App::$languages->moduleSpeaks($componentName, $componentDir, 'en-us');

?>