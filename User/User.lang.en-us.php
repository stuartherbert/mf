<?php

// ========================================================================
//
// User/User.lang.en-us.php
//              US English language strings for the User component
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
// 2007-08-15   SLH     Created
// 2008-01-06   SLH     Stopped using constants for language strings
// 2009-03-25   SLH     Now uses App::$languages
// ========================================================================

App::$languages->addTranslationsForModule('User', 'en-us', array
(
        // field names, for use in forms
        'LANG_USERS_FIRSTNAME'                  => 'First name',
        'LANG_USERS_LASTNAME'                   => 'Last name',
        'LANG_USERS_EMAILADDRESS'               => 'Email address',
        'LANG_USERS_CONFIRMEMAILADDRESS'        => 'Confirm email address',
        'LANG_USERS_PASSWORD'                   => 'Password',
        'LANG_USERS_CONFIRMPASSWORD'            => 'Confirm password',
        'LANG_USERS_ADDRESS1'                   => 'Address 1',
        'LANG_USERS_ADDRESS2'                   => 'Address 2',
        'LANG_USERS_ADDRESSCITY'                => 'Town or City',
        'LANG_USERS_ADDRESSCOUNTY'              => 'County or State',
        'LANG_USERS_ADDRESSPOSTCODE'            => 'Post code',
        'LANG_USERS_ADDRESSCOUNTRY'             => 'Country',
));

?>