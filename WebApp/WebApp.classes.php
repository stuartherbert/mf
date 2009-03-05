<?php

// ========================================================================
//
// WebApp/WebApp.classes.php
//              Classes defined by the WebApp module
//
//              Part of the Methodosity Framework for PHP
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
// 2008-10-17   SLH     Created
// ========================================================================

class WebApp_UserAuthenticator extends User_Authenticator
{
        public function newUser(App_Request $oRequest, Datastore $oDB)
        {
                $oUserCookie = new UserCookie(APP_SHORT_NAME, COOKIE_SECRET);

                try
                {
                        if ($oUserCookie->isReturningUser())
                        {
                                $oUser = $oUserTable->retrieve($oDB, $oUserCookie->id);
                                if ($oUser)
                                {
                                        if ($oUserCookie->authenticateUser($oUser))
                                        {
                                                // the cookie is valid
                                                $oUser->authenticated = true;
                                        }
                                }
                        }
                }
                catch (Datastore_E_RetrieveFailed $e)
                {
                        // do nothing
                }

                // make sure we have a user that we can use!
                if (!isset($oUser) || $oUser === false)
                {
                        // FIXME: this needs fixing
                        $oUser = new User_Record();
                }

                // our user is loaded
                return $oUser;

                // all done
        }
}

App_User::authenticateUsing(new WebApp_UserAuthenticator);

?>