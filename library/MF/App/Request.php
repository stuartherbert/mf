<?php

/**
 * Methodosity Framework
 *
 * LICENSE
 *
 * Copyright (c) 2010 Stuart Herbert
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   MF
 * @package    MF_App
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

class MF_App_Request
{
        /**
         * The URL of the homepage of our app.  All other URLs for our
         * app sit beneath this one
         *
         * @var string
         */
        public $baseUrl      = null;

        // holds the path the user has requested, which we later
        // decode to determine which class to route the request to
        public $pathInfo     = null;

        /**
         * What route are we being asked to process?
         *
         * @var Routing_Route
         */
        public $currentRoute = null;

        /**
         * What type of content is the user asking for?
         *
         * this is set automatically by the constructor, but the AnonApi
         * and Api classes will override this based on any format parameter
         * included in the URL
         *
         * @var string
         */
        public $requestedContentType = null;

        // the different types of content that can be requested
        const CT_XHTML   = 1;
        const CT_XML     = 2;
        const CT_JSON    = 3;
        const CT_PHP     = 4;
        const CT_CONSOLE = 5;

        protected $contentTypeNames = array
        (
                CT_XHTML   => 'xhtml',
                CT_XML     => 'xml',
                CT_JSON    => 'json',
                CT_PHP     => 'php',
                CT_CONSOLE => 'term',
        );

        public function __construct()
        {
                $this->baseUrl              = $this->determineBaseUrl();
                $this->pathInfo             = $this->determinePathInfo();
                $this->requestedContentType = $this->determineContentType();
        }

        public function determineBaseUrl()
        {
                // the SCRIPT_NAME is always <url>/index.php, which is
                // why this works at all :)
                return dirname($_SERVER['SCRIPT_NAME']);
        }

        public function determinePathInfo()
        {
                $publicDir = dirname($_SERVER['SCRIPT_NAME']) . '/';
                $strippedPath = str_replace($publicDir, '', $_SERVER['REDIRECT_URL']);
                if ($strippedPath[0] != '/')
                {
                        $strippedPath = '/' . $strippedPath;
                }

                return $strippedPath;
        }

        /**
         * work out what type of content the user is asking for
         */
        public function determineContentType()
        {
                // step 1: are we in a browser at all?
                if (!isset($_SERVER))
                {
                        // no, so we must be a console app
                        return MF_App_Request::CT_CONSOLE;
                }

                // TODO: detect content negotiation properly
                return MF_App_Request::CT_XHTML;
        }
}

?>