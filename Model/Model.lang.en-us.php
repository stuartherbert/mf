<?php

// ========================================================================
//
// Model/Model.lang.en-us.php
//              US English language strings for the Model component
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
// 2007-11-13   SLH     Added this header
// 2008-01-06   SLH     Stopped using constants for language strings
// 2008-02-11   SLH     Foreign language strings now go in APP_CONFIG
// 2009-03-01   SLH     Foreign language strings now go in Language_Manager
// ========================================================================

App::$languages->addTranslationsForModule('Model', 'en-us', array
(
        'LANG_MODEL_E_EXPECTEDFIELDVALUE'       => "Expected a value for field '%s', but didn't get one",
        'LANG_MODEL_E_FOREIGNKEYNOTDEFINED'     => "Foreign key on '%s' for '%s' not defined",
        'LANG_MODEL_E_INCOMPATIBLEDEFINITION'   => 'Expected %s to use definition %s; it actually uses definition %s',
        'LANG_MODEL_E_ISREADONLY'               => "Record of type '%s' is readonly",
        'LANG_MODEL_E_NOSUCHDEFINITION'         => "No model definition exists for '%s'",
        'LANG_MODEL_E_NOSUCHFIELD'              => "Field '%s' is not supported for records of type '%s'",
        'LANG_MODEL_E_NOSUCHRECORDCLASS_1'      => "No such class '%s'; rename your existing class '%s' to have _Record on the end of the name",
        'LANG_MODEL_E_NOSUCHRECORDCLASS_2'      => "No such class %s; cannot create record",
        'LANG_MODEL_E_NOSUCHVIEW'               => "View '%s' is not defined for records of type '%s'",
));

?>