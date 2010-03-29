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

?>