<?php

// ========================================================================
//
// Email/Email.funcs.php
//              Helper functions defined by the Email module
//
//              Part of the Methodosity Framework for PHP applications
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007 Stuart Herbert
//              All rights reserved
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-09-07   SLH     Created
// ========================================================================

// ========================================================================
// isEmailAddress()
//
// ------------------------------------------------------------------------

function constraint_mustBeEmailAddress ($address)
{
        if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $address))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

function constraint_emailAddressServerMustRespond($emailAddress)
{
        // based on code from the Zend Code Gallery
        // http://www.zend.com/zend/spotlight/ev12apr.php

        // step 1: work out the address of the remote email server

        list ($user, $emailDomain) = split('@', $emailAddress);

        $mxHost = array();
        if (getmxrr($emailDomain, $mxHost))
        {
                $emailDomain = $mxHost[0];
        }

        // step 2: connect to server
        $rSock = @fsockopen($emailDomain, 25, $errno, $errMsg, 10);
        if (!$rSock)
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }

        // step 3: check that the email server is responding
        if (!ereg("^220", fgets($rSock, 1024)))
        {
                fclose($rSock);
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

// ========================================================================
//
// ------------------------------------------------------------------------

function constraint_emailAddressMustExist($emailAddress)
{
        // based on code from the Zend Code Gallery
        // http://www.zend.com/zend/spotlight/ev12apr.php

        // step 1: work out the address of the remote email server

        list ($user, $emailDomain) = split('@', $emailAddress);

        $aMxHost = array();
        if (getmxrr($emailDomain, $aMxHost))
        {
                $emailDomain = $aMxHost[0];
        }

        // special case - example.com is never allowed to receive email
        if ($emailDomain == 'example.com')
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }

        // step 2: connect to server
        $rSock = @fsockopen($emailDomain, 25, $errno, $errMsg, 10);
        if (!$rSock)
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }

        // step 3: check that the email server is responding
        if (!ereg("^220", fgets($rSock, 1024)))
        {
                fclose($rSock);
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }

        // step 4: check if email address exists
        fputs($rSock, "HELO $HTTP_HOST\r\n");
        fgets($rSock, 1024);

        fputs($rSock, "MAIL FROM: <{$emailAddress}>\r\n");
        $from = fgets($rSock, 1024);

        fputs ($rSock, "RCPT TO: <{$emailAddress}>\r\n");
        $to = fgets ($rSock, 1024);

        fputs ($rSock, "QUIT\r\n");
        fclose($rSock);

        if (!ereg ("^250", $from) || !ereg ( "^250", $to))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

?>