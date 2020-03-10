<?php
declare(strict_types = 1);
namespace IchHabRecht\Filefill\Resource\Domain;

/*
 * This file is part of the TYPO3 extension filefill.
 *
 * (c) Nicole Cordes <typo3@cordes.co>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use IchHabRecht\Filefill\Resource\RemoteResourceInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DomainResource implements RemoteResourceInterface
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $configuration
     */
    public function __construct($configuration)
    {
        $this->url = rtrim((string)$configuration, '/') . '/';
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return bool
     */
    public function hasFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        $report = [];
        GeneralUtility::getUrl($this->url . ltrim($filePath, '/'), 2, false, $report);

        $isCurlResponse = in_array($report['lib'], ['cURL', 'GuzzleHttp'], true)
            && (
                (empty($report['http_code']) && (int)$report['error'] === 200)
                || (int)$report['http_code'] === 200
            );
        $isSocketResponse = $report['lib'] === 'socket' && $report['error'] === 0;

        return $isCurlResponse || $isSocketResponse;
    }

    /**
     * @param string $fileIdentifier
     * @param string $filePath
     * @param FileInterface|null $fileObject
     * @return resource|string
     */
    public function getFile($fileIdentifier, $filePath, FileInterface $fileObject = null)
    {
        $fileName = $this->url . ltrim($filePath, '/');

        return @fopen($fileName, 'r') ?: GeneralUtility::getUrl($fileName, 0, false);
    }
}
