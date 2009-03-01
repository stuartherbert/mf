<?php

// ========================================================================
//
// Datastore/Datastore.funcs.php
//              Helper functions defined by the Datastore component
//
//              Part of the Modular Framework for PHP Applications
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
// 2008-05-22   SLH     Created
// ========================================================================

function constraint_mustBeDatastoreRecord($record)
{
	if (! $record instanceof Datastore_Record)
        {
        	throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

?>