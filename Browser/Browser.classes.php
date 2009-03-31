<?php

// ========================================================================
//
// Browser/Browser.classes.php
//              Classes to support working with the client-side device
//              (generically referred to as the browser)
//
//              Part of the Methodosity Framework for PHP
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-03-25   SLH     Created
// 2009-03-31   SLH     Added basic browser test for Firefox 2, 3.0, 3.5
// ========================================================================

class Browser_Manager
{
        public function __construct()
        {
                App::$browser = $this->determineBrowser();
        }

        /**
         * @return array detailing the browser
         */
        public function determineBrowser()
        {
                $browser = new Browser();

                // do we have a user agent at all to look at?
                if (!isset($_SERVER['HTTP_USER_AGENT']))
                {
                        $browser->setConsole();

                        return $browser;
                }

                // yes we do
                // work out what it is

                if (preg_match('|Firefox/([0-9.]+)|', $_SERVER['HTTP_USER_AGENT'], $matches))
                {
                        // Firefox, or something pretending to be Firefox
                        $browser->setFirefox($preg_match[1]);

                        return $browser;
                }

                // TODO: check for IE
                // TODO: check for Safari
                // TODO: provide a catchall for niche browsers

                return $browser;
        }
}

class Browser
{
        public $platform = null;
        public $name     = null;
        public $version  = null;

        const PLATFORM_DESKTOP = 'desktop';
        const PLATFORM_IPHONE  = 'iPhone';
        const PLATFORM_WINMO   = 'winmo';
        const PLATFORM_CONSOLE = 'term';

        /**
         * the "browser" is actually a console ... we are being executed
         * from the command line
         */
        public function setConsole()
        {
                $this->platform = Browser::PLATFORM_CONSOLE;
                $this->name     = 'terminal';
                $this->version  = '1.0';
        }

        public function setFirefox($version)
        {
                $this->platform = Browser::PLATFORM_DESKTOP;
                $this->name     = 'Firefox';

                // expand on the version string
                $versionDetails = explode('.', $version);

                // we don't care about minor details in general,
                // except when marketing have been screwing around
                // with logical version numbers :)
                if ($versionDetails[0] == 2)
                        $this->version  = 2;
                else if ($versionDetails[0] == 3)
                        $this->version = $versionDetails[0] . '.' . $versionDetails[1];
                else
                        $this->version = 'unknown';
        }
}

?>
