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
// Copyright    (c) 2008-2009 Stuart Herbert
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
                        $this->mixins[$classname] = new $classname;
                }

                return $this->mixins[$classname];
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

        public function __get($propertyName)
        {
                $obj = $this->findObjForProperty($propertyName);
                if ($obj)
                {
                        return $obj->$propertyName;
                }

                $method = 'get' . ucfirst($propertyName);
                $obj = $this->findObjForMethod($method);
                if ($obj)
                {
                        return $obj->$method($this);
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
                        return $obj->$method($this);
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
                        $obj->$method($this);
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
                $args = array($this);
                foreach ($origArgs as $arg)
                {
                        $args[] = $arg;
                }

                $obj = $this->findObjForMethod($method);
                if ($obj)
                {
                        return $obj->$method($args);
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
        static protected $extendedClasses = array();

        static public $mixinAutoInc = 0;

        static public function destroy()
        {
                self::$mixins          = array();
                self::$extendedClasses = array();
                self::$mixinAutoInc    = 0;
        }
        
        static public function addMixin($extensionClass)
        {
                self::$mixinAutoInc++;

                if (!isset(self::$mixins[$extensionClass]))
                {
                        $mixin = new Obj_Mixin($extensionClass);
                        self::$mixins[$extensionClass] = $mixin;
                }

                return self::$mixins[$extensionClass];
        }

        /**
         * This method should only be called by Obj_Mixin
         *
         * @param string $classname
         * @param Obj_Mixin $mixin
         */

        static public function _extend($classname, Obj_Mixin $mixin)
        {
                if (!isset(self::$extendedClasses[$classname]))
                {
                        self::$extendedClasses[$classname] = new Obj_Mixins($classname);
                }
                self::$extendedClasses[$classname]->addMixin($mixin);

                // let everyone else know that this has happened
                Events_Manager::triggerEvent('classExtended', null, array('class' => $classname));
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
                if (isset(self::$extendedClasses[$classname]))
                {
                        // because classes can be defined partway through
                        // code that has already executed, the only safe
                        // way to ensure that our mixins obj knows about
                        // all of the mixins for each object is to make it
                        // update itself at this point

                        $mixins = self::$extendedClasses[$classname];
                        $mixins->updateBaseClassList();
                        return $mixins;
                }

                // if we get here, then $classname has not been extended
                // at all
                return null;
        }

        static public function _getMixinsFor($classname)
        {
                if (isset(self::$extendedClasses[$classname]))
                {
                        return self::$extendedClasses[$classname];
                }

                return null;
        }

        static public function var_dump()
        {
                var_dump(self::$mixins);
                var_dump(self::$extendedClasses);
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

        public function getMixinCount()
        {
                $baseClasses = $this->getBaseClasses();

                $count = 0;

                foreach ($baseClasses as $baseClass)
                {
                        $mixins = Obj_MixinsManager::getMixinsFor($baseClass);
                        if ($mixins === null)
                                continue;
                        $count += $mixins->_getMixinCount();
                }

                return $count;
        }

        public function _getMixinCount()
        {
                return count($this->mixins);
        }

        public function addMixin(Obj_Mixin $mixin)
        {
                $mixinName = $mixin->getName();
                $this->mixins[$mixinName]['mixin'] = $mixin;
                $this->mixins[$mixinName]['class'] = $mixinName;

                $methods    = $mixin->getMethods();
                $properties = $mixin->getProperties();

                foreach ($methods as $method)
                {
                        $this->mixinMethods[$method] = $mixinName;
                }

                foreach ($properties as $property)
                {
                        $this->mixinProperties[$property] = $mixinName;
                }
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
                                $this->cachedMixinMethods += $methods;
                                
                                $properties = $mixins->getProperties();
                                $this->cachedMixinProperties += $properties;
                        }

                        $this->lastSeenMixinAutoInc = Obj_MixinsManager::$mixinAutoInc;
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

        public function getMethods()
        {
                return $this->mixinMethods;
        }

        public function getProperties()
        {
                return $this->mixinProperties;
        }

        public function getClassnameForMethod($method)
        {
                if (!isset($this->cachedMixinMethods[$method]))
                {
                        return null;
                }
                return $this->cachedMixinMethods[$method];
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

class Obj_Mixin
{
        protected $name = '';
        protected $mixinMethods    = array();
        protected $mixinProperties = array();

        public function __construct($extensionName)
        {
                $this->name = $extensionName;
                $this->determineMethodsAndProperties();
        }

        protected function determineMethodsAndProperties()
        {
                $reflection = new ReflectionClass($this->name);
                $methods    = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach ($methods as $method)
                {
                        $this->mixinMethods[$method->getName()] = $method->getName();
                }

                $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
                foreach ($properties as $property)
                {
                        $this->mixinProperties[$property->getName()] = $property->getName();
                }
        }

        // ================================================================
        // $this->name is readonly to the outside

        public function getName()
        {
                return $this->name;
        }

        public function getMethods()
        {
                return $this->mixinMethods;
        }

        public function getProperties()
        {
                return $this->mixinProperties;
        }

        // ================================================================
        // API used to register a mixin for a specific class

        public function toClass($classname)
        {
                Obj_MixinsManager::_extend($classname, $this);
        }
}

?>