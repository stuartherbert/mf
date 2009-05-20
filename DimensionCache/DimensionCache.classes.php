<?php

// ========================================================================
//
// DimensionCache/DimensionCache.classes.php
//              Classes defined by the DimensionCache module
//
//              Part of the Methodosity Framework for PHP applications
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-05-19   SLH     Created
// ========================================================================

interface DimensionCache_PublicCacheable
{
        public function loadFromOriginalSources();
        public function loadFromPublicCache();
        public function saveToPublicCache();
}

/**
 * simple class to avoid loading source files if the cached data is
 * up to date
 *
 * useful for things like caching the routes defined by the available
 * modules
 */

class DimensionCache_FileCache
{
        /**
         *
         * @var string
         */
        public $filename = null;

        public function __construct($cacheName)
        {
                constraint_mustBeString($cacheName);

                $this->filename = APP_TOPDIR . '/cache/' . $cacheName . '.cache.php';
        }

        public function loadOrRefreshCache(DimensionCache_PublicCacheable $obj, $sourcesList)
        {
                if ($this->isCacheCurrent($sourcesList))
                {
                        $obj->loadFromPublicCache();
                }
                else
                {
                        $obj->loadFromOriginalSources();
                        $obj->saveToPublicCache();
                }
        }

        public function loadCache()
        {
                return file_get_contents($this->filename);
        }

        public function saveCache($contents)
        {
                @file_put_contents($this->filename, $contents);
        }

        public function isCacheCurrent($filesToCheck)
        {
                if (!file_exists($this->filename))
                        return false;

                $cachemtime = filemtime($this->filename);

                foreach ($filesToCheck as $fileToCheck)
                {
                        if (filemtime($fileToCheck) >= $cachemtime)
                        {
                                return false;
                        }
                }

                return true;
        }
}

?>
