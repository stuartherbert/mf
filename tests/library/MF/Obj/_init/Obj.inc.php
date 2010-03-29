<?php

class Test_ObjBase extends MF_Obj
{

}

class Test_ObjExt extends Test_ObjBase
{
        public $pubVar = 'hello';

        protected $protVar = 'world';

        public function getPlanet()
        {
                return $this->protVar;
        }

        public function setPlanet($value)
        {
                // var_dump($value);
                $this->protVar = $value;
        }

        public function issetPlanet()
        {
                return isset($this->protVar);
        }

        public function unsetPlanet()
        {
                // NOTE:
                //
                // We *cannot* unset($this->protVar), because that then
                // triggers problems if we call isset($this->protVar)
                // afterwards
                //
                // Instead, we must set the member's value to NULL

                $this->protVar = null;
        }

        public function validateCalled()
        {
                $return = array();

                $objs = $this->findObjsForMethod(__FUNCTION__);
                foreach ($objs as $obj)
                {
                        $result = $obj->validateCalled();
                        $return[] = $result;
                }

                return $return;
        }
}

class Test_Obj2 extends Test_ObjBase
{

}

class Test_Obj_BaseMixin extends MF_Obj_Mixin
{
        public    $baseMixinProp = 'silver';
        protected $protVar       = 'gold';

        public function getMetal()
        {
                return $this->protVar;
        }

        public function setMetal($value)
        {
                $this->protVar = $value;
        }

        public function issetMetal()
        {
                return isset($this->protVar);
        }

        public function unsetMetal()
        {
                unset($this->protVar);
        }

        public function validateCalled()
        {
                return get_class($this);
        }
}

class Test_Obj_ExtMixin extends MF_Obj_Mixin
{
        public    $mixinProp = 'fred';
        protected $protVar   = 'trout';

        public function getFish()
        {
                return $this->mixinProtVar;
        }

        public function setFish($value)
        {
                $this->protVar = $value;
        }

        public function issetFish()
        {
                return isset($this->protVar);
        }

        public function unsetFish()
        {
                $this->protVar = null;
        }

        public function validateCalled()
        {
                return get_class($this);
        }
}

class Test_Obj_ExtMixin2 extends MF_Obj_Mixin
{
        public function validateCalled()
        {
                return get_class($this);
        }
}

class Test_Obj_Decorator extends MF_Obj
{
        public    $decoratorProp = 'alice';
        protected $protVar       = 'lisa';

        public function getName()
        {
                return $this->protVar;
        }

        public function setName($value)
        {
                $this->protVar = $value;
        }

        public function issetName()
        {
                return isset($this->protVar);
        }

        public function unsetFish()
        {
                $this->protVar = null;
        }
}

?>
