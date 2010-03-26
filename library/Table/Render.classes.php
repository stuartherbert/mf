<?php

// ========================================================================
//
// components/Render/Render.classes.php
//              Classes defined by the Render component
//
//              Part of the Modular Framework for PHP applications
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007 Stuart Herbert
//              All rights reserved
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-08-15   SLH     Consolidated from individual files
// ========================================================================

class Render_Messages
{
        protected static $messages     = array();
        protected static $errorCount   = 0;

        function addMessage($message)
        {
                // special case - do not add empty messages
                if ($message === null || strlen($message) == 0)
                        return;

                self::$messages[] = array
                (
                        'class' => 'message',
                        'msg'   => $message,
                );
        }

        function addError($message)
        {
                // special case - do not add empty messages
                if ($message === null || strlen($message) == 0)
                {
                        return;
                }

                self::$messages[] = array
                (
                        'class' => 'error',
                        'msg'   => $message,
                );

                self::$errorCount++;
        }

        function toXhtml()
        {
                $return = '';

                if (count(self::$messages) == 0)
                {
                        return $return;
                }

                if (self::getErrorCount() > 0)
                {
                	$return .= '<p class="formInstructions">'
                                .  LANG_RENDER_MESSAGES_ERROR_INSTRUCTIONS
                                . '</p>';
                }

                $return .= '<ul class="formMessages">';

                foreach (self::$messages as $message)
                {
                        $return .= '<li class="' . $message['class'] . '">'
                                . $message['msg']
                                . "</li>\n";
                }

                $return .= "</ul>\n";

                return $return;
        }

        function getCount()
        {
                return count(self::$messages);
        }

        function getErrorCount()
        {
                return self::$errorCount;
        }
}

class Render_DataView
{
        const TOP       = 1;
        const BOTTOM    = 2;
        const BOTH      = 3;

        protected $aViews       = array();
        protected $pagerView    = 3;
        protected $viewsView    = 3;
        protected $rowCallback  = null;
        protected $viewKey      = 'view';

        public function __construct()
        {
                $this->pagerView = Render_DataView::BOTH;
        }

        public function showPagerAtTop()
        {
                $this->pagerView |= Render_DataView::TOP;
        }

        public function dontShowPagerAtTop()
        {
                $this->pagerView ^= Render_DataView::TOP;
        }

        public function showPagerAtBottom()
        {
                $this->pagerView |= Render_DataView::BOTTOM;
        }

        public function dontShowPagerAtBottom()
        {
                $this->pagerView ^= Render_DataView::PAGER_BOTTOM;
        }

        public function showViewsAtTop()
        {
                $this->viewsView |= Render_DataView::TOP;
        }

        public function dontViewsPagerAtTop()
        {
                $this->viewsView ^= Render_DataView::TOP;
        }

        public function showViewsAtBottom()
        {
                $this->viewsView |= Render_DataView::BOTTOM;
        }

        public function dontShowViewsAtBottom()
        {
                $this->viewsView ^= Render_DataView::BOTTOM;
        }

        public function newView($view)
        {
                $this->aViews[$view] = array();
        }

        public function requireView($view)
        {
                if (!isset($this->aViews[$view]))
                {
                        throw new Render_E_NoSuchView($view);
                }
        }

        public function addFieldToView($view, Model_Defintion $oDef, $fieldName, $columnHeader, $aProperties = array())
        {
                // make sure the field we're adding really does exist
                $this->requireView($view);
                $oDef->requireValidFieldName($fieldName);

                // we deliberately do not validate the properties, because
                // there is no single definitive list of valid properties
                // anywhere

                // add this field to the view
                $this->aViews[$view][$fieldName] = array
                (
                        'name'          => $fieldName,
                        'type'          => Render_Table::TYPE_DATA,
                        'header'        => $columnHeader,
                        'properties'    => $aProperties
                );
        }

        public function addSeparatorToView($view)
        {
                $this->requireView($view);

                $fields = count($this->aViews[$view]) + 1;
                $this->aViews[$view]['C' . $fields] = array
                (
                        'name'          => 'C' . $fields,
                        'type'          => Render_DataView::TYPE_BLANK
                );
        }

        public function setRowCallback($method)
        {
                $this->szRowCallback = $method;
        }

        public function toXhtml(Datastore_QueryResult $oResult, $view = 'Full', $cssClass = '')
        {
                // is the view valid?
                $this->requireView($view);

                // create the iterator
                $oIter = $a_oResult->getIter();

                // create the pager
                $oPager = new Render_PageSelector(
                        $oResult->pageNo,
                        $oResult->rowsThisPage,
                        $oResult->rowsPerPage,
                        $oResult->rowsOnAllPages
                );

                $return = '';

                // display the pager at the top, if requested
                if ($this->pagerView & Render_DataView::TOP)
                {
                        $return .= '<div class="pager">'
                                . $oPager->toXhtml()
                                . "</div>\n";
                }

                if ($this->viewsView & Render_DataView::TOP)
                {
                        $return .= '<div class="views">'
                                . $this->xhtml_views($view)
                                . "</div>\n";
                }

                // output the table
                $oTable = new Render_Table($this->aViews[$view]);
                $oTable->setRowCallback($this->rowCallback);

                $return .= '<table class="' . $cssClass . '">'
                        .  $oTable->xhtml_header();

                // display each row in turn
                foreach ($oIter as $aRow)
                {
                        $return .= $oTable->xhtml_row($aRow);
                }

                $return .= "</table>\n";

                // display the pager again
                if ($this->viewsView & Render_DataView::BOTTOM)
                {
                        $return .= '<div class="views">'
                                . $this->xhtml_views($view)
                                . "</div>\n";
                }

                if ($this->pagerView & Render_DataView::BOTTOM)
                {
                        $return .= '<div class="pager">'
                                . $oPager->toXhtml()
                                . "</div>\n";
                }


                return $return;
        }

        function xhtml_views($currentView)
        {
                if (count($this->aViews) < 2)
                        return '';

                $baseQuery = strip_query_string_of(array($this->viewKey));
                $append    = false;

                $return = 'View: ';

                foreach ($this->aViews as $view => $aDummy)
                {
                        if ($append)
                        {
                                $return .= ' | ';
                        }
                        $append = true;

                        if ($view != $currentView)
                        {
                                $query = add_to_query_string($baseQuery, array($this->viewKey => $view));

                                $return .= '<a href="?' . $query . '">'
                                        . $view
                                        . "</a>\n";
                        }
                        else
                        {
                                $return .= '<span class="currentView">'
                                        . $view
                                        . "</span>";
                        }
                }

                return $return;
        }
}

class Render_PageSelector
{
        // which page are we on?
        protected $page        = 0;

        // how many pages are there in total?
        protected $totalPages  = 0;

        // what do we use in the query string to select a page?
        protected $pageKey     = null;

        // how many page links are we going to render?
        protected $pagesToShow = 10;

        // how many records are there this page?
        protected $resultsThisPage = 0;

        // how many records are there per page?
        protected $resultsPerPage = 0;

        // how many total records are there?
        protected $totalResults = 0;

        public function __construct
        (
                $page,
                $resultsThisPage,
                $resultsPerPage,
                $totalResults,
                $pageKey = 'page'
        )
        {
                constraint_mustBeInteger($page);
                constraint_mustBeInteger($resultsThisPage);
                constraint_mustBeInteger($resultsPerPage);
                constraint_mustBeInteger($totalResults);

                $this->page            = $page;
                $this->resultsThisPage = $resultsThisPage;
                $this->resultsPerPage  = $resultsPerPage;
                $this->totalResults    = $totalResults;
                $this->pageKey         = $pageKey;

                $this->totalPages = $totalResults / $resultsPerPage;
                if ($totalResults % $resultsPerPage > 0)
                        $this->totalPages++;
        }

        public function toXhtml()
        {
                // step 1: do we have any pages to render?
                if ($this->totalPages < 1)
                {
                        return LANG_RENDER_NO_PAGES;
                }

                // step 2: do we only have the one page to render?
                if ($this->totalPages == 1)
                {
                        return sprintf(LANG_RENDER_SHOWING_ALL_RESULTS, $this->totalResults);
                }

                // if we get here, then we have a set of pages to render
                $totalPages = $this->totalPages;
                $startPage  = 1;
                $endPage    = $totalPages;

                // do we have too many pages?
                // we show up to ten pages at a time

                if ($totalPages > 10)
                {
                        $startPage = $this->page - 5;
                        if ($startPage < 1)
                                $startPage = 1;

                        $endPage = $startPage + 9;

                        if ($endPage > $totalPages)
                        {
                                $endPage   = $totalPages;
                                $startPage = $endPage - 9;
                        }
                }

                $startRecord = (($this->page - 1) * $this->resultsPerPage) + 1;
                $endRecord   = $startRecord + $this->resultsThisPage - 1;

                // work out our query string
                $baseQuery = strip_query_string_of(array($this->pageKey));

                // build up the list of pages
                $return = sprintf(LANG_RENDER_SHOWING_RESULTS_OF, ($startRecord - $endRecord), $this->totalResults)
                        . ' ' . LANG_RENDER_PAGE_LIST;
                $append = false;

                // if we have too many pages, then add a link to the first
                // page in the list

                if ($startPage > 1)
                {
                        $query  = add_to_query_string($baseQuery, array($this->pageKey => 1));
                        $return .= "<a href=\"?$query\">&lt;&lt;</a>";
                        $append = true;
                }

                // build up the list of pages

                for ($i = $startPage; $i <= $endPage; $i++)
                {
                        if ($append)
                                $return .= " | ";

                        $append = true;

                        if ($i == $this->page)
                        {
                                $return .= "<span class=\"currentPage\">" . $i . "</span>";
                        }
                        else
                        {
                                $query   = add_to_query_string($baseQuery, array($this->pageKey => $i));
                                $return .= "<a href=\"?$query\">$i</a>";
                        }
                }

                // if we have too many pages, add a link to the last page
                // in the list

                if ($endPage < $totalPages)
                {
                        $return .= " | ";
                        $query   = add_to_query_string($baseQuery, array($this->pageKey => $totalPages));
                        $return .= "<a href=\"?$query\">&gt;&gt;</a>";
                        $append  = true;
                }

                return $return;
        }
}

class Render_Table
{
        const DIRECTION_START           = 0;
        const DIRECTION_DOWN            = 1;
        const DIRECTION_ACROSS          = 2;
        const DIRECTION_END             = 3;

        const TYPE_START                = 0;
        const TYPE_DATA                 = 1;
        const TYPE_SEPARATOR            = 2;
        const TYPE_BLANK                = 3;
        const TYPE_END                  = 4;

        protected $aFields              = array();
        protected $alternateRow         = 0;
        protected $alternateRows        = true;
        protected $headerCss            = "tableheader";
        protected $direction            = Render_Table::DIRECTION_DOWN;
        protected $columns              = 0;

        protected $rowCallback          = null;

        public function __construct($aFields)
        {
                $this->aFields = $aFields;
                $this->columns = count($aFields);
        }

        public function setDisplayDirection ($direction)
        {
                constraint_mustBeGreaterThan($direction, Render_Table::DIRECTION_START);
                constraint_mustBeLessThan   ($direction, Render_Table::DIRECTION_END);

                $this->direction = $direction;
        }

        public function setAlternatingRows($alternate = true)
        {
                constaint_mustBeBoolean($alternate);

                $this->alternateRows = $alternate;
        }

        public function setHeaderCss ($cssClass)
        {
                constraint_mustBeString($cssClass);

                $this->headerClass = $cssClass;
        }

        public function setRowCallback($method)
        {
                $this->rowCallback = $method;
        }

        public function xhtml_column ($columnNo, $row, $aColumn, $aOptions = array())
        {
                // are we the first column?

                if ($columnNo == 0)
                {
                        if ($this->alternateRows)
                        {
                                $this->alternateRow = (1 - $this->alternateRow);
                        }

                        $this->xhtml_separator();
                        echo "<td class=\"" . $this->headerCss . "\" nowrap><p align=\"right\">" . $this->aFields[$row]['header'] . ":</p></td>\n";
                        $this->xhtml_separator();
                }

                $aFieldOptions = $this->aFields[$row];
                if (isset($aOptions[$row]))
                        $aFieldOptions = array_merge($aFieldOptions, $aOptions[$row]);

                if (!isset($aFieldOptions['type']))
                        $aFieldOptions['type'] = Render_Table::TYPE_DATA;

                switch ($aFieldOptions['type'])
                {
                        case Render_Table::TYPE_DATA:
                                $this->xhtml_dataCell($this->row, $row, $aFieldOptions, $aColumn);
                                break;

                        case Render_Table::TYPE_SEPARATOR:
                                $this->xhtml_separator();
                                break;
                }

                // are we at the end of the row?

                $columnNo++;
                if ($columnNo == $this->columns)
                        echo "<td class=\"" . $this->headerCss . "\" width=\"1\"></td>\n";
        }

        public function xhtml_header ()
        {
                if ($this->direction == Render_Table::DIRECTION_DOWN)
                {
                        $output = "<tr>\n";
                        foreach ($this->aFields as $index => $aOptions)
                        {
                                $output .= "<th class=\"" . $this->headerCss . "\"";

                                if (isset($aOptions['nowrap']) && $aOptions['nowrap'])
                                        $output .= " nowrap";

                                $output .= ">" . $aOptions['header'] . "</th>";
                        }
                        $output .= "</tr>\n";

                        return $output;
                }
        }

        public function xhtml_row ($aRow, $aOptions = array())
        {
                if ($this->alternateRows)
                {
                        $this->alternateRow = (1 - $this->alternateRow);
                }

                // prepare the row for display
                if (isset($this->rowCallback))
                {
                        $func = $this->rowCallback;
                        $func($aRow, $aOptions);
                }

                $output = "<tr class=\"tableRow" . $this->alternateRow . "\">\n";
                foreach ($this->aFields as $index => $aFieldOptions)
                {
                        if (isset($aOptions[$index]))
                        {
                                $aFieldOptions = array_merge($aFieldOptions, $aOptions[$index]);
                        }

                        if (!isset($aFieldOptions['type']))
                                $aFieldOptions['type'] = Render_Table::TYPE_DATA;

                        switch ($aFieldOptions['type'])
                        {
                                case Render_Table::TYPE_DATA:
                                        $output .= $this->xhtml_dataCell($index, $aFieldOptions, $aRow);
                                        break;

                                case Render_Table::TYPE_SEPARATOR:
                                        $output .= "<td class=\"" . $this->headerCss . "\" width=\"1\"></td>\n";
                                        break;

                                case Render_Table::TYPE_BLANK:
                                        $output .= "<td";
                                        if (isset($aFieldOptions['colspan']))
                                                $output .= " colspan=\"" . $aFieldOptions['colspan'] . "\"";
                                        $output .= ">&nbsp;</td>";
                                        break;
                        }
                }
                $output .= "</tr>\n";

                return $output;
        }

        public function xhtml_separator ()
        {
                return "<td class=\"" . $this->headerCss . "\" width=\"1\"></td>\n";
        }

        protected function xhtml_dataCell ($index, $aOptions, $aRow)
        {
                // some sanity checking

                if (!isset($aOptions['align']))
                        $aOptions['align'] = 'left';

                if (!isset($aOptions['valign']))
                        $aOptions['valign'] = 'top';

                // display the start of the cell
                //
                // we append the value of $a_iRow to the css class for
                // this cell; this allows us to span multiple cells

                $output = "<td class=\"" . $aOptions['css'];

                if ($this->alternateRows)
                        $output .= $this->alternateRow;

                // close the class= attribute
                $output .= "\"";

                // add the vertical alignment
                $output .= ' valign="' . $aOptions['valign'] . '"';

                // do we need to ensure that the cell never wraps?
                if (isset($aOptions['nowrap']) && ($aOptions['nowrap']))
                        $output .= " nowrap";

                // does this cell span multiple columns?
                if (isset($aOptions['colspan']))
                        $output .= " colspan=\"" . $aOptions['colspan'] . "\"";

                // okay, we're done trying to find options for the cell
                $output .= "><p align=\"" . $aOptions['align'] . "\"";

                // does this cell require a particular CSS class?
                if (isset($aOptions['pclass']))
                        $output .= ' class="' . $aOptions['pclass'] . '"';

                // we're finished with the paragraph style
                $output .= ">";

                // if this cell contains a hyperlink, now is the time to
                // add the opening <a> tag

                if (isset($aOptions['link']))
                {
                        $output .= "<a href=\"" . $aOptions['link'] . "\">";
                }

                // how do we format this data?
                //
                // we currently support three ways of formatting the data
                //
                // a) the data is a UNIX timestamp, which needs feeding
                //    into date()
                // b) the data needs formatting via sprintf()
                // c) the data can be displayed as-is

                $output .= $aRow[$index];

                // if this cell contains a hyperlink, now is the time to
                // close it
                if (isset($aOptions['link']))
                {
                        $output .= "</a>";
                }

                // time to close the cell
                $output .= "</p></td>\n";

                return $output;
        }

        public function xhtml_emptyLine ($cssClass = null)
        {
                if ($cssClass === NULL)
                        $cssClass = $this->headerCss;

                $cols    = 0;
                $inEmpty = false;
                $output  = '<tr>';

                foreach ($this->aFields as $index => $aFieldOptions)
                {
                        if (!isset($aFieldOptions['type']))
                                $aFieldOptions['type'] = Render_Table::TYPE_DATA;

                        switch ($aFieldOptions['type'])
                        {
                                case Render_Table::TYPE_DATA:
                                case Render_Table::TYPE_SEPARATOR:
                                        if (isset($aFieldOptions['colspan']))
                                                $cols += $aFieldOptions['colspan'];
                                        else
                                                $cols++;

                                        break;

                                case Render_Table::TYPE_BLANK:
                                        if ($cols > 0)
                                        {
                                                $output .= "<td class=\"$cssClass\" colspan=\"$cols\" height=\"3\"></td>\n";
                                                $cols    = 0;
                                        }

                                        $output .= "<td";
                                        if (isset($aFieldOptions['colspan']))
                                                $output .= ' colspan="' . $aFieldOptions['colspan'] . '"';

                                        $output .= ' height="3"></td>';
                                        break;
                        }
                }

                // do we have any undrawn cells left?

                if ($cols > 0)
                        $output .= "<td class=\"$cssClass\" colspan=\"$cols\" height=\"3\"></td>\n";

                $output .= "</tr>\n";

                return $output;
        }
}

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