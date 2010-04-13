<?php

// ========================================================================
//
// App/App.inc.php
//              Include file for the App component
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
// 2008-09-09   SLH     Pipeline is now called App
// 2009-03-05   SLH     Now include the exceptions file
// ========================================================================

// who we are
$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;

// special case:
// App must initialise itself before we can go any further
MF_App::init();

// support for the user's chosen language
MF_App::$languages->moduleSpeaks('MF_App', 'en-us', $componentDir . '/App.lang.en-us.php');

?>