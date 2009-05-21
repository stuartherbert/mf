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

                // now, give each extension a chance to perform whatever
                // setup they need to do
                $oDef = Model_Definitions::get('User');
                $extensions = $oDef->getExtensions();

                foreach ($extensions as $extension)
                {
                        if (method_exists($extension, 'postAuth'))
                        {
                                $extension->postAuth();
                        }
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

class User_Address_Ext implements Model_Extension
{
        const E_NO_ADDRESS1                     = 1;
        const E_NO_ADDRESSCITY                  = 2;

        public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('address1');
                $oDef->addField('address2');
                $oDef->addField('address3');
                $oDef->addField('addressCity');
                $oDef->addField('addressState');
                $oDef->addField('addressPostcode');
                $oDef->addField('addressCountry');
        }

        public function validateAddress($model)
        {
        	// we can return one or more error codes!
                $return = array();

                // do we have the first line of the address?
                if (!isset($model->address1) || strlen(trim($model->address1)) == 0)
                {
                	$return[] = User_Address_Ext::E_NO_ADDRESS1;
                }

                // do we have the town/city?
                if (!isset($model->addressCity) || strlen(trim($model->addressCity)) == 0)
                {
                	$return[] = User_Address_Ext::E_NO_ADDRESSCITY;
                }

                // do we have the county / state?
                if (!isset($model->addressState) || strlen(trim($model->addressState)) == 0)
                {
                	$return[] = User_Address_Ext::E_NO_ADDRESSSTATE;
                }

                // what about the postcode?
                if (!isset($model->addressPostcode) || strlen(trim($model->addressPostcode)) == 0)
                {
                	$return[] = User_Address_Ext::E_NO_ADDRESSPOSTCODE;
                }

                // and what about the country?
        }
}

class User_Email_Ext implements Model_Extension
{
        const E_NO_EMAILADDRESS                 = 1;
        const E_INVALID_EMAILADDRESS            = 2;
        const E_NO_CONFIRMEMAILADDRESS          = 3;
        const E_EMAILADDRESSES_DIFFERENT        = 4;
        const E_EMAILADDRESS_INUSE              = 5;

        public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('emailAddress');
                $oDef->addFakeField('confirmEmailAddress');

                $oDef->addView('emailAddress')
                     ->withField('uid')
                     ->withField('emailAddress');
        }

        public function validateEmailAddress(Datastore $oDB)
        {
                // we can return one or more error codes!
                $return = array();

        	// step 1: do we have an email address to validate?

                if (!$model->emailAddress)
                {
                	$return[] = User_Email_Ext::E_NO_EMAILADDRESS;
                }
                else
                {
                        if (!$this->hasValidEmailAddress())
                        {
                                $return[] = User_Email_Ext::E_INVALID_EMAILADDRESS;
                        }
                        else if (!$this->hasUniqueEmailAddress($oDB))
                        {
                        	$return[] = User_Email_Ext::E_EMAILADDRESS_INUSE;
                        }
                }

                // step 2: do we have a confirm email address to validate?

                if (!$model->confirmEmailAddress)
                {
                	$return[] = User_Email_Ext::E_NO_CONFIRMEMAILADDRESS;
                }
                else if (!$this->emailAddressesMatch())
                {
                	$return[] = User_Email_Ext::E_EMAILADDRESSES_DIFFERENT;
                }

                return $return;
        }

        public function hasValidEmailAddress($model)
        {
        	try
                {
                	constraint_mustBeEmailAddress($model->emailAddress);
                }
                catch (PHP_E_ConstraintFailed $e)
                {
                	return false;
                }

                return true;
        }

        public function hasUniqueEmailAddress(User $model, Datastore $oDB)
        {
                if (!isset($model->emailAddress))
                {
                        return true;
                }

                // FIXME
                $oTable   = new User_Table();
                $oMatches = $oTable->findAllBy_emailAddress($oDB, $this->emailAddress, 'emailAddress');

                if ($oMatches->getCount() > 0)
                {
                        return false;
                }

                return true;
        }

        public function emailAddressesMatch(User $model)
        {
        	if ($model->emailAddress == $model->confirmEmailAddress)
                {
                	return true;
                }

                return false;
        }
}

class User_Name_Ext implements Model_Extension
{
        const E_NO_FIRSTNAME                    = 1;
        const E_NO_LASTNAME                     = 2;

        public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('firstName');
                $oDef->addField('lastName');
        }

        public function validateName(User $model)
        {
        	// we can return one or more error codes!
                $return = array();

                // do we have a first name?
                if (!isset($model->firstName) || strlen(trim($model->firstName)) == 0)
                {
                	$return[] = User_Name_Ext::E_NO_FIRSTNAME;
                }

                // do we have a last name?
                if (!isset($model->lastName) || strlen(trim($model->lastName)) == 0)
                {
                	$return[] = User_Name_Ext::E_NO_LASTNAME;
                }

                return $return;
        }

        public function postAuth(User $model)
        {
                $model->firstName = 'Guest';
                $model->lastName  = 'User';
        }
}

class User_Password_Ext implements Model_Extension
{
        const E_BLANK_PASSWORD                  = 1;
        const E_WEAK_PASSWORD                   = 2;
        const E_PASSWORDS_DIFFERENT             = 3;

        public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('password');
                $oDef->addFakeField('confirmPassword');
        }

        public function validatePassword(User $model)
        {
                $aReturn = array();

                if ($model->passwordIsBlank())
                {
                	$aReturn[] = User_Password_Ext::E_BLANK_PASSWORD;
                }
                else if ($model->passwordIsWeak())
                {
                	$aReturn[] = User_Password_Ext::E_WEAK_PASSWORD;
                }

                if (!$model->passwordsMatch())
                {
                	$aReturn[] = User_Password_Ext::E_PASSWORDS_DIFFERENT;
                }

                return $aReturn;
        }

        public function hasBlankPassword()
        {
                if (!isset($model->password) || strlen($model->password) == 0)
                {
                        return true;
                }

                return false;
        }

        public function hasWeakPassword()
        {
        	if (strlen($model->password) < 6)
                {
                	return true;
                }

                return false;
        }

        public function passwordsMatch()
        {
        	if ($model->password == $model->confirmPassword)
                {
                	return true;
                }

                return false;
        }
}

class User_Theme_Ext implements Model_Extension
{
        public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('theme');
                $oDef->getField('supportsThemePref')
                     ->setDefaultValue(true);
        }
}

class User_VerifiedEmail_Ext implements Model_Extension
{
        public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('verified')
                     ->setDefaultValue(0);
                $oDef->addField('verificationCode');
        }

        public function setVerified($model, $value)
        {
                $data =& $model->getData();

                if ($value)
                {
                        $data['verified'] = 1;
                        $data['verificationCode'] = null;
                }
                else
                {
                        $data['verified'] = 0;
                        $data['verificationCode'] = md5(srand(1,999999) . App::$config['APP_SECRET_KEY']);
                }
        }
}

// add in the additional behaviours

// $oDef->addBehaviour(new Datastore_RecordBehaviour_ChangingTimes());
// $oDef->addBehaviour(new Datastore_RecordBehaviour_Deleted());
// $oDef->addBehaviour(new Datastore_RecordBehaviour_ValidatedEmailAddress());

?>