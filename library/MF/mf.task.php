<?php

// ========================================================================
//
// mf/mf.task.php
//              Contains the main processing for tasks built using MF
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
// 2009-03-12   SLH     Created
// ========================================================================

if (!isset($argv[1]) || strlen(trim($argv[1])) == 0)
{
        echo "*** usage: php task.php <module>::<task>\n";
        exit (255);
}

$__mf_argv = explode('::', $argv[1]);
$__mf_taskModule = $__mf_argv[0];
$__mf_taskFile   = $__mf_argv[1];

$__mf_taskFilenames = array
(
        APP_TOPDIR . '/app/' . $__mf_taskModule . '/tasks/' . $__mf_taskFile . '.task.php',
        MF_TOPDIR  . '/' . $__mf_taskModule . '/tasks/' . $__mf_taskFile . '.task.php'
);

$__mf_notFound = true;
foreach ($__mf_taskFilenames as $__mf_taskFilename)
{
        if ($__mf_notFound && file_exists($__mf_taskFilename))
        {
                include_once($__mf_taskFilename);
                $__mf_notFound = false;
        }
}

// if we get here, the task is not to be found where we expect it

if ($__mf_notFound)
{
        echo '*** error: task ' . $__mf_taskModule . '::' . $__mf_taskFile . " not found\n";
        exit (254);
}
?>