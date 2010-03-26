<?php

// ========================================================================
//
// User/User.funcs.php
//              Helper functions for working with Users
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
// 2007-08-06   SLH     Created
// 2007-08-06   SLH     Added constraint_mustBeUser()
// ========================================================================

function constraint_mustBeUser($user)
{
	if (!is_object($user))
        {
        	throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }

        if (!$user instanceof User)
        {
        	throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

?>