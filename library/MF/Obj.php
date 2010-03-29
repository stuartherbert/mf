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
 * @package    MF_Obj
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

__mf_init_module('Obj');
__mf_init_module('PHP');

/**
 * @category   MF
 * @package    MF_Obj
 */
class MF_Obj
{
        /**
         * A list of the mixin objects that extend this object
         * @var array
         */
        protected $mixins = array();

        /**
         * When we are looking for mixins, what name shall we give?
         *
         * This was originally added to allow Model to use this generic
         * mixin support rather than have its own generic implementation.
         *
         * @var string
         */
        protected $extensibleName = null;

        /**
         * Objects that extends this *specific* instance. (Mixins extend
         * *all* instances).
         *
         * @var object
         */
        protected $decorators = array();

        public function __construct($extensibleName = null)
        {
                if ($extensibleName !== null)
                {
                        $this->extensibleName = $extensibleName;
                }
                else
                {
                        $this->extensibleName = get_class($this);
                }
        }

        // ================================================================
        // Helper methods for decorators
        // ----------------------------------------------------------------

        public function addDecorator($obj)
        {
                $this->decorators['objs'][] = $obj;

                $refObj = new ReflectionObject($obj);
                $methods = $refObj->getMethods(ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $method)
                {
                        $this->decorators['methods'][$method->name] = $obj;
                }

                $properties = $refObj->getProperties(ReflectionProperty::IS_PUBLIC);
                foreach ($properties as $property)
                {
                        $this->decorators['properties'][$property->name] = $obj;
                }
        }

        public function resetDecorators()
        {
                $this->decorators = array();
        }

        // ================================================================
        // Helper methods for mixins
        // ----------------------------------------------------------------

        protected function getMixinObject($classname)
        {
                if (!isset($this->mixins[$classname]))
                {
                        $mixin = new $classname($this);
                        $this->requireValidMixin($mixin);
                        $this->mixins[$classname] = $mixin;
                }

                return $this->mixins[$classname];
        }

        protected function requireValidMixin($obj)
        {
                if (!$obj instanceof MF_Obj_Mixin)
                {
                        throw new MF_PHP_E_ConstraintFailed(__METHOD__);
                }
        }

        public function getMixinCount()
        {
                $mixins = MF_Obj_MixinsManager::getMixinsFor($this->extensibleName);

                if ($mixins === null)
                {
                        // we have no mixins at all yet
                        return 0;
                }

                return $mixins->getMixinCount();
        }

        public function getExtendsObj()
        {
                return true;
        }

        public function hasMixins()
        {
                return ($this->getMixinCount() > 0);
        }

        // ================================================================
        // Member support
        // ----------------------------------------------------------------

        protected function findObjForProperty($propertyName)
        {
                // we are not a mixin ... so look at our mixins instead
                $mixins = MF_Obj_MixinsManager::getMixinsFor($this->extensibleName);
                if ($mixins !== null)
                {
                        $class = $mixins->getClassnameForProperty($propertyName);
                        if ($class !== null)
                        {
                                return $this->getMixinObject($class);
                        }
                }

                // if we get here, the property does not exist in a mixin
                // what about our decorators?

                if (isset($this->decorators['properties'][$propertyName]))
                {
                        return $this->decorators['properties'][$propertyName];
                }

                // if we get here, we have nowhere else left to look
                return null;
        }

        protected function findObjForMethod($methodName)
        {
                //var_dump('findObjForMethod: ' . $methodName);
                // does the method exist in our own class?
                if (method_exists($this, $methodName))
                        return $this;

                // what about in the mixins?
                $mixins = MF_Obj_MixinsManager::getMixinsFor($this->extensibleName);
                //var_dump('Back from getMixinsFor');
                if ($mixins !== null)
                {
                        //var_dump('Got mixins to look at');
                        $class = $mixins->getClassnameForMethod($methodName);
                        //var_dump('Classname is ' . $class);
                        if ($class !== null)
                        {
                                return $this->getMixinObject($class);
                        }
                }

                // what about our decorators?
                if (isset($this->decorators['methods'][$methodName]))
                {
                        return $this->decorators['methods'][$methodName];
                }

                // if we get here, we have nowhere else left to look
                return null;
        }

        protected function findObjsForMethod($methodName)
        {
                // we are specifically looking for all of the objects
                // that provide the same method

                $return = array();

                // check the mixins first
                $mixins = MF_Obj_MixinsManager::getMixinsFor($this->extensibleName);
                if ($mixins !== null)
                {
                        // var_dump($mixins);
                        $classes = $mixins->getClassnamesForMethod($methodName);
                        if ($classes !== null)
                        {
                                foreach ($classes as $class)
                                {
                                        $return[] = $this->getMixinObject($class);
                                }
                        }
                }

                // now, what about our decorators too?
                if (isset($this->decorators['objs']))
                {
                        foreach ($this->decorators['objs'] as $decorator)
                        {
                                if (method_exists($decorator, $methodName))
                                {
                                        $return[] = $decorator;
                                }
                        }
                }

                return $return;
        }

        public function __get($propertyName)
        {
                // var_dump('Looking for property ' . $propertyName);
                
                $obj = $this->findObjForProperty($propertyName);
                if ($obj)
                {
                        return $obj->$propertyName;
                }

                $method = 'get' . ucfirst($propertyName);
                //var_dump('Looking for method ' . $method);
                $obj = $this->findObjForMethod($method);
                //var_dump($obj);
                if ($obj)
                {
                        //var_dump($method);
                        return $obj->$method();
                }

                // if we get here, the property does not exist
                throw new MF_Obj_E_NoSuchProperty($propertyName, $this);
        }

        public function __set($propertyName, $value)
        {
                $obj = $this->findObjForProperty($propertyName);
                if ($obj)
                {
                        $obj->$propertyName = $value;
                        return;
                }

                $method = 'set' . ucfirst($propertyName);
                $obj = $this->findObjForMethod($method);
                if ($obj)
                {
                        $obj->$method($value);
                        return;
                }

                // if we get here, the property does not exist
                throw new MF_Obj_E_NoSuchProperty($propertyName, $this);
        }

        public function __isset($propertyName)
        {
                $obj = $this->findObjForProperty($propertyName);
                if ($obj)
                {
                        return isset($obj->$propertyName);
                }

                $method = 'isset' . ucfirst($propertyName);
                $obj = $this->findObjForMethod($method);
                if ($obj)
                {
                        return $obj->$method();
                }

                // if we get here, the property does not exist
                //
                // as a special case, we do not throw an exception, as
                // it is legitimate to use isset() to test for whether a
                // property exists at all or not
                return false;
        }

        public function __unset($propertyName)
        {
                $obj = $this->findObjForProperty($propertyName);
                if ($obj)
                {
                        unset($obj->$propertyName);
                        return;
                }

                $method = 'unset' . ucfirst($propertyName);
                $obj = $this->findObjForMethod($method);
                if ($obj)
                {
                        $obj->$method();
                        return;
                }

                // if we get here, the property does not exist
                //
                // as a special case, we silently ignore this error, to
                // be consistent with the semantics of isset()
        }

        // ================================================================
        // Method support
        // ----------------------------------------------------------------

        public function __call($method, $origArgs)
        {
                // prepare the args to pass to the method
                $obj = $this->findObjForMethod($method);
                if ($obj)
                {
                        if ($obj instanceof MF_Obj_Mixin)
                        {
                                return call_user_func_array(array($obj, $method), $origArgs);
                        }
                        else
                        {
                                // put $this at the front of the args
                                $args = array($this);
                                foreach ($origArgs as $arg)
                                {
                                        $args[] = $arg;
                                }

                                return call_user_func_array(array($obj, $method), $args);
                        }
                }

                // if we get here, then the method does not exist
                throw new MF_Obj_E_NoSuchMethod($method, $this);
        }

        // ================================================================
        // Useful helpers for calling methods
        // ----------------------------------------------------------------

        public function requireValidMethod($method)
        {
                if (!method_exists($this, $method))
                {
                        throw new MF_Obj_E_NoSuchMethod($method, $this);
                }
        }
}

?>