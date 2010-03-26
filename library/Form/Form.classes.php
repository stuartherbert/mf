<?php

// ========================================================================
//
// Form/Form.classes.php
//              Classes defined by the Form component
//
//              Part of the Methodosity Framework for PHP applications
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
// 2007-08-15   SLH     Consolidated from individual files
// 2009-04-16   SLH     Moved from obsolete Render component
// ========================================================================

// ========================================================================
// Support for rendering a form
// ------------------------------------------------------------------------

// represents an individual form field
//
// form fields are normally linked to a field defined by a model

class Render_FormField
{
	public $oDef = null;

        public function xhtml_label()
        {

        }

        public function xhtml_field()
        {

        }
}

class Render_Form implements Iterator
{
	public function addField($modelName, $fieldName, $label = null)
        {
                $this->aFields[$fieldName] = array
                (
                        'model' => $modelName,
                        'name'  => $fieldName,
                        'label' => $label,
                );
        }

        // ================================================================
        // Interface: Iterator
        // ----------------------------------------------------------------

        public function rewind ()
        {
                reset($this->aFields);
                list($this->iterKey, ) = each ($this->aFields);
        }

        public function valid()
        {
                if (!isset($this->aFields[$this->iterKey]))
                        return false;

                return true;
        }

        public function key()
        {
                return $this->iterKey;
        }

        public function current()
        {
                return $this->aFields[$this->iterKey];
        }

        public function next()
        {
                list($this->iterKey, ) = each ($this->aFields);
                return $this->valid();
        }


}

// ========================================================================
// Support for multi-page forms
// ------------------------------------------------------------------------

class Render_Wizard
{
        public    $wizardName = null;

	protected $aSteps     = array();
        protected $aModels    = array();

        public function __construct($wizardName)
        {
                constraint_mustBeString($wizardName);
                $this->wizardName = $wizardName;
        }

        public function addModel($alias, $model)
        {
                $this->aModels[$alias] = $model;

                // we also make the model available via the alias, to
                // make it nicer to use in forms
                $this->$alias = $model;
        }

        public function addStep(Render_WizardStep $oStep)
        {
        	$class = get_class($oStep);
                $this->aSteps[$class] = $oStep;
                $oStep->oWizard       = $this;
        }

        public function setCurrentStep($step)
        {
        	$this->requireValidStep($step);
                $this->oCurrentStep = $this->aSteps[$step];
        }

        public function requireValidStep($step)
        {
        	if (!isset($this->aSteps[$step]))
                {
                	throw new Render_E_NoSuchWizardStep($this->wizardName, $step);
                }
        }

        public function markAllFieldsAsValid()
        {
        	$this->invalidFields = array();
        }

        public function markAsInvalid($alias, $field)
        {
        	$this->invalidFields[$alias][$field] = true;
        }

        public function getCssClass($alias, $field)
        {
        	if (isset($this->invalidFields[$alias][$field])
                    && $this->invalidFields[$alias][$field])
                {
                	return 'wizardInvalid';
                }

                return 'wizardValid';
        }

        public function process()
        {
                // step 1: validate any POST data we already have

                $this->validatePostData();

                // step 2: work out what the current step is

                $this->determineCurrentStep();

                // step 3: give the next step the opportunity to
                //         auto-generate any model data required

                $this->oCurrentStep->processBeforeRender();

                // and we're done for now
        }

        public function render()
        {
        	$this->oCurrentStep->toXhtml();
        }

        public function getModel()
        {
                $aData = array();

        	foreach ($this->aModels as $modelName => $model)
                {
                	foreach ($model as $field => $value)
                        {
                                // var_dump($modelName . ' :: ' . $field . ' :: ' . $value);
                        	$aData[$modelName][$field] = $value;
                        }
                }

                $return = base64_encode(serialize($aData));
                $return = md5(COOKIE_SECRET . $return)
                        . $return;

                return $return;
        }

        public function validatePostData()
        {
        	global $oConfig;

                // do we have the form field that holds our data model?
                if (!isset($_POST['model']))
                {
                        // FIXME: we should throw an exception here if
                        //        we are expecting a model but it is
                        //        missing
                	return;
                }

                // try to decode it
                $md5   = substr($_POST['model'], 0, 32);
                $model = substr($_POST['model'], 32);
                if (md5(COOKIE_SECRET . $model) != $md5)
                {
                	throw new Render_E_InvalidModel();
                }

                $modelFields = unserialize(base64_decode($model));

                // set the values in the models
                foreach ($modelFields as $model => $fields)
                {
                	foreach ($fields as $fieldName => $fieldValue)
                        {
                                // FIXME: add some code to check and make
                                //        sure the model exists

                                // FIXME: add some code to take advantage
                                //        of the PHP filter extension
                        	$this->aModels[$model]->$fieldName = $fieldValue;
                        }
                }
        }

        public function determineCurrentStep()
        {
                global $oResponse;

        	// do we have anything in the form to help us?
                if (!isset($_POST['thisStep']))
                {
                        // no, so set current step to be the first
                        // step registered
                        reset($this->aSteps);
                	$this->oCurrentStep = current($this->aSteps);
                        return;
                }

                // does the form refer to a valid step?

                if (isset($this->aSteps[$_POST['thisStep']]))
                {
                	$this->oCurrentStep = $this->aSteps[$_POST['thisStep']];
                        $this->oCurrentStep->processAfterSubmit();

                        // has the next button been pressed?
                        if (isset($_POST['next']))
                        {
                                $this->oCurrentStep->validateFields();
                                if ($oResponse->oMessages->getErrorCount() == 0)
                                {
                                        $this->oCurrentStep->setNextStep();
                                }
                        }
                        else if (isset($_POST['back']))
                        {
                        	// the back button has been pressed
                                $this->oCurrentStep->setBackStep();
                        }

                        return;
                }

                // the form refers to an invalid step

                throw new Render_E_NoSuchWizardStep($this->wizardName, $_POST['thisStep']);
        }
}

class Render_WizardStep
{
        public    $oWizard      = null;
        protected $nextLabel    = null;
        protected $backLabel    = null;

        public function processBeforeRender()
        {
        	// this method is called before the form is displayed
                //
                // override this method if you need to auto-calculate
                // any field values before the form is rendered
        }

        public function processAfterSubmit()
        {
        	// this method is called after the form has been
                // submitted
                //
                // override this method to perform any checks that need
                // to be done on the data that has been submitted
        }

        public function validateFields()
        {
        	// documentation here
        }

        public function setNextButtonIs($label = LANG_RENDER_NEXT)
        {
        	$this->nextLabel = $label;
        }

        public function setNextButtonIsSummary()
        {
        	$this->nextLabel = LANG_RENDER_SUMMARY;
        }

        public function setNextButtonIsFinish()
        {
        	$this->nextLabel = LANG_RENDER_FINISH;
        }

        public function setBackButtonIs($label = LANG_RENDER_BACK)
        {
        	$this->backLabel = $label;
        }

        public function setNoBackButton()
        {
        	$this->backLabel = null;
        }

        public function toXhtml()
        {
        	// default behaviour is to render the form provided by
                // the getForm() method, followed by the next/previous
                // controls at the bottom of the page
                //
                // override this if you really feel the need to do
                // something different here

                echo '<div id="wizardForm">' . "\n"
                     . '<form method="post" class="cmxform">' . "\n"
                     . '<div id="wizardFormFields">' . "\n";

                $oWizard = $this->oWizard;
                include $this->getForm();

                echo "\n</div>\n";

                // render the form controls
                $this->xhtml_form_controls();

                // now, close off the form
                echo '</form></div>';
        }

        public function xhtml_form_controls()
        {
                // render the form controls

                echo '<div id="wizardFormControls">' . "\n";
                echo '<input type="hidden" name="thisStep" value="' . get_class($this) . '"/>';

                if ($this->hasBackStep())
                {
                        echo '<input type="submit" name="back" value="' . htmlentities($this->backLabel) . '"/>';
                        echo '&nbsp;';
                }

                echo '<input type="submit" name="next" value="' . htmlentities($this->nextLabel) . '"/>';

                echo '</div>' . "\n";
        }
        // ================================================================

        public function hasBackStep()
        {
        	return ($this->backLabel != null);
        }
}

?>