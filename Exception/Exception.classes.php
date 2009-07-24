<?php

// ========================================================================
//
// Exception/Exception.classes.php
//              Base exceptions for use throughout the application
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
// 2007-08-11   SLH     Consolidated from separate files
// 2009-03-02   SLH     Added support for setting HTTP response codes
//                      in the exceptions
// 2009-03-02   SLH     Added support for setting a unique ID for
//                      Process exceptions
// 2009-03-02   SLH     Improved the message output by the exception
// 2009-06-10   SLH     Use the function provided by App for translating
//                      messages
// ========================================================================

class Exception_Enterprise extends Exception
{
        /**
         * holds the original exception if we are throwing a new one
         */

        protected $oCause = null;

        /**
         * holds the list of parameters to explain this exception
         */

        protected $aParams = null;

        /**
         * constructor
         */

        public function __construct ($errorCode, $formatString, $aParams, Exception $oCause = null)
        {
                $message = app_expandFormatString($formatString, $aParams);

                parent::__construct($message, $errorCode);

                $this->oCause    = $oCause;
                $this->aParams   = $aParams;
        }

        /**
         * return the exception that caused this exception
         */

        public function getCause()
        {
                return $this->oCause;
        }

        /**
         * was this exception caused by another one?
         */

        public function hasCause()
        {
                if ($this->oCause != null)
                        return true;

                return false;
        }

        public function wasCausedBy($cause)
        {
        	if (!$this->hasCause())
                {
                	return false;
                }

                if ($this->oCause instanceof $cause)
                {
                	return true;
                }

                return $this->oCause->wasCausedBy($cause);
        }

        public function getIterator ()
        {
                return new Exception_Iterator($this);
        }

        public function getParams()
        {
                return $this->aParams;
        }
}

class Exception_Iterator implements Iterator
{
        protected $oStart = null;

        private $oCurrent = null;
        private $level    = 0;

        public function __construct(Exception_Enterprise $oException)
        {
                $this->oStart = $oException;
                $this->rewind();
        }

        // ---------------------------------------------------------------
        // Iterator support
        // ---------------------------------------------------------------

        public function rewind()
        {
                $this->oCurrent = $this->oStart;
                $this->level    = 0;
        }

        public function current()
        {
                return $this->oCurrent;
        }

        public function key()
        {
                return $this->level;
        }

        public function next()
        {
                // we can go no further if we're already off the end
                // of this list

                if ($this->oCurrent === null)
                {
                        return false;
                }

                // if we are at the end of the list, time to fall off
                // the end
                //
                // the last item in the list can be one of two types
                // of Exception:
                //
                // a) A generic PHP 5 Exception object, or
                // b) An Arafel2 Exception that has no cause data

                if ((!($this->oCurrent instanceof Exception_Enterprise)) ||
                    (!$this->oCurrent->hasCause()))
                {
                        $this->oCurrent = null;
                        $this->level++;
                        return false;
                }

                // if we get here, then we have not yet reached the end
                // of the list

                $this->oCurrent = $this->oCurrent->getCause();
                $this->level++;

                return true;
        }

        public function valid()
        {
                return ($this->oCurrent !== null);
        }
}

// ========================================================================
// Process exceptions are thrown when business logic fails
// ------------------------------------------------------------------------

class Exception_Process extends Exception_Enterprise
{
        /**
         *
         * @var int the HTTP code to return to the browser or calling HTTP
         *          client
         */
        public $httpCode = 500;

        public function __construct ($httpCode, $errorCode, $formatString, $aParams, Exception $oCause = null)
        {
                parent::__construct($errorCode, $formatString, $aParams, $oCause);
                $this->httpCode = $httpCode;
        }

}

// ========================================================================
// Technical exceptions are thrown when generic code fails
// ------------------------------------------------------------------------

class Exception_Technical extends Exception_Enterprise
{
        public function __construct ($formatString, $aParams, Exception $oCause = null)
        {
                parent::__construct(0, $formatString, $aParams, $oCause);
        }
}

?>