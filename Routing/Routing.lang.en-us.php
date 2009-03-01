<?php

// ========================================================================
//
// Routing/Routing.lang.en-us.php
//              US English language strings for the Routing component
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
// 2007-11-19   SLH     Created
// 2008-01-06   SLH     Stopped using constants for language strings
// 2008-02-11   SLH     Foreign languages now go in $APP_CONFIG
// ========================================================================

App::$languages->addTranslationsForModule('Routing', 'en-us', array
(
        'LANG_ROUTING_E_MISSINGPARAMETERS'      => "%s parameter(s) missing: %s",
        'LANG_ROUTING_E_NOMATCHINGROUTE'        => "No route found to match url '%s'",
        'LANG_ROUTING_E_NOSUCHROUTE'            => "Route '%s' is not defined",
));

?>