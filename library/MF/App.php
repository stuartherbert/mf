<?php

/**
 * Methodosity Framework
 *
 * LICENSE
 *
 * Copyright (c) 2010 Stuart Herbert
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   MF
 * @package    MF_App
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

class MF_App
{
        /**
         *
         * @var MF_App_Request
         */
        public static $request           = null;

        /**
         *
         * @var MF_App_Response
         */
        public static $response          = null;

        /**
         * @var MF_User_Manager
         */
        public static $users             = null;

        /**
         *
         * @var MF_User
         */
        public static $user              = null;

        /**
         *
         * @var MF_Browser_Manager
         */
        public static $browsers          = null;

        /**
         *
         * @var MF_Browser
         */
        public static $browser           = null;
        /**
         *
         * @var MF_Theme_Manager
         */
        public static $themes            = null;

        /**
         * @var MF_Theme_BaseTheme
         */
        public static $theme             = null;

        /**
         *
         * @var MF_Language_Manager
         */
        public static $languages         = null;

        /**
         *
         * @var MF_Routing_Manager
         */
        public static $routes            = null;

        /**
         *
         * @var MF_Page_Manager
         */
        public static $pages             = null;

        /**
         *
         * @var array
         */
        public static $config            = array();

        /**
         *
         * @var MF_App_Conditions
         */
        public static $conditions        = array();

        /**
         * @var MF_Debug_Manager
         */

        public static $debug             = array();

        // cannot be instantiated
	private function __construct()
        {

        }

        /**
         * this is where we put the things absolutely required to allow
         * other framework modules to load successfully
         */
        public static function initInitial()
        {
		// load the general environment first
                // the order matters!
                self::$languages  = new MF_Language_Manager();
        }

        /**
         * setup the things that every app has
         *
         * it is up to the mainLoop() of the different types of app
         * to setup the rest (user, page, and theme)
         */

        public static function init()
        {
                // self::$browsers   = new MF_Browser_Manager();
                // self::$users      = new MF_User_Manager();
                // self::$routes     = new MF_Routing_Manager();
                // self::$pages      = new MF_Page_Manager();
                // self::$themes     = new MF_Theme_Manager();
                // self::$debug      = new MF_Debug_Manager();

                // disable debugging if we are unit testing
                // if (defined('UNIT_TEST') && UNIT_TEST)
                //{
                //        self::$debug->setEnabled(false);
                //}

		// with the general environment loaded, we can now load
		// the modules that are app-specific
                self::$request    = new MF_App_Request();
                self::$response   = new MF_App_Response();
                self::$conditions = new MF_App_Conditions();
        }
}

MF_App::initInitial();

?>
