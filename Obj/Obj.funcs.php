<?php

// ========================================================================
//
// Obj/Obj.funcs.php
//              Helper functions for all purposes
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-07-25   SLH     Created
// 2009-05-24   SLH     Added helper function for mixins
// 2009-05-25   SLH     Obj_MixinDefinitions renamed Obj_MixinsManager
// 2009-06-01   SLH     Updated __mf_extend() to reflect improved API
// 2009-06-04   SLH     Added constraint_mustBeValidMixin()
// 2009-08-24   SLH     Added constraint_mustBeObject()
// ========================================================================

function debug_vardump($file, $line, $function, $title, $var)
{
	echo "--- var_dump: $function: $title ---\n";
	echo basename($file) . "@$line\n";
	echo "--- data ---\n";
	var_dump($var);
	echo "--- end of var_dump ---\n";
}

function __mf_extend($classname, $extensionClass)
{
        Obj_MixinsManager::extend($classname)->withClass($extensionClass);
}

function constraint_mustBeValidMixin($obj)
{
        if (!is_object($obj))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__, null);
        }

        if (!$obj instanceof Obj_Mixin)
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__, null);
        }
}

function constraint_mustBeObject($obj)
{
        if (!is_object($obj))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__, null);
        }
}

?>
