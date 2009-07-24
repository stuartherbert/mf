<?php

// ========================================================================
//
// Datastore/Datastore.lang.en-us.php
//              US English language strings for the Datastore component
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
// 2007-08-11   SLH     Created
// 2007-09-11   SLH     Added LANG_DATASTORE_E_NOROWSFOUND
// 2008-01-05   SLH     Split up Datastore_Record to create the new
//                      Model class
// 2008-01-06   SLH     Stopped using constants for language strings
// 2008-02-11   SLH     Foreign language strings now go in APP_CONFIG
// 2008-08-13   SLH     Added LANG_DATASTORE_E_STORAGEUNKNOWN
// 2009-03-01   SLH     Languages now loaded through App
// ========================================================================

App::$languages->addTranslationsForModule('Datastore', 'en-us', array
(
        'E_AdapterNotSupported'         => 'Adapter %s is not supported by datastore %s',
        'E_ConnectFailed'               => 'Unable to connect to datastore; error message is %s',
        'E_DeleteFailed'                => 'Unable to delete from datastore; error message is %s',
        'E_DeleteWithoutPrimary'        => 'Attempt to delete a record w/ no primary ID specified',
        'E_ExpectedDatastore'           => "Expected param '%d' to be a Datastore, but didn't get one",
        'E_IncompatibleAdapter'         => "Adapter '%s' is not a Datastore_I_CRUD_Store (is type '%s')",
        'E_InsertFailed'                => 'Unable to insert into datastore; error message is %s',
        'E_NeedDatastore'               => "Cannot store() instance of '%s' without a datastore",
        'E_NeedFields'                  => "Expected a list of fields, but didn't get any",
        'E_NoRowsFound'                 => "No rows found for query '%s'",
        'E_NoSuchAdapter'               => 'Adapter %s does not exist (missing require statement?)',
        'E_NotConnected'                => "Instance of '%s' needs to be connected first",
        'E_NoValueForPrimaryKey'        => 'Data does not have a value for primary key %s',
        'E_OperationNotSupported'       => 'Operation %s is not supported by this connector',
        'E_QueryFailed'                 => 'Unable to process query \'%s\'; error message is %s',
        'E_RelatedDataNotFound'         => "Could not find data of type %s w/ search conditions %s",
        'E_RetrieveFailed'              => 'Unable to retrieve from datastore; error message is %s',
        'E_StorageUnknown'              => 'Datastore has no storage map for model %s',
        'E_TruncateFailed'              => 'Unable to truncate datastore; error message is %s',
        'E_UpdateFailed'                => 'Unable to update datastore; error message is %s',
));

?>