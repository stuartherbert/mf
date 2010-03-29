<?php

// ========================================================================
//
// Theme/Theme.lang.en-us.php
//              US English language strings for the Theme component
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
// 2009-03-31   SLH     Created
// ========================================================================

App::$languages->addTranslationsForModule('Theme', 'en-us', array
(
        'E_NoLayoutSet'         => 'App has not set a layout; needs to call App::$response->page->setLayout() in the controller script',
        'E_NoSuchLayout'        => 'Layout "%s" not found',
));

?>