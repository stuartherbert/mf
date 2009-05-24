<?php

// ========================================================================
//
// Events/Events.tests.php
//              PHPUnit tests for the Events component
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
// 2009-05-24   SLH     Created
// ========================================================================
//
// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');

class Test_Event_Triggered
{
        public function doSomething()
        {
                Events_Manager::triggerEvent('testEvent1', $this, array('a', 'b', 'c'));
        }

        public function doSomethingElse()
        {
                Events_Manager::triggerEvent('testEvent2', $this, array('e', 'f', 'g'));
        }
}

class Test_Event_ListenerObj
        implements Events_Listener
{
        public $eventsTriggered = 0;
        public $data            = array();

        public function listenToTestEvent1($source, $data)
        {
                $this->eventsTriggered++;
                $this->data = $data;
        }

        public function listenToTestEvent2($source, $data)
        {
                $this->eventsTriggered++;
                $this->data = $data;
        }
}

Testsuite_registerTests('Events_Tests');
class Events_Tests extends PHPUnit_Framework_TestCase
{
        public function testCanRegisterEvents()
        {
                // entry conditions
                $listeners = Events_Manager::getListeners();
                $this->assertEquals(0, count($listeners));

                // change state
                $listener = new Test_Event_ListenerObj;
                Events_Manager::listensToEvents($listener);

                // retest
                $listeners = Events_Manager::getListeners();
                $this->assertEquals(2, count($listeners));
        }

        public function testCanTriggerEvents()
        {
                $listener = new Test_Event_ListenerObj;
                Events_Manager::listensToEvents($listener);

                $obj = new Test_Event_Triggered();

                // entry conditions
                $listeners = Events_Manager::getListeners();
                $this->assertEquals(2, count($listeners));
                $this->assertEquals(0, count($listener->data));

                // change state
                $obj->doSomething();

                // retest
                $this->assertEquals(1, $listener->eventsTriggered);
                $this->assertEquals(array('a', 'b', 'c'), $listener->data);
        }

        public function testCanTriggerTwoEvents()
        {
                $listener = new Test_Event_ListenerObj;
                Events_Manager::listensToEvents($listener);

                $obj = new Test_Event_Triggered();

                // entry conditions
                $listeners = Events_Manager::getListeners();
                $this->assertEquals(2, count($listeners));
                $this->assertEquals(0, count($listener->data));

                // change state
                $obj->doSomething();
                $obj->doSomethingElse();

                // retest
                $this->assertEquals(2, $listener->eventsTriggered);
                $this->assertEquals(array('e', 'f', 'g'), $listener->data);
        }
}

?>