<?php

/**
 * Methodosity Framework
 *
 * LICENSE
 *
 * Copyright (c) 2010 Stuart Herbert
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   MF
 * @package    MF_Debug
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

function constraint_mustBeString ($a_szIn)
{
        if (!is_string($a_szIn))
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBeInteger ($in)
{
        if (!is_numeric($in))
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
        }

        if ((int)$in != $in)
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBeGreaterThan($number, $min)
{
        if (!is_numeric($number))
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
        }

        if ($number <= $min)
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBeLessThan($number, $max)
{
        if (!is_numeric($number))
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
        }

        if ($number >= $max)
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

function constraint_mustBeObject($obj)
{
        if (!is_object($obj))
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__, null);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_mustBePositive($number)
{
        if ($number < 0)
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
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
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
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
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
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