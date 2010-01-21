<?php

// ========================================================================
//
// DataModel/DataModel.exceptions.php
//              Exceptions thrown by the DataModel component
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
// 2007-08-11   SLH     Consolidated from separate files
// 2008-01-06   SLH     Stopped using constants for language strings
// 2009-05-20   SLH     Added Model_E_NoSuchConvertor
// 2009-09-15	SLH	Renamed from Model to DataModel
// ========================================================================

class DataModel_E_Exception_Technical extends Exception_Technical
{
}

// ========================================================================

class DataModel_E_ExpectedFieldValue extends DataModel_E_Exception_Technical
{
        function __construct ($fieldName, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('DataModel', 'E_ExpectedFieldValue'),
                        array ($fieldName),
                        $oCause
                );
        }
}

// ========================================================================

class DataModel_E_ForeignKeyNotDefined extends DataModel_E_Exception_Technical
{
        function __construct ($myDefinition, $theirDefinition, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('DataModel', 'E_ForeignKeyNotDefined'),
                        array ($myDefinition, $theirDefinition),
                        $oCause
                );
        }
}

class DataModel_E_IncompatibleDefinition extends DataModel_E_Exception_Technical
{
        function __construct($recordName, $actualDefinition, $expectedDefinition, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('DataModel', 'E_IncompatibleDefinition'),
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

class DataModel_E_IsReadOnly extends DataModel_E_Exception_Technical
{
        function __construct($oObject, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('DataModel', 'E_IsReadOnly'),
                        array (get_class($oObject)),
                        $oCause
                );
        }
}

// ========================================================================

class DataModel_E_NoSuchConvertor extends DataModel_E_Exception_Technical
{
        function __construct($class, $convertor, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('DataModel', 'E_NoSuchConvertor'),
                        array ($class, $convertor),
                        $oCause
                );
        }
}

// ========================================================================

class DataModel_E_NoSuchDefinition extends DataModel_E_Exception_Technical
{
        function __construct ($definitionName, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('DataModel', 'E_NoSuchDefinition'),
                        array ($definitionName),
                        $oCause
                );
        }
}

class DataModel_E_NoSuchField extends DataModel_E_Exception_Technical
{
        function __construct ($field, $definition, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('DataModel', 'E_NoSuchField'),
                        array ($field, $definition),
                        $oCause
                );
        }
}

class DataModel_E_NoSuchRecordClass extends DataModel_E_Exception_Technical
{
        function __construct($className, Exception $oCause = null)
        {
                $maybeClassName = str_replace('_Record', '', $className);
                if ($maybeClassName !== $className && class_exists($maybeClassName))
                {
                        parent::__construct (
                                app_l('DataModel', 'E_NoSuchRecordClass_1'),
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
                                app_l('DataModel', 'E_NoSuchRecordClass_2'),
                                array
                                (
                                        $className,
                                ),
                                $oCause
                        );
                }
        }
}

class DataModel_E_NoSuchView extends DataModel_E_Exception_Technical
{
        function __construct ($modelName, $view, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('DataModel', 'E_NoSuchView'),
                        array ($view, $modelName),
                        $oCause
                );
        }
}

?>
