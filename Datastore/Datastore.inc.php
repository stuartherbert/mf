<?php

// ========================================================================
//
// Datastore/Datastore.inc.php
//              Include file for the Datastore component
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
// 2008-05-22   SLH     Added Datastore.funcs.php
// ========================================================================

// load our dependencies
__mf_require_once('Model');

// who we are
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Datastore';

// load our files
require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.classes.php');
require_once($componentDir . $componentName . '.funcs.php');

// support for different languages
App::$languages->moduleSpeaks($componentName, $componentDir, 'en-us');

?>