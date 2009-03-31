<?php

// ========================================================================
//
// User/User.inc.php
//              The include file for the User component
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
// 2007-07-20   SLH     Created
// 2007-08-06   SLH     Added loading of Users.funcs.php
// ========================================================================

$componentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;

require_once($componentDir . 'User.funcs.php');
require_once($componentDir . 'User.models.php');
require_once($componentDir . 'User.classes.php');

App::$languages->moduleSpeaks('User', $componentDir, 'en-us');

?>