<?php

// ========================================================================
//
// Testsuite/tasks/AllUnitTests.task.php
//              Run all the unit tests we can find
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
// 2009-03-18   SLH     Created
// ========================================================================

// load our helper code
__mf_require_once('Testsuite');

$testScripts = Testsuite_findAllTests(APP_TOPDIR);

foreach ($testScripts as $testScript)
{
        $test = basename($testScript);
        $dir  = dirname($testScript);

        echo "************************************************************\n";
        echo "Executing test $test\n";
        echo "************************************************************\n";

        // we have to run everything from a shell (as a separate process)
        // to ensure each unit test can load whatever mock objects it prefers
        system ("cd " . escapeshellcmd(realpath($dir)) . "; phpunit AllTests $test");

        echo "\n";
}

?>
