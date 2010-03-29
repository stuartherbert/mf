<?php

// ========================================================================
//
// PHP/PHP.classes.php
//              Classes to help with working with the PHP language
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
// 2009-07-07   SLH     Added ArrayAccess support to PHP_Array
// 2009-07-07   SLH     Added __get/__set support to PHP_Array
// 2009-07-07   SLH     Added PHP_Array.append()
// 2009-07-08   SLH     Split out basic object/array support into
//                      PHP_ObjectArray
// 2009-07-09   SLH     Changed name of PHP_ObjectArray's underlying
//                      properties to avoid them clashing with likely keys
//                      in the user's actual array data
// ========================================================================

class PHP_ObjectArray implements ArrayAccess, Iterator
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
                return new PHP_Array($this->__data);
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



// ========================================================================



?>