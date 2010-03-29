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

class MF_Exception_Iterator implements Iterator
{
        protected $oStart = null;

        private $oCurrent = null;
        private $level    = 0;

        public function __construct(MF_Exception_Enterprise $oException)
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

                if ((!($this->oCurrent instanceof MF_Exception_Enterprise)) ||
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

?>