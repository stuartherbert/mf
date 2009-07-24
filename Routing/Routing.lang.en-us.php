<?php

// ========================================================================
//
// Routing/Routing.lang.en-us.php
//              US English language strings for the Routing component
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
// 2008-02-11   SLH     Foreign languages now go in $APP_CONFIG
// 2009-07-16   SLH     Added E_NoLinkText
// ========================================================================

App::$languages->addTranslationsForModule('Routing', 'en-us', array
(
        'E_MissingParameters'   => "%s parameter(s) missing: %s",
        'E_NoLinkText'          => "Route '%s' has no default translation set",
        'E_NoMatchingRoute'     => "No route found to match url '%s'",
        'E_NoSuchRoute'         => "Route '%s' is not defined",

));

?>