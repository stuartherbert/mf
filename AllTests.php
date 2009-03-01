<?php

// ========================================================================
//
// AlTests.php
//              Script you can run to run all available unit tests
//
//              Part of the Modular Framework for PHP applications
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
// 2008-08-12   SLH     Added SQLite3 unit tests
// ========================================================================

$mf_libs_dir = dirname(__FILE__) . '/libs/';

require_once($mf_libs_dir . 'Routing/AllTests.php');
require_once($mf_libs_dir . 'Model/AllTests.php');
// require_once($mf_datastore_libs_dir . 'Datastore/AllTests.php');
// require_once($mf_datastore_libs_dir . 'DatastoreArray/AllTests.php');
// require_once($mf_datastore_libs_dir . 'DatastoreBehaviours/AllTests.php');
require_once($mf_libs_dir . 'DatastorePDO/AllTests.php');
require_once($mf_libs_dir . 'DatastoreSQL/AllTests.php');
require_once($mf_libs_dir . 'DatastoreSQLite3/AllTests.php');

?>