<?php

// ========================================================================
//
// DatastoreSQLite3/DatastoreSQLite3.classes.php
//              Support for working with SQLite v3 databases
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
// 2008-08-12   SLH     Created
// ========================================================================

class DatastoreSQLite3_Connector extends DatastorePDO_Connector
{
	public function __construct($file)
        {
        	parent::__construct('sqlite:' . $file, null, null);
        }
}

?>
