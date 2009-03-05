<?php

// ========================================================================
//
// Routing/Routing.exceptions.php
//              Exceptions defined by the Routing component
//
//              Part of the Methodosity Framework for PHP applications
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
// 2007-11-19   SLH     Created
// 2008-01-06   SLH     Stopped using constants for language strings
// 2008-01-06   SLH     Added Routing_E_Exception_Technical base class
// ========================================================================

class Routing_E_Exception_Technical extends Exception_Technical
{

}

class Routing_E_NoSuchRoute extends Routing_E_Exception_Technical
{
        function __construct ($routeName, Exception $oCause = null)
        {
                parent::__construct (
                        l('Routing', 'LANG_ROUTING_E_NOSUCHROUTE'),
                        array ($routeName),
                        $oCause
                );
        }
}

class Routing_E_MissingParameters extends Routing_E_Exception_Technical
{
	function __construct ($noOfProblems, $missingParams, Exception $oCause = null)
        {
        	parent::__construct (
                        l('Routing', 'LANG_ROUTING_E_MISSINGPARAMETERS'),
                        array ($noOfProblems, $missingParams),
                        $oCause
                );
        }
}

class Routing_E_NoMatchingRoute extends Routing_E_Exception_Technical
{
	function __construct ($url, Exception $oCause = null)
        {
        	parent::__construct (
                        l('Routing', 'LANG_ROUTING_E_NOMATCHINGROUTE'),
                        array ($url),
                        $oCause
                );
        }
}

?>