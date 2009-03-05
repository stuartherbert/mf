<?php

// ========================================================================
//
// RecordBehaviour/RecordBehaviour.classes.php
//              Classes for the RecordBehaviour component
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
// 2008-03-16   SLH     Separated out from Datastore component
// ========================================================================

class RecordBehaviour_Base
{
        public function addFields(Model_Definition $oDef)
        {

        }

        public function preCreate(Datastore $oDB, Datastore_Record $oRecord)
        {
                return true;
        }

        public function preUpdate(Datastore $oDB, Datastore_Record $oRecord)
        {
                return true;
        }

        public function preDelete(Datastore $oDB, Datastore_Record $oRecord)
        {
                return true;
        }

        public function preRetrieve(Datastore $oDB, Datastore_Record $oRecord)
        {
                return true;
        }

        public function preStore(Datastore $oDB, Datastore_Record $oRecord)
        {
                return true;
        }

        public function postCreate(Datastore $oDB, Datastore_Record $oRecord)
        {
                return true;
        }

        public function postUpdate(Datastore $oDB, Datastore_Record $oRecord)
        {
        }

        public function postDelete(Datastore $oDB, Datastore_Record $oRecord)
        {
        }

        public function postRetrieve(Datastore $oDB, Datastore_Record $oRecord)
        {
        }

        public function postStore(Datastore $oDB, Datastore_Record $oRecord)
        {
        }
}

class RecordBehaviour_AuthenticatedUser
extends RecordBehaviour_Base
{
        protected $isAuthenticated = false;

        public function addFields(Model_Definition $oDef)
        {
                $oDef->addFakeField('authenticated');
                $oDef->addField('dateAuthenticated');
        }

        public function preStore(Datastore $oDB, Datastore_Record $oRecord)
        {
                if (!$oRecord->authenticated)
                {
                        $oRecord->dateAuthenticated = null;
                }
                else if ($oRecord->dateAuthenticated === null)
                {
                        $oRecord->dateAuthenticated = date('Y-m-d H:i:s', time());
                }

                return true;
        }
}

class RecordBehaviour_ChangingTimes
extends RecordBehaviour_Base
{
        public function addFields(Model_Definition $oDef)
        {
                $oDef->addField('dateCreated');
                $oDef->addField('dateModified');
        }

        public function preCreate(Datastore $oDB, Datastore_Record $oRecord)
        {
                $oRecord->dateCreated  = date('Y-m-d H:i:s', time());
                $oRecord->dateModified = null;

                return true;
        }

        public function preUpdate(Datastore $oDB, Datastore_Record $oRecord)
        {
                $oRecord->dateModified = date('Y-m-d H:i:s', time());

                return true;
        }
}

class RecordBehaviour_Deleted
extends RecordBehaviour_Base
{
        public function addFields(Model_Definition $oDef)
        {
                $oDef->addField('deleted');
                $oDef->setDefaultForField('deleted', 0);
        }

        public function preDelete(Datastore $oDB, Datastore_Record $oRecord)
        {
                $oRecord->deleted = 1;
                $oRecord->store($oDB);

                // we do not want the record to continue with the delete
                // operation!
                return false;
        }
}

class RecordBehaviour_Uuid
extends RecordBehaviour_Base
{
        public function addFields(Model_Definition $oDef)
        {
                $oDef->setPrimaryKeyIsAutoGenerated(false);
        }

        public function preCreate(Datastore $oDB, Datastore_Record $oRecord)
        {
                if (!$oRecord->getUniqueId())
                {
                        $oRecord->setUniqueId(uuid_create(UUID_TYPE_TIME));
                }

                return true;
        }
}

class RecordBehaviour_ValidatedEmailAddress
extends RecordBehaviour_Base
{
        public function addFields(Model_Definition $oDef)
        {
                $oDef->addFakeField('emailValidated');
                $oDef->addField('dateEmailValidated');
                $oDef->addField('emailValidationCode');
        }

        public function preStore(Datastore $oDB, Datastore_Record $oRecord)
        {
                if (!$oRecord->emailValidated)
                {
                        $oRecord->dateEmailValidated = null;
                }
                else if ($oRecord->dateEmailValidated === null)
                {
                        $oRecord->dateEmailValidated  = date('Y-m-d H:i:s', time());
                        $oRecord->emailValidationCode = null;
                }

                return true;
        }

        public function postRetrieve(Datastore $oDB, Datastore_Record $oRecord)
        {
                if ($oRecord->dateEmailValidated !== null)
                {
                        $oRecord->emailValidated = true;
                }
                else
                {
                        $oRecord->emailValidated = false;
                }
        }
}

?>