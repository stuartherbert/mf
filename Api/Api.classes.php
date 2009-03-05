<?php

// ========================================================================
//
// Api/Api.classes.php
//              Support for an application that handles API requests
//              instead of browser requests
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
// 2008-08-13   SLH     Created empty file
// 2008-10-17   SLH     Added empty ApiAuthenticator class
// ========================================================================

class ApiAuthenticator extends UserAuthenticator
{
        public function newUser (AppRequest $oRequest, Datastore $oUserDB)
        {
        	// TODO
        }
}

AppUser::authenticateUsing(new ApiAuthenticator);

?>