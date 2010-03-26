<?php

// ========================================================================
//
// Obj/Obj.classes.php
//              Base class for use by other objects
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-07-19   SLH     Created
// 2009-05-22   SLH     Added fake property support
// 2009-05-23   SLH     Removed __call_X() - just not needed
// 2009-05-23   SLH     Added generic mixin support
// 2009-05-24   SLH     Renamed to Obj
// 2009-05-25   SLH     Trigger an event when a class is extended
// 2009-05-25   SLH     Added generic decorator support too, for
//                      completeness
// 2009-05-25   SLH     Obj_MixinDefinitions renamed Obj_MixinsManager
// 2009-05-27   SLH     Fixed Obj::__call to pass correct args
// 2009-05-28   SLH     Added ability to call same method on all
//                      decorators and mixins at once
// 2009-06-02   SLH     Fix for calling same method on all mixins at once
// 2009-06-03   SLH     Added ability for mixins and decorators to act
//                      as if truly part of the object they are extending
// ========================================================================

class Obj
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
                if (!$obj instanceof Obj_Mixin)
                {
                        throw new PHP_E_ConstraintFailed(__METHOD__);
                }
        }

        public function getMixinCount()
        {
                $mixins = Obj_MixinsManager::getMixinsFor($this->extensibleName);
                
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
                $mixins = Obj_MixinsManager::getMixinsFor($this->extensibleName);
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
                // does the method exist in our own class?
                if (method_exists($this, $methodName))
                        return $this;

                // what about in the mixins?
                $mixins = Obj_MixinsManager::getMixinsFor($this->extensibleName);
                if ($mixins !== null)
                {
                        $class = $mixins->getClassnameForMethod($methodName);
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
                $mixins = Obj_MixinsManager::getMixinsFor($this->extensibleName);
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
                // var_dump('Looking for method ' . $method);
                $obj = $this->findObjForMethod($method);
                if ($obj)
                {
                        return $obj->$method();
                }

                // if we get here, the property does not exist
                throw new Obj_E_NoSuchProperty($propertyName, $this);
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
                throw new Obj_E_NoSuchProperty($propertyName, $this);
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
                        if ($obj instanceof Obj_Mixin)
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
                throw new Obj_E_NoSuchMethod($method, $this);
        }

        // ================================================================
        // Useful helpers for calling methods
        // ----------------------------------------------------------------

        public function requireValidMethod($method)
        {
                if (!method_exists($this, $method))
                {
                        throw new Obj_E_NoSuchMethod($method, $this);
                }
        }
}

class Obj_Mixin
{
        protected $extending = null;

        public function __construct($extending)
        {
                $this->extending = $extending;
        }

        public function __get($property)
        {
                if (!is_object($this->extending))
                {
                        throw new Obj_E_NoSuchProperty($property, $this);
                }

                $obj = $this->extending;
                return $obj->$property;
        }

        public function __set($property, $value)
        {
                if (!is_object($this->extending))
                {
                        throw new Obj_E_NoSuchProperty($property, $this);
                }

                $obj = $this->extending;
                $obj->$property = $value;
        }

        public function __isset($property)
        {
                if (!is_object($this->extending))
                {
                        throw new Obj_E_NoSuchProperty($property, $this);
                }

                $obj = $this->extending;
                return isset($obj->$property);
        }

        public function __unset($property)
        {
                if (!is_object($this->extending))
                {
                        throw new Obj_E_NoSuchProperty($property, $this);
                }

                $obj = $this->extending;
                unset($obj->$property);
        }

        public function __call($method, $args)
        {
                if (!is_object($this->extending))
                {
                        throw new Obj_E_NoSuchMethod($method, $this);
                }

                $obj = $this->extending;
                return call_user_func_array(array($obj, $method), $args);
        }
}

class Obj_Singleton
{
        protected function __construct()
        {
                // do nothing ... class cannot be instantiated
        }
}

class Obj_MixinsManager extends Obj_Singleton
{
        static protected $mixins          = array();

        static public $mixinAutoInc = 0;

        static public function destroy()
        {
                self::$mixins          = array();
                self::$mixinAutoInc    = 0;
        }

        static public function extend($extendedClass)
        {
                self::$mixinAutoInc++;

                if (!isset(self::$mixins[$extendedClass]))
                {
                        self::$mixins[$extendedClass] = new Obj_Mixins($extendedClass);
                }

                return self::$mixins[$extendedClass];
        }

        /**
         * Returns an object representing all the mixins registered for
         * a given class (include all of its base classes)
         *
         * @param string $classname
         * @return Obj_Mixins
         */

        static public function getMixinsFor($classname)
        {
                if (isset(self::$mixins[$classname]))
                {
                        // because classes can be defined partway through
                        // code that has already executed, the only safe
                        // way to ensure that our mixins obj knows about
                        // all of the mixins for each object is to make it
                        // update itself at this point

                        $mixins = self::$mixins[$classname];
                        $mixins->updateBaseClassList();
                        return $mixins;
                }

                // if we get here, then $classname has not been extended
                // at all
                return null;
        }

        static public function _getMixinsFor($classname)
        {
                if (isset(self::$mixins[$classname]))
                {
                        return self::$mixins[$classname];
                }

                return null;
        }

        static public function var_dump()
        {
                var_dump(self::$mixins);
                var_dump(self::$mixinAutoInc);
        }
}

class Obj_Mixins
{
        protected $mixins          = array();
        protected $mixinMethods    = array();
        protected $mixinProperties = array();

        protected $cachedMixinMethods    = array();
        protected $cachedMixinProperties = array();

        protected $lastSeenMixinAutoInc = 0;
        protected $baseClasses     = array();

        public function __construct($classname)
        {
                $this->name = $classname;
        }

        public function withClass($extensionClass)
        {
                // var_dump('Extending ' . $this->name . ' with ' . $extensionClass);
                // var_dump('We have ' . count($this->mixins) . ' mixins before this one');
                
                constraint_mustBeString($extensionClass);

                $this->mixins[$extensionClass] = $extensionClass;
                
                $methods    = $this->getMethodsFromClass($extensionClass);
                $properties = $this->getPropertiesFromClass($extensionClass);

                foreach ($methods as $method)
                {
                        $this->mixinMethods[$method][] = $extensionClass;
                }

                foreach ($properties as $property)
                {
                        $this->mixinProperties[$property] = $extensionClass;
                }

                Events_Manager::triggerEvent('classExtended', null, array('class' => $this->name, 'extension' => $extensionClass));

                // var_dump('We now have ' . count($this->mixins) . ' mixins after this one');
        }

        public function getMixinCount()
        {
                $baseClasses = $this->getBaseClasses();

                $count = 0;

                foreach ($baseClasses as $baseClass)
                {
                        $mixins = Obj_MixinsManager::getMixinsFor($baseClass);
                        if ($mixins === null)
                                continue;

                        // var_dump('getMixinCount(): found ' . $mixins->_getMixinCount() . ' mixins for base class ' . $baseClass);
                        // var_dump($mixins);
                        $count += $mixins->_getMixinCount();
                }

                return $count;
        }

        public function _getMixinCount()
        {
                return count($this->mixins);
        }

        public function updateBaseClassList()
        {
                // we need to look at all the base classes of the class
                // we have extended, and make sure we know about their
                // mixins too
                //
                // to boost performance, we look at the auto increment
                // counter in Obj_MixinDefinitions to see whether our
                // cache is still valid or not

                if ($this->lastSeenMixinAutoInc !== Obj_MixinsManager::$mixinAutoInc)
                {
                        // we need to rebuild our list
                        $baseClasses = $this->getBaseClasses();
                        foreach ($baseClasses as $baseClass)
                        {
                                $mixins = Obj_MixinsManager::_getMixinsFor($baseClass);
                                
                                // skip over any classes that have not been
                                // extended at this time
                                if ($mixins === null)
                                        continue;

                                $methods    = $mixins->getMethods();
                                
                                foreach ($methods as $method => $classnames)
                                {
                                        foreach ($classnames as $classname)
                                        {
                                                // var_dump('Adding ' . $classname . '::' . $method . ' to list of cached methods');
                                                $this->cachedMixinMethods[$method][] = $classname;
                                        }
                                }
                                
                                $properties = $mixins->getProperties();
                                $this->cachedMixinProperties += $properties;
                        }

                        $this->lastSeenMixinAutoInc = Obj_MixinsManager::$mixinAutoInc;

                        // var_dump($this);
                }

                // at this point, the cachedMixin* properties have the
                // right values (in the right order!!) to be used by the
                // classes we have extended
        }

        public function getBaseClasses()
        {
                $refObj      = new ReflectionClass($this->name);
                $baseClasses = array();

                while ($refObj !== false)
                {
                        $baseClasses[] = $refObj->getName();
                        $refObj = $refObj->getParentClass();
                }

                // var_dump($baseClasses);
                return $baseClasses;
        }

        public function getMethodsFromClass($classname)
        {
                $reflection = new ReflectionClass($classname);
                $methods    = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                $return     = array();

                foreach ($methods as $method)
                {
                        $return[$method->getName()] = $method->getName();
                }

                return $return;
        }

        public function getPropertiesfromClass($classname)
        {
                $reflection = new ReflectionClass($classname);
                $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
                $return     = array();

                foreach ($properties as $property)
                {
                        $return[$property->getName()] = $property->getName();
                }

                return $return;
        }

        public function getMethods()
        {
                return $this->mixinMethods;
        }

        public function getProperties()
        {
                return $this->mixinProperties;
        }

        public function getClassnamesForMethod($method)
        {
                if (!isset($this->cachedMixinMethods[$method]))
                {
                        return null;
                }
                return $this->cachedMixinMethods[$method];
        }

        public function getClassnameForMethod($method)
        {
                // var_dump('looking in cached for ' . $method);
                
                if (!isset($this->cachedMixinMethods[$method]))
                {
                        return null;
                }

                $count = count($this->cachedMixinMethods[$method]);
                if ($count == 0)
                {
                        // should never happen, but just in case
                        return null;
                }

                // var_dump($this->cachedMixinMethods[$method]);
                // var_dump($count);
                // var_dump($this->cachedMixinMethods[$method][$count - 1]);
                
                return $this->cachedMixinMethods[$method][$count - 1];
        }

        public function getClassnameForProperty($property)
        {
                if (!isset($this->cachedMixinProperties[$property]))
                {
                        return null;
                }
                return $this->cachedMixinProperties[$property];
        }
}

?>