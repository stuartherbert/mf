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
 * @package    MF_App
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

class MF_App_Conditions extends MF_PHP_ArrayWrapper
{
        public function __construct()
        {
                parent::__construct();

                // by default, users start off logged out
                $this->loggedIn = false;
        }

        public function resetConditions()
        {
                $this->clear();
        }

        // ================================================================
        // Intercept attempts to set loggedIn & loggedOut, to ensure
        // that the underlying conditions are always consistent

        public function setLoggedIn($value = true)
        {
                if ($value)
                {
                        $this->__data['loggedIn']  = true;
                        $this->__data['loggedOut'] = false;
                }
                else
                {
                        $this->__data['loggedIn']  = false;
                        $this->__data['loggedOut'] = true;
                }
        }

        public function setLoggedOut($value = true)
        {
                if ($value)
                {
                        $this->__data['loggedIn']  = false;
                        $this->__data['loggedOut'] = true;
                }
                else
                {
                        $this->__data['loggedIn']  = true;
                        $this->__data['loggedOut'] = false;
                }
        }
}

?>