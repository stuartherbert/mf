<?php

class MF_App_Conditions_Tests extends PHPUnit_Framework_TestCase
{
	public function setup()
        {
        	$this->fixture = new MF_App_Conditions();
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
