<?php

// ========================================================================
//
// Language/Language.inc.php
//              Include file for the Language component
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
// 2009-07-08   SLH     Broken out from the App component
// ========================================================================

// who we are
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Language';

// load our files
// require_once($componentDir . $componentName . '.exceptions.php');
// require_once($componentDir . $componentName . '.funcs.php');
require_once($componentDir . $componentName . '.classes.php');

?>