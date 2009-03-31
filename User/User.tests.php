<?php

// ========================================================================
//
// Users/Users.tests.php
//              Unit tests for the User component
//
//              Part of the Modular Framework for PHP applications
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
// 2007-07-24   SLH     Created
// ========================================================================

class User_Tests extends PHPUnit_Framework_TestCase
{
        public function setup()
        {
                loadTestDatabase();
        }

        public function testTableKnowsAboutSpecialUserNewUser()
        {
                // we have a fake user called 'newuser', which is used
                // by the entry.register module when a new user is
                // registered

                $oRecord = User_Table::getEntryPointRecordFor('tests', 'newuser');

                $this->assertTrue($oRecord instanceof User_Record);
                $this->assertEquals($oRecord->uid, null);
        }
}

?>