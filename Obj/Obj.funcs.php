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
// Copyright    (c) 2008-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-07-25   SLH     Created
// 2009-05-24   SLH     Added helper function for mixins
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
        Obj_MixinDefinitions::addMixin($extensionClass)
                              ->toClass($classname);
}

?>