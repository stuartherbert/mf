<?php

// ========================================================================
//
// Model/Model.exceptions.php
//              Exceptions thrown by the Model component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-08-11   SLH     Consolidated from separate files
// 2008-01-06   SLH     Stopped using constants for language strings
// 2009-05-20   SLH     Added Model_E_NoSuchConvertor
// ========================================================================

class Model_E_Exception_Technical extends Exception_Technical
{
}

// ========================================================================

class Model_E_ExpectedFieldValue extends Model_E_Exception_Technical
{
        function __construct ($fieldName, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Model', 'E_ExpectedFieldValue'),
                        array ($fieldName),
                        $oCause
                );
        }
}

// ========================================================================

class Model_E_ForeignKeyNotDefined extends Model_E_Exception_Technical
{
        function __construct ($myDefinition, $theirDefinition, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Model', 'E_ForeignKeyNotDefined'),
                        array ($myDefinition, $theirDefinition),
                        $oCause
                );
        }
}

class Model_E_IncompatibleDefinition extends Model_E_Exception_Technical
{
        function __construct($recordName, $actualDefinition, $expectedDefinition, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Model', 'E_IncompatibleDefinition'),
                        array
                        (
                                $recordName,
                                $expectedDefinition,
                                $actualDefinition,
                        ),
                        $oCause
                );
        }
}

// ========================================================================

class Model_E_IsReadOnly extends Model_E_Exception_Technical
{
        function __construct($oObject, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Model', 'E_IsReadOnly'),
                        array (get_class($oObject)),
                        $oCause
                );
        }
}

// ========================================================================

class Model_E_NoSuchConvertor extends Model_E_Exception_Technical
{
        function __construct($class, $convertor, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Model', 'E_NoSuchConvertor'),
                        array ($class, $convertor),
                        $oCause
                );
        }
}

// ========================================================================

class Model_E_NoSuchDefinition extends Model_E_Exception_Technical
{
        function __construct ($definitionName, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Model', 'E_NoSuchDefinition'),
                        array ($definitionName),
                        $oCause
                );
        }
}

class Model_E_NoSuchField extends Model_E_Exception_Technical
{
        function __construct ($field, $definition, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Model', 'E_NoSuchField'),
                        array ($field, $definition),
                        $oCause
                );
        }
}

class Model_E_NoSuchRecordClass extends Model_E_Exception_Technical
{
        function __construct($className, Exception $oCause = null)
        {
                $maybeClassName = str_replace('_Record', '', $className);
                if ($maybeClassName !== $className && class_exists($maybeClassName))
                {
                        parent::__construct (
                                app_l('Model', 'E_NoSuchRecordClass_1'),
                                array
                                (
                                        $className,
                                        $maybeClassName
                                ),
                                $oCause
                        );
                }
                else
                {
                        parent::__construct (
                                app_l('Model', 'E_NoSuchRecordClass_2'),
                                array
                                (
                                        $className,
                                ),
                                $oCause
                        );
                }
        }
}

class Model_E_NoSuchView extends Model_E_Exception_Technical
{
        function __construct ($modelName, $view, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Model', 'E_NoSuchView'),
                        array ($view, $modelName),
                        $oCause
                );
        }
}

?>