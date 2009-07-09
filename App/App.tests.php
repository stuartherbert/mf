<?php

// ========================================================================
//
// App/App.tests.php
//              Tests for the App component
//
//              Part of the Methodosity Framework for PHP Applications
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
// 2009-07-07   SLH     Created
// ========================================================================

// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');

Testsuite_registerTests('App_Conditions_Tests');
class App_Conditions_Tests extends PHPUnit_Framework_TestCase
{
	public function setup()
        {
        	$this->fixture = new App_Conditions();
        }

	public function testDefaultConditionIsLoggedOut()
	{
		$this->assertTrue ($this->fixture['loggedOut']);
		$this->assertFalse($this->fixture['loggedIn']);
	}

        public function testLoggingInUpdatesLoggedOutConditionToo()
        {
                // entry conditions
                $this->assertTrue ($this->fixture['loggedOut']);
                $this->assertFalse($this->fixture['loggedIn']);

                // make the change
                $this->fixture->loggedIn = true;

                // retest
                $this->assertFalse($this->fixture['loggedOut']);
                $this->assertTrue ($this->fixture['loggedIn']);

                // now, reset the fixture and try again, only this time
                // we will login by saying we're no longer logged out
                // (because someone will do this one day)

                $this->setup();

                // entry conditions
                $this->assertTrue ($this->fixture['loggedOut']);
                $this->assertFalse($this->fixture['loggedIn']);

                // make the change
                $this->fixture->loggedOut = false;

                // retest
                $this->assertFalse($this->fixture['loggedOut']);
                $this->assertTrue ($this->fixture['loggedIn']);
        }

        public function testLoggingOutUpdatesLoggedInConditionToo()
        {
                // put the fixture into the right initial state
                $this->fixture->loggedIn = true;
                
                // entry conditions
                $this->assertTrue ($this->fixture['loggedIn']);
                $this->assertFalse($this->fixture['loggedOut']);

                // make the change
                $this->fixture->loggedOut = true;

                // retest
                $this->assertFalse($this->fixture['loggedIn']);
                $this->assertTrue ($this->fixture['loggedOut']);

                // now, reset the fixture and try again, only this time
                // we will login by saying we're no longer logged out
                // (because someone will do this one day)

                $this->setup();

                // put the fixture into the right initial state
                $this->fixture->loggedIn = true;
                
                // entry conditions
                $this->assertTrue ($this->fixture['loggedIn']);
                $this->assertFalse($this->fixture['loggedOut']);

                // make the change
                $this->fixture->loggedIn = false;

                // retest
                $this->assertFalse($this->fixture['loggedIn']);
                $this->assertTrue ($this->fixture['loggedOut']);
        }
}

?>
