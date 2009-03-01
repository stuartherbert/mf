<?php

// ========================================================================
//
// PHP/PHP.exceptions.php
//              Exceptions defined by the PHP component
//
//              Part of the Modular Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-08-11   SLH     Consolidated from individual files
// 2008-01-06   SLH     Stopped using constants for language strings
// ========================================================================

class PHP_E_NoSuchClass extends Exception_Technical
{
        function __construct ($className, Exception $oCause = null)
        {
                parent::__construct (
                        l('PHP', 'LANG_PHP_E_NOSUCHCLASS'),
                        array ($className),
                        $oCause
                );
        }
}

class PHP_E_NoSuchMethod extends Exception_Technical
{
        function __construct ($methodName, $className, Exception $oCause = null)
        {
                if (is_object($className))
                {
                	$className = get_class($className);
                }

                parent::__construct (
                        l('PHP', 'LANG_PHP_E_NOSUCHMETHOD'),
                        array ($methodName, $className),
                        $oCause
                );
        }
}

class PHP_E_ConstraintFailed extends Exception_Technical
{
	function __construct ($functionName, Exception $oCause = null)
        {
        	parent::__construct (
                        l('PHP', 'LANG_PHP_E_CONSTRAINTFAILED'),
                        array ($functionName),
                        $oCause
                );
        }
}

?>