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
 * @package    MF_Exception
 * @copyright  Copyright (c) 2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

/**
 * @category MF
 * @package  MF_Exception
 */

class MF_Exception_Enterprise extends Exception
{
        /**
         * holds the original exception if we are throwing a new one
         *
         * @var Exception
         */
        protected $cause = null;

        /**
         * holds the list of parameters to explain this exception
         *
         * @var array
         */
        protected $params = null;

        /**
         * the HTTP code to return to the browser or calling HTTP client
         *
         * @var int
         */
        public $httpCode = 500;

        /**
         * constructor
         */

        public function __construct ($httpCode, $errorCode, $formatString, $params, Exception $cause = null)
        {
                $message = vsprintf($formatString, $params);

                parent::__construct($message, $errorCode);

                $this->cause     = $cause;
                $this->params    = $params;
                $this->httpCode  = $httpCode;
        }

        /**
         * return the exception that caused this exception
         */

        public function getCause()
        {
                return $this->cause;
        }

        /**
         * was this exception caused by another one?
         */

        public function hasCause()
        {
                if ($this->cause != null)
                        return true;

                return false;
        }

        /**
         *
         * @param string $cause name of the class to check for
         * @return boolean
         */
        public function wasCausedBy($cause)
        {
                // if we have no cause, bail
        	if (!$this->hasCause())
                {
                	return false;
                }

                // if the cause matches, let them know
                if ($this->cause instanceof $cause)
                {
                	return true;
                }

                // what if the cause is also a symptom?
                if (method_exists($this->cause, 'wasCausedBy'))
                {
                        return $this->cause->wasCausedBy($cause);
                }

                // if we get here, we have exhausted our possibilities
                return false;
        }

        public function getIterator ()
        {
                return new MF_Exception_Iterator($this);
        }

        public function getParams()
        {
                return $this->params;
        }

        public function getHttpReturnCode()
        {
                return $this->httpCode;
        }
}

?>