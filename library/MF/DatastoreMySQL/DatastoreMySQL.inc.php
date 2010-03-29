<?php

// ========================================================================
//
// DatastoreMySQL/DatastoreMySQL.inc.php
//              Include file for the DatastoreMySQL component
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
// 2007-08-11   SLH     Created
// 2009-02-28   SLH     Separated out from the DatastoreSQL module
// ========================================================================

$componentDir  = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$componentName = 'DatastoreMySQL';

//require_once($componentDir . $componentName . '.exceptions.php');
require_once($componentDir . $componentName . '.classes.php');
// require_once($componentDir . $componentName . '.funcs.php');

// support for the user's chosen language
//require_once($componentDir . $componentName . '.lang.' . $GLOBALS['APP_LANG'] . '.php');

?>