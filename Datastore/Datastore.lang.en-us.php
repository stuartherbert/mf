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
        'LANG_DATASTORE_E_ADAPTERNOTSUPPORTED'          => 'Adapter %s is not supported by datastore %s',
        'LANG_DATASTORE_E_CONNECTFAILED'                => 'Unable to connect to datastore; error message is %s',
        'LANG_DATASTORE_E_DELETEFAILED'                 => 'Unable to delete from datastore; error message is %s',
        'LANG_DATASTORE_E_DELETEWITHOUTPRIMARY'         => 'Attempt to delete a record w/ no primary ID specified',
        'LANG_DATASTORE_E_EXPECTEDDATASTORE'            => "Expected param '%d' to be a Datastore, but didn't get one",
        'LANG_DATASTORE_E_INCOMPATIBLEADAPTER'          => "Adapter '%s' is not a Datastore_I_CRUD_Store (is type '%s')",
        'LANG_DATASTORE_E_INSERTFAILED'                 => 'Unable to insert into datastore; error message is %s',
        'LANG_DATASTORE_E_NEEDDATASTORE'                => "Cannot store() instance of '%s' without a datastore",
        'LANG_DATASTORE_E_NEEDFIELDS'                   => "Expected a list of fields, but didn't get any",
        'LANG_DATASTORE_E_NOROWSFOUND'                  => "No rows found for query '%s'",
        'LANG_DATASTORE_E_NOSUCHADAPTER'                => 'Adapter %s does not exist (missing require statement?)',
        'LANG_DATASTORE_E_NOTCONNECTED'                 => "Instance of '%s' needs to be connected first",
        'LANG_DATASTORE_E_NOVALUEFORPRIMARYKEY'         => 'Data does not have a value for primary key %s',
        'LANG_DATASTORE_E_OPERATIONNOTSUPPORTED'        => 'Operation %s is not supported by this connector',
        'LANG_DATASTORE_E_QUERYFAILED'                  => 'Unable to process query \'%s\'; error message is %s',
        'LANG_DATASTORE_E_RELATEDDATANOTFOUND'          => "Could not find data of type %s w/ search conditions %s",
        'LANG_DATASTORE_E_RETRIEVEFAILED'               => 'Unable to retrieve from datastore; error message is %s',
        'LANG_DATASTORE_E_STORAGEUNKNOWN'               => 'Datastore has no storage map for model %s',
        'LANG_DATASTORE_E_TRUNCATEFAILED'               => 'Unable to truncate datastore; error message is %s',
));

?>