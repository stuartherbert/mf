<?php

// ========================================================================
//
// components/Tasks/Tasks.funcs.php
//              Functions defined by the Tasks component
//
//              Part of the FV web application
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008 Stuart Herbert
//              All rights reserved
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-01-05   SLH     Created
// ========================================================================

function constraint_mustBeRunFromCommandLine()
{
	// we are only allowed to run from the command-line

        global $argv, $argc;

        if (!isset($argv) || !is_array($argv) || count($argv) == 0)
        {
        	throw new Exception;
        }
}

?>