<?php

// ========================================================================
//
// Testsuite/tasks/UnitTest.task.php
//              Run a specific unit test
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

// $argv[2] contains the module we want to run tests for

if (!isset($argv[2]) || strlen(trim($argv[2])) == 0)
{
        echo "*** error: no module to unit test specified\n";
        exit(255);
}

if (!is_dir(APP_TOPDIR . '/' . $argv[2]))
{
        echo "*** error: module " . $argv[2] . "does not exist\n";
        exit(255);
}

// if we get here, we are going to run PHPUnit in the specified directory
system("cd " . escapeshellcmd(APP_TOPDIR . '/' . $argv[2]) . "; phpunit AllTests");

?>
