<?php

// ========================================================================
//
// PHP/PHP.funcs.php
//              Helper functions defined by the PHP component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-08-11   SLH     Consolidated from individual files
// ========================================================================

// ========================================================================
//
//
// ------------------------------------------------------------------------

function constraint_mustBeString ($a_szIn)
{
        if (!is_string($a_szIn))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBeInteger ($in)
{
        if (!is_numeric($in))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }

        if ((int)$in != $in)
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBeGreaterThan($number, $min)
{
        if (!is_numeric($number))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }

        if ($number <= $min)
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBeLessThan($number, $max)
{
        if (!is_numeric($number))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }

        if ($number >= $max)
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBePositive($number)
{
        if ($number < 0)
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBeUrl($url)
{
        // do nothing for now
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBeArray(&$aIn)
{
        if (!is_array($aIn))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustNotBeEmptyArray(&$aIn)
{
        if (!is_array($aIn))
        {
                // Technically, if it isn't an array, then the constraint
                // holds.  We may have to change this if lots of people
                // fall foul of this.

                return;
        }

        if (count($aIn) == 0)
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function ipAddress_to_int($ipAddress)
{
        $parts = explode('.', $ipAddress);

        return ($parts[0] * 16777216)
               + ($parts[1] * 65536)
               + ($parts[2] * 256)
               + $parts[3];
}

// ========================================================================
// ------------------------------------------------------------------------

function int_to_ipAddress($ipAddress)
{
        $aFactors = array (16777216, 65536, 256, 1);
        $index    = 0;
        $aParts   = array();

        foreach ($aFactors as $factor)
        {
                $aParts[$index] = (int) ($ipAddress / $factor);
                $ipAddress      = $ipAddress - ($aParts[$index] * $factor);

                $index++;
        }

        return implode('.', $aParts);
}

// ========================================================================
// ------------------------------------------------------------------------

function add_to_query_string($query, $additions)
{
        constraint_mustBeString($query);
        constraint_mustBeArray($additions);

        $append = false;
        if (strlen($query) > 0)
        {
                $append = true;
        }

        $return = '';

        foreach ($additions as $key => $value)
        {
                if ($append)
                {
                        $return .= '&';
                }
                $append = true;

                $return .= urlencode($key) . '=' . urlencode($value);
        }

        return $query . $return;
}

// ========================================================================
// ------------------------------------------------------------------------

function strip_query_string_of($aKeys)
{
        $aGET = $_GET;

        foreach ($aKeys as $key)
        {
                if (isset($aGET[$key]))
                        unset($aGET[$key]);
        }

        return add_to_query_string('', $aGET);
}

?>