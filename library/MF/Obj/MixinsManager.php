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

/**
 * @category   MF
 * @package    MF_Obj
 */
class MF_Obj_MixinsManager
{
        static protected $mixins          = array();

        static public $mixinAutoInc = 0;

        protected function __construct()
        {
                // do nothing ... class cannot be instantiated
        }

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
                        self::$mixins[$extendedClass] = new MF_Obj_MixinsList($extendedClass);
                }

                return self::$mixins[$extendedClass];
        }

        /**
         * Returns an object representing all the mixins registered for
         * a given class (include all of its base classes)
         *
         * @param string $classname
         * @return MF_Obj_Mixins
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

?>