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
                        l('Model', 'LANG_MODEL_E_EXPECTEDFIELDVALUE'),
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
                        l('Model', 'LANG_MODEL_E_FOREIGNKEYNOTDEFINED'),
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
                        l('Model', 'LANG_MODEL_E_INCOMPATIBLEDEFINITION'),
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
                        l('Model', 'LANG_MODEL_E_ISREADONLY'),
                        array (get_class($oObject)),
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
                        l('Model', 'LANG_MODEL_E_NOSUCHDEFINITION'),
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
                        l('Model', 'LANG_MODEL_E_NOSUCHFIELD'),
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
                                l('Model', 'LANG_MODEL_E_NOSUCHRECORDCLASS_1'),
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
                                l('Model', 'LANG_MODEL_E_NOSUCHRECORDCLASS_2'),
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
                        l('Model', 'LANG_MODEL_E_NOSUCHVIEW'),
                        array ($view, $modelName),
                        $oCause
                );
        }
}

?>