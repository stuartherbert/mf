<?php

// ========================================================================
//
// App/App.lang.en-us.php
//              US English language strings for the App component
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
// 2008-01-03   SLH     Created
// 2008-01-06   SLH     Stopped using constants for language strings
// 2008-02-11   SLH     Foreign languages now go in $APP_CONFIG
// 2008-10-18   SLH     Aded LANG_E_APP_AUTHENTICATOR_REQUIRED
// 2009-03-01   SLH     Languages are now loaded through App
// 2009-03-02   SLH     Added E_INTERNAL_SERVER_ERROR
// ========================================================================

App::$languages->addTranslationsForModule('App', 'en-us', array
(
        // Exceptions
        'E_INTERNAL_SERVER_ERROR'                    => 'An unexpected error occurred',

        // Messages
        'LANG_APP_MESSAGES_ERROR_INSTRUCTIONS'       => 'What you just tried to do didn\'t work.  Here\'s what you need to do differently:',
));

?>