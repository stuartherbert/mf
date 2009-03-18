<?php

// ========================================================================
//
// Testsuite/Testsuite.funcs.php
//              Functions defined by the Testsuite module
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

function Testsuite_findAllTests($dir)
{
        $return = array();
        for ($dh = dir($dir); $file = $dh->read(); $dh !== null)
        {
                if ($file == '.' || $file == '..')
                        continue;

                $filename = $dir . '/' . $file;

                if (is_dir($filename))
                {
                        $return = array_merge($return, Testsuite_findAllTests($filename));
                }
                else if (is_file($filename) && strpos($filename, '.tests.') > 0)
                {
                        $return[] = $filename;
                }
        }

        return $return;
}

function Testsuite_loadAllTests($dir)
{
        $testScripts = Testsuite_findAllTests($dir);
        var_dump($testScripts);
        
        foreach ($testScripts as $testScript)
        {
                require_once($testScript);
        }
}

function Testsuite_registerTests($name)
{
        AllTests::addTestsuite($name);
}

?>