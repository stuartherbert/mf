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
// 2009-06-10   SLH     Switched to friendlier language string names
// 2009-06-10   SLH     Added User form verification error strings
// ========================================================================

App::$languages->addTranslationsForModule('User', 'en-us', array
(
        // field names, for use in forms
        'F_FirstName'                   => 'First name',
        'F_LastName'                    => 'Last name',
        'F_EmailAddress'                => 'Email address',
        'F_ConfirmEmailAddress'         => 'Confirm email address',
        'F_Password'                    => 'Password',
        'F_ConfirmPassword'             => 'Confirm password',
        'F_Address1'                    => 'Address 1',
        'F_Address2'                    => 'Address 2',
        'F_AddressCity'                 => 'Town or City',
        'F_AddressState'                => 'County or State',
        'F_AddressPostcode'             => 'Post code',
        'F_AddressCountry'              => 'Country',

        // errors, for use in forms
        'V_NoAddress1'                  => 'You must enter the first line of your address',
        'V_NoAddressCity'               => 'You must enter the city',
        'V_NoAddressState'              => 'You must enter your county / state',
        'V_NoAddressPostcode'           => 'You must enter your postcode / zip code',
        'V_NoAddressCountry'            => 'You must enter your country',
        'V_NoEmailAddress'              => 'You must enter your email address',
        'V_EmailAddressInvalid'         => 'You must enter a valid email address',
        'V_EmailAddressInUse'           => 'That email address is already in use; please use another one',
        'V_NoConfirmEmailAddress'       => 'You must confirm your email address',
        'V_EmailAddressesDifferent'     => 'The two email addresses are not the same',
        'V_NoFirstName'                 => 'You must enter your first name',
        'V_NoLastName'                  => 'You must enter your last name',
        'V_BlankPassword'               => 'You must enter a password',
        'V_WeakPassword'                => 'Your password is easy to guess or break',
        'V_PasswordsDifferent'          => 'The two passwords are not the same',
));

?>