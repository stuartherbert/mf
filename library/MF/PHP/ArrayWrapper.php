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
 * @package    MF_PHP
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

__mf_init_module('PHP');

/**
 * @category   MF
 * @package    MF_PHP
 */
class MF_PHP_ArrayWrapper implements ArrayAccess, Iterator
{
        protected $__data = array();

        protected $__keys  = array();
        protected $__index = 0;

        public function __construct (&$__data = null)
        {
                if ($__data != null)
                {
                        constraint_mustBeArray($__data);
                        $this->__data =& $__data;
                        $this->rewind();
                }
        }

        // ================================================================
        // Interface: Iterator
        // ----------------------------------------------------------------

        public function rewind()
        {
                $this->__index = 0;
                $this->__keys  = array_keys($this->__data);
        }

        public function valid()
        {
                if (!isset($this->__keys[$this->__index]))
                        return false;

                if (!isset($this->__data[$this->__keys[$this->__index]]))
                        return false;

                return true;
        }

        public function key()
        {
                return $this->__keys[$this->__index];
        }

        public function current()
        {
                return $this->__data[$this->__keys[$this->__index]];
        }

        public function value()
        {
                return $this->__data[$this->__keys[$this->__index]];
        }

        public function next()
        {
                $this->__index++;

                return $this->valid();
        }

        // ================================================================
        // Additional methods to make dealing with arrays more useful

        public function previous()
        {
                if ($this->__index > 0)
                {
                        $this->__index--;
                }

                return $this->valid();
        }

        public function index()
        {
                return $this->__index;
        }

        // ================================================================
        // Allow array contents to be accessed as if class properties
        //
        // Derived classes can override this behaviour with their own
        // get/set methods (see App_Conditions for an example)
        // ----------------------------------------------------------------

        public function __get($name)
        {
                // step 1: do we have an override method?
                $method = 'get' . ucfirst($name);
                if (method_exists($this, $method))
                {
                        return $this->$method();
                }

                // step 2: return the data in the array
                if (!isset($this->__data[$name]))
                {
                        return null;
                }

                return $this->__data[$name];
        }

        public function __set($name, $value)
        {
                // step 1: do we have an override method?
                $method = 'set' . ucfirst($name);
                if (method_exists($this, $method))
                {
                        return $this->$method($value);
                }

                // step 2: just store the data directly in the array
                $this->__data[$name] = $value;
        }

        public function __isset($name)
        {
                // step 1: do we have an override method?
                $method = 'isset' . ucfirst($name);
                if (method_exists($this, $method))
                {
                        return $this->$method();
                }

                // step 2: just check on the data in the array
                return isset($this->__data[$name]);
        }

        // ================================================================
        // More voodoo ... array iterator support

        public function getIterator()
        {
                return new MF_PHP_Array($this->__data);
        }

        // ================================================================
        // Just in case you've not had enough voodoo ...
        // array [] operator support

        public function offsetSet($name, $value)
        {
                return $this->__set($name, $value);
        }

        public function offsetExists($name)
        {
                return $this->__isset($name);
        }

        public function offsetUnset($name)
        {
                unset($this->__data[$name]);
        }

        public function offsetGet($name)
        {
                return $this->__get($name);
        }
}

?>
