<?php

// ========================================================================
//
// User/User.models.php
//              Database models for the User component
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
// 2007-07-20   SLH     Created
// 2007-07-23   SLH     Added alias field to User_Record
// 2007-07-24   SLH     Added getEntryPointRecordFor() to User_Table
// 2007-08-06   SLH     Added theme field to User_Record
// 2007-08-07   SLH     Added constructed field to User_Record
// 2007-08-07   SLH     Added firstName field to User_Record
// 2007-08-07   SLH     Added lastName field to User_Record
// 2007-08-15   SLH     Added fields for the user's address
// 2007-09-06   SLH     Added passwordsMatch() to User_Record
// 2007-09-17   SLH     User_Record::hasUniqueEmailAddress() now uses a
//                      view to reduce the number of columns returned
//                      from the datastore
// 2007-09-21   SLH     Added validation for name fields
// 2007-12-11   SLH     Added authenticated field to User_Record
// 2008-01-06   SLH     Updated to accomodate separation of Model
//                      and Database_Record classes
// 2008-10-16   SLH     More updates to accomodate changes to how Models
//                      are defined
// 2009-03-25   SLH     Split User up into a core model + optional
//                      extensions for more flexibility
// 2009-03-25   SLH     Moved theme support into separate extension
// 2009-03-30   SLH     User_Password_Ext now implements Model_Extension
// 2009-03-31   SLH     Added authType field to generic User model
// 2009-05-20   SLH     Added User_VerifiedEmail_Ext
// 2009-06-04   SLH     Updated to work with the generic mixin API
// ========================================================================

class User extends Model
{
        // ----------------------------------------------------------------
        // constants for how the user has been authenticated

        /**
         * The user has not been authenticated at all
         */
        const AUTHTYPE_ANON              = 0;

        /**
         * The user was previously logged in through the website
         */
        const AUTHTYPE_WEBUSER           = 1;

        /**
         * The user is attempting to use an API
         */
        const AUTHTYPE_APIUSER           = 2;

        /**
         * The user is expressly using the OAuth approach
         */
        const AUTHTYPE_OAUTH             = 3;

        /**
         * The user is browsing us from Facebook, and we are a regular
         * Facebook appp
         */
        const AUTHTYPE_FACEBOOK_PLATFORM = 4;

        /**
         * The user is a Facebook user, and we are using Facebook Connect
         */
        const AUTHTYPE_FACEBOOK_CONNECT  = 5;

        /**
         * the fake field 'authenticated' is a partial alias for the
         * authType field
         * 
         * @return boolean
         */
        public function getAuthenticated()
        {
                if ($this->aData['authType'] !== User::AUTHTYPE_ANON)
                {
                        return true;
                }

                return false;
        }

        public function setAuthenticated($authType)
        {
                $this->authType = $authType;
        }

        public function issetAuthenticated()
        {
                return isset($this->authType);
        }

        /**
         * Automagically called whenether $this->authType is set
         *
         * @param int $authType
         */
        protected function setAuthType($authType)
        {
                $this->aData['authType'] = $authType;

                $objs = $this->findObjsForMethod('postAuth');
                foreach ($objs as $obj)
                {
                        $obj->postAuth($authType);
                }
        }

        public function validateRegistration(Datastore $oDB)
        {
                $return = array();
                $objs   = $this->findObjsForMethod('validateRegistration');

                foreach ($objs as $obj)
                {
                        var_dump('Calling ' . get_class($obj) . '::validateRegistration()');
                        $results = $obj->validateRegistration($oDB);
                        foreach ($results as $result)
                        {
                                $return[] = $result;
                        }
                }

                if (count($return) > 0)
                {
                        // validation failed ... tell the user why
                        var_dump($return);
                }
        }
}

// define the minimum fields for a user
$oDef = Model_Definitions::get('User');
$oDef->addField('uid');
$oDef->addField('username');
$oDef->addFakeField('authType')
     ->setDefaultValue(User::AUTHTYPE_ANON);
$oDef->addFakeField('authenticated');
$oDef->addField('alias');
$oDef->addField('constructed')
     ->setDefaultValue(0);
$oDef->setPrimaryKey('uid');

// this is used by App::loadThemeEngine to determine whether the user
// can personalise their theme or not
//
// the mainLoop() of the various App_Engines can choose to use the user
// theme or not as appropriate
//
// the User_Theme_Ext extension changes the default value of this field
$oDef->addFakeField('supportsThemePref')
     ->setDefaultValue(false);

// ========================================================================
// Extensions to the original User model

class User_Address_Ext 
        extends Obj_Mixin
        implements Model_Extension
{
        const E_NO_ADDRESS1                     = 'E_NoAddress1';
        const E_NO_ADDRESSCITY                  = 'E_NoAddressCity';

        static public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('address1');
                $oDef->addField('address2');
                $oDef->addField('address3');
                $oDef->addField('addressCity');
                $oDef->addField('addressState');
                $oDef->addField('addressPostcode');
                $oDef->addField('addressCountry');
        }

        public function validateAddress()
        {
        	// we can return one or more error codes!
                $return = array();

                // do we have the first line of the address?
                if (!isset($this->address1) || strlen(trim($this->address1)) == 0)
                {
                	$return[] = User_Address_Ext::E_NO_ADDRESS1;
                }

                // do we have the town/city?
                if (!isset($this->addressCity) || strlen(trim($this->addressCity)) == 0)
                {
                	$return[] = User_Address_Ext::E_NO_ADDRESSCITY;
                }

                // do we have the county / state?
                if (!isset($this->addressState) || strlen(trim($this->addressState)) == 0)
                {
                	$return[] = User_Address_Ext::E_NO_ADDRESSSTATE;
                }

                // what about the postcode?
                if (!isset($this->addressPostcode) || strlen(trim($this->addressPostcode)) == 0)
                {
                	$return[] = User_Address_Ext::E_NO_ADDRESSPOSTCODE;
                }

                // and what about the country?
        }

        public function validateRegistration()
        {
                return $this->validateAddress();
        }
}

class User_Email_Ext
        extends Obj_Mixin
        implements Model_Extension
{
        const E_NO_EMAILADDRESS                 = 'E_NoEmailAddress';
        const E_INVALID_EMAILADDRESS            = 'E_InvalidEmailAddress';
        const E_NO_CONFIRMEMAILADDRESS          = 'E_NoConfirmEmailAddress';
        const E_EMAILADDRESSES_DIFFERENT        = 'E_EmailAddressesDifferent';
        const E_EMAILADDRESS_INUSE              = 'E_EmailAddressInUse';

        static public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('emailAddress');
                $oDef->addFakeField('confirmEmailAddress');

                $oDef->addView('emailAddress')
                     ->withField('uid')
                     ->withField('emailAddress');
        }

        public function validateRegistration(Datastore $oDB)
        {
                return $this->validateEmailAddress($oDB);
        }

        public function validateEmailAddress(Datastore $oDB)
        {
                // we can return one or more error codes!
                $return = array();

        	// step 1: do we have an email address to validate?

                if (!$this->emailAddress)
                {
                	$return[] = User_Email_Ext::E_NO_EMAILADDRESS;
                }
                else
                {
                        if (!$this->hasValidEmailAddress($oDB))
                        {
                                $return[] = User_Email_Ext::E_INVALID_EMAILADDRESS;
                        }
                        else if (!$this->hasUniqueEmailAddress($oDB))
                        {
                        	$return[] = User_Email_Ext::E_EMAILADDRESS_INUSE;
                        }
                }

                // step 2: do we have a confirm email address to validate?

                if (!$this->confirmEmailAddress)
                {
                	$return[] = User_Email_Ext::E_NO_CONFIRMEMAILADDRESS;
                }
                else if (!$this->emailAddressesMatch())
                {
                	$return[] = User_Email_Ext::E_EMAILADDRESSES_DIFFERENT;
                }

                return $return;
        }

        public function hasValidEmailAddress()
        {
        	try
                {
                	constraint_mustBeEmailAddress($this->emailAddress);
                }
                catch (PHP_E_ConstraintFailed $e)
                {
                	return false;
                }

                return true;
        }

        public function hasUniqueEmailAddress(Datastore $oDB)
        {
                if (!isset($this->emailAddress))
                {
                        return true;
                }

                try
                {
                        $users = $oDB->newQuery()
                                 ->findFirst('User')
                                 ->withForeignKeys(array('emailAddress' => $this->emailAddress))
                                 ->go();
                }
                catch (Datastore_E_RetrieveFailed $e)
                {
                        return true;
                }

                return false;
        }

        public function emailAddressesMatch()
        {
        	if ($this->emailAddress == $this->confirmEmailAddress)
                {
                	return true;
                }

                return false;
        }
}

class User_Name_Ext
        extends Obj_Mixin
        implements Model_Extension
{
        const E_NO_FIRSTNAME = 'E_NoFirstName';
        const E_NO_LASTNAME  = 'E_NoLastName';

        static public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('firstName');
                $oDef->addField('lastName');
        }

        public function validateRegistration()
        {
                return $this->validateName();
        }

        public function validateName()
        {
        	// we can return one or more error codes!
                $return = array();

                // do we have a first name?
                if (!isset($this->firstName) || strlen(trim($this->firstName)) == 0)
                {
                	$return[] = User_Name_Ext::E_NO_FIRSTNAME;
                }

                // do we have a last name?
                if (!isset($this->lastName) || strlen(trim($this->lastName)) == 0)
                {
                	$return[] = User_Name_Ext::E_NO_LASTNAME;
                }

                return $return;
        }

        public function postAuth()
        {
                $this->firstName = 'Guest';
                $this->lastName  = 'User';
        }
}

class User_Password_Ext
        extends Obj_Mixin
        implements Model_Extension
{
        const E_BLANK_PASSWORD      = 'E_BlankPassword';
        const E_WEAK_PASSWORD       = 'E_WeakPassword';
        const E_PASSWORDS_DIFFERENT = 'E_PasswordsDifferent';

        static public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('password');
                $oDef->addFakeField('confirmPassword');
        }

        public function validateRegistration()
        {
                return $this->validatePassword();
        }
        
        public function validatePassword()
        {
                $aReturn = array();

                if ($this->hasBlankPassword())
                {
                	$aReturn[] = User_Password_Ext::E_BLANK_PASSWORD;
                }
                else if ($this->hasWeakPassword())
                {
                	$aReturn[] = User_Password_Ext::E_WEAK_PASSWORD;
                }

                if (!$this->passwordsMatch())
                {
                	$aReturn[] = User_Password_Ext::E_PASSWORDS_DIFFERENT;
                }

                return $aReturn;
        }

        public function hasBlankPassword()
        {
                if (!isset($this->password) || strlen($this->password) == 0)
                {
                        return true;
                }

                return false;
        }

        public function hasWeakPassword()
        {
        	if (strlen($this->password) < 6)
                {
                	return true;
                }

                return false;
        }

        public function passwordsMatch()
        {
        	if ($this->password == $this->confirmPassword)
                {
                	return true;
                }

                return false;
        }
}

class User_Theme_Ext 
        extends Obj_Mixin
        implements Model_Extension
{
        static public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('theme');
                $oDef->getField('supportsThemePref')
                     ->setDefaultValue(true);
        }
}

class User_VerifiedEmail_Ext 
        extends Obj_Mixin
        implements Model_Extension
{
        static public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('verified')
                     ->setDefaultValue(0);
                $oDef->addField('verificationCode');
        }

        public function setVerified($value)
        {
                $data =& $this->getData();

                if ($value)
                {
                        $data['verified'] = 1;
                        $data['verificationCode'] = null;
                }
                else
                {
                        $data['verified'] = 0;
                        $data['verificationCode'] = md5(srand(999999) . App::$config['APP_SECRET_KEY']);
                }
        }
}

// add in the additional behaviours

// $oDef->addBehaviour(new Datastore_RecordBehaviour_ChangingTimes());
// $oDef->addBehaviour(new Datastore_RecordBehaviour_Deleted());
// $oDef->addBehaviour(new Datastore_RecordBehaviour_ValidatedEmailAddress());

?>