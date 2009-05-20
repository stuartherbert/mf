<?php

// ========================================================================
//
// User/User.classes.php
//              General classes defined by the User components
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
// 2008-10-17   SLH     Added this header
// 2008-10-17   SLH     Added UserAuthenticator class
// 2008-10-26   SLH     Added underscores to separate out library names
// 2009-03-25   SLH     Removed hungarian notation
// 2009-03-30   SLH     Created User_Engine class
// 2009-03-31   SLH     Renamed User_Engine to User_Manager
// 2009-03-31   SLH     Added User_Authenticator_Anon
// 2009-03-31   SLH     Added User_Authenticator_ApiUser
// 2009-03-31   SLH     Added User_Authenticator_WebUser
// 2009-04-16   SLH     Conditions have been moved up to App from Routing
// 2009-05-20   SLH     Updated to work with changes to Model
// ========================================================================

// ========================================================================
// Support for remembering a user via a cookie

class User_Cookie
{
        public $id            = null;
        public $signature     = null;

        protected $cookieName = null;
        protected $secret     = null;

        function __construct($cookieName, $zecret)
        {
                $this->cookieName = $cookieName;
                $this->secret     = $secret;
        }

        function isReturningUser()
        {
                if (!isset($_COOKIE[$this->cookieName]))
                        return false;

                $this->decodeCookie();

                if (!isset($this->id))
                        return false;

                return true;
        }

        function authenticateUser(User $user)
        {
                $sig = $this->createSignature($user);

                if ($sig === $this->signature)
                {
                        return true;
                }

                return false;
        }

        function rememberUser(User $user)
        {
                setcookie($this->cookieName, $this->createPayload($user));
                //var_dump($this->createPayload($a_oUser));
        }

        function forgetUser()
        {
                setcookie($this->cookieName, '');
        }

        function createPayload(User $user)
        {
                return $user->id . ':' . $this->createSignature($user);
        }

        function createSignature(User_Record $user)
        {
                return md5($user->password . ':' . $this->secret);
        }

        function decodeCookie()
        {
                if (!isset($_COOKIE[$this->cookieName]))
                {
                        return;
                }

                $parts = explode(':', $_COOKIE[$this->cookieName]);

                $this->id        = $parts[0];
                $this->signature = $parts[1];
        }
}

class User_Manager
{
        /**
         * A list of the different forms of authentication this app
         * supports, and the class to call to perform the authentication
         * 
         * @var array
         */
        protected $authenticationMethods = array();

        public function __construct()
        {
                // register a suitable set of defaults
                // the developer can override these in his config files
                $this->addAuthenticator(User::AUTHTYPE_ANON,    'User_Authenticator_Anon');
                $this->addAuthenticator(User::AUTHTYPE_APIUSER, 'User_Authenticator_Api');
                $this->addAuthenticator(User::AUTHTYPE_WEBUSER, 'User_Authenticator_WebUser');
                // $this->addAuthenticator(User::AUTHTYPE_OAUTH,   'User_Authenticator_OAuth');
                // $this->addAuthenticator(User::AUTHTYPE_FACEBOOK_PLATFORM, 'User_Authenticator_FacebookPlatform');
                // $this->addAuthenticator(User::AUTHTYPE_FACEBOOK_CONNECT,  'User_Authenticator_FacebookConnect');
        }

        public function addAuthenticator($method, $class)
        {
                $this->authenticationMethods[$method] = $class;
        }

        public function authenticateUser($authMap = array())
        {
                App::$user = new User();

                // attempt the authentication methods in the order given
                // the first one that succeeds is the one we use

                foreach ($authMap as $method)
                {
                        if (isset($this->authenticationMethods[$method]))
                        {
                                $authenticatorClass = $this->authenticationMethods[$method];
                                $authenticator = new $authenticatorClass();
                                if ($authenticator->authenticateUser(App::$user))
                                {
                                        App::$user->authType = $method;
                                        return true;
                                }
                        }
                }

                // if we get here, we have not authenticated the user
                // we explicitly call the anonymous user authenticator,
                // in case it does any specific setup at all
                $authenticatorClass = $this->authenticationMethods[User::AUTHTYPE_ANON];
                $authenticator      = new $authenticatorClass;
                $authenticator->authenticateUser(App::$user);
                App::$user->authType = User::AUTHTYPE_ANON;

                // all done
        }
}

class User_Authenticator_Anon
{
        public function authenticateUser(User $user)
        {
                // there is currently nothing to do
                return true;
        }
}

class User_Authenticator_ApiUser
{
        public function authenticateUser(User $user)
        {
                // TODO: make this work
                return false;
        }
}

class User_Authenticator_WebUser
{
        public function authenticateUser(User $user)
        {
                $userCookie = new User_Cookie(App::$config['APP_SHORT_NAME'], App::$config['APP_SECRET_KEY']);

                // step 1: do we have a
                if ($userCookie->isReturningUser())
                {
                        try
                        {
                                $userFromCookie->retrieve(App::$config['DB'], $userCookie->id);
                                if ($userCookie->authenticateUser($user))
                                {
                                        $user->setFields($userFromCookie->getData());
                                        
                                        // tell the routing engine that the user
                                        // is logged in
                                        App::$conditions->loggedin = true;
                                        
                                        return true;
                                }
                        }
                        catch (Datastore_E_RetrieveFailed $e)
                        {
                                // user does not exist in the database
                                // nuke the cookie
                                $userCookie->forgetUser();
                        }
                }

                return false;
        }
}

?>