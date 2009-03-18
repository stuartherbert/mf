<?php

// ========================================================================
//
// Testsuite/Testsuite.inc.php
//              Include file for the Testsuite library
//
//              Part of the Methodosity Framework for PHP applications
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
// 2009-03-18   SLH     Now load in the required functions too
// ========================================================================

require_once ('PHPUnit/Framework/TestSuite.php');
require_once ('PHPUnit/Framework/TestCase.php');
require_once ('PHPUnit/TextUI/TestRunner.php');

$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Testsuite';

require_once($componentDir . $componentName . '.classes.php');
require_once($componentDir . $componentName . '.funcs.php');

?>