<?php

// ========================================================================
//
// PHP/PHP.lang.en-us.php
//              US English language strings for the PHP component
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
// 2007-08-11   SLH     Created
// 2008-01-06   SLH     Stopped using constants for language strings
// 2008-02-11   SLH     Foreign languages now go in $APP_CONFIG
// 2009-03-01   SLH     Foreign languages now loaded through App
// ========================================================================

App::$languages->addTranslationsForModule('PHP', 'en-us', array
(
        'E_ConstraintFailed'   => "Constraint '%s' failed",
        'E_NoSuchClass'        => "Class '%s' is not defined",
        'E_NoSuchMethod'       => "Class '%2\$s' does not have a method called '%1\$s'",
));

?>