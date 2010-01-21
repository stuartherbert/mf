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
// Copyright    (c) 2008-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-10-17   SLH     Created
// 2009-03-31   SLH     Moved user creation out into the main loop
// 2009-04-01   SLH     Setup the default titles before the main loop
// ========================================================================

class WebApp
{
        // cannot be instantiated
        private function __construct()
        {

        }

        public static function preMainLoop($route)
        {
                // load the theme
                self::setTheme();

                // set the default page title
                App::$response->page->setDefaultTitlesEtc();
        }

        public static function postMainLoop()
        {
                // do nothing
        }
        
        public static function setTheme()
        {
                // if the user is anonymous, they get the default theme
                if (!App::$user->authenticated)
                {
                        App::$themes->setTheme(App::$config['APP_THEME']);
                        return;
                }

                // if the user model doesn't support user-selected themes,
                // they get the default theme
                if (!App::$user->supportsThemePref)
                {
                        App::$themes->setTheme(App::$config['APP_THEME']);
                        return;
                }

                // if the user has not selected a theme, they get the
                // default theme (spot the trend here ... :)
                if (!isset(App::$user->theme) || empty(App::$user->theme))
                {
                        App::$themes->setTheme(App::$config['APP_THEME']);
                        return;
                }

                // if we get here, we are trying to set the user's preferred
                // theme
                //
                // there is always the chance that the user's preference is
                // no longer valid, so we need to be careful
                try
                {
                        App::$themes->setTheme(App::$user->theme);
                }
                catch (Exception $e)
                {
                        App::$themes->setTheme(App::$config['APP_THEME']);
                }
        }
}

?>