<?php

// ========================================================================
//
// Datastore/Datastore.exceptions.php
//              Exceptions thrown by the Datastore component
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
// 2007-09-11   SLH     Added Datastore_E_NoRowsFound
// 2008-01-05   SLH     Split upt Datastore_Record to create the new
//                      Model class
// 2008-01-06   SLH     Stopped using constants for language strings
// 2008-08-13   SLH     Added Datastore_E_StorageUnknown
// ========================================================================

// ========================================================================

class Datastore_E_Exception_Technical extends Exception_Technical
{

}

// ========================================================================

class Datastore_E_AdapterNotSupported extends Datastore_E_Exception_Technical
{
        function __construct($adapterName, $datastoreName, Exception $oCause = null)
        {
                parent::__construct(
                        app_l('Datastore', 'E_AdapterNotSupported'),
                        array ($adapterName, $datastoreName),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_ConnectFailed extends Datastore_E_Exception_Technical
{
        function __construct($msg, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_ConnectFailed'),
                        array ($msg),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_DeleteFailed extends Datastore_E_Exception_Technical
{
        function __construct($msg, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_DeleteFailed'),
                        array ($msg),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_DeleteWithoutPrimary extends Datastore_E_Exception_Technical
{
        function __construct(Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_DeleteWithoutPrimary'),
                        array(),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_ExpectedDatastore extends Datastore_E_Exception_Technical
{
        function __construct ($paramNo, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_ExpectedDatastore'),
                        array ($paramNo),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_IncompatibleAdapter extends Datastore_E_Exception_Technical
{
        function __construct($adapterName, $type, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_IncompatibleAdapter'),
                        array ($adapterName, $type),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_InsertFailed extends Datastore_E_Exception_Technical
{
        function __construct($msg, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_InsertFailed'),
                        array ($msg),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_NoRowsFound extends Datastore_E_Exception_Technical
{
	function __construct($sql, Exception $oCause = null)
        {
        	parent::__construct (
                        app_l('Datastore', 'E_NoRowsFound'),
                        array ($sql),
                        $oCause
                );
        }
}
// ========================================================================

class Datastore_E_OperationNotSupported extends Datastore_E_Exception_Technical
{
	function __construct($operation, Exception $oCause = null)
        {
                // TODO: localise this!!

                parent::__construct (
                        app_l('Datastore', 'E_OperationNotSupported'),
                        array ($operation),
                        $oCause
                );
        }
}
// ========================================================================

class Datastore_E_NeedDatastore extends Datastore_E_Exception_Technical
{
        function __construct($className, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_NeedDatastore'),
                        array ($className),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_NeedFields extends Datastore_E_Exception_Technical
{
        function __construct (Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_NeedFields'),
                        array(),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_NoSuchAdapter extends Datastore_E_Exception_Technical
{
        function __construct($adapterName, Exception $oCause = null)
        {
               parent::__construct(
                       app_l('Datastore', 'E_NoSuchAdapter'),
                       array ($adapterName),
                       $oCause
               );
        }
}

// ========================================================================

class Datastore_E_NotConnected extends Datastore_E_Exception_Technical
{
        function __construct($className, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_NotConnected'),
                        array ($className),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_NoValueForPrimaryKey extends Datastore_E_Exception_Technical
{
        function __construct($primaryKey, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_NoValueForPrimaryKey'),
                        array ($primaryKey),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_QueryFailed extends Datastore_E_Exception_Technical
{
        function __construct($query, $msg, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_QueryFailed'),
                        array ($query, $msg),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_RelatedDataNotFound extends Datastore_E_Exception_Technical
{
        function __construct ($type, $aConditions, Exception $oCause = null)
        {
                $conditions = "";

                foreach ($aConditions as $key => $value)
                {
                        $conditions .= $key . ' => ' . $value . ', ';
                }

                parent::__construct (
                        app_l('Datastore', 'E_RelatedDataNotFound'),
                        array ($type, $conditions),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_RetrieveFailed extends Datastore_E_Exception_Technical
{
        function __construct($msg, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_RetrieveFailed'),
                        array ($msg),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_StorageUnknown extends Datastore_E_Exception_Technical
{
	function __construct($modelName, Exception $oCause = null)
        {
        	parent::__construct (
        		app_l('Datastore', 'E_StorageUnknown'),
                        array ($modelName),
                        $oCause
        	);
        }
}

// ========================================================================

class Datastore_E_TruncateFailed extends Datastore_E_Exception_Technical
{
        function __construct($msg, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_TruncateFailed'),
                        array ($msg),
                        $oCause
                );
        }
}

// ========================================================================

class Datastore_E_UpdateFailed extends Datastore_E_Exception_Technical
{
        function __construct($msg, Exception $oCause = null)
        {
                parent::__construct (
                        app_l('Datastore', 'E_UpdateFailed'),
                        array ($msg),
                        $oCause
                );
        }
}

?>