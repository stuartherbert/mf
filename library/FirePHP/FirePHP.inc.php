<?php

// ========================================================================
//
// FirePHP/FirePHP.inc.php
//              Include file for the FirePHP component
//
//              Part of the Methodosity Framework for PHP applications
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-07-14   SLH     Created
// ========================================================================

// ========================================================================
//
// NOTES
//
// This file is a common placeholder to wrap the include file for whichever
// specific version of FirePHP we are shipping at this time.
//
// The general idea is to hide any future file layout differences of
// future FirePHP releases ... without guaranteeing backwards-compatibility
// of the FirePHP functionality itself
//
// If you don't want debugging output, do the following:
//
//      App::$debug->setEnabled(false);
//
// We automatically disable debugging if UNIT_TEST is defined
//
// ========================================================================

require_once (dirname(__FILE__). '/FirePHPCore-0.3.1/lib/FirePHPCore/FirePHP.class.php');

?>