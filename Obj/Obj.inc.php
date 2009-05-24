<?php

// ========================================================================
//
// Obj/Obj.inc.php
//              Include file for the Obj component
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
// 2008-07-25	SLH	Added Core.funcs.php
// 2009-05-22   SLH     Added Core.exceptions.php
// 2009-05-24   SLH     Renamed to Obj
// ========================================================================

$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'Obj';

require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.funcs.php');
require_once($componentDir . $componentName . '.classes.php');

// Core has to be self-contained, because it is at the very top of the
// class hierarchy, and therefore cannot support MF's wider language
// features

?>
