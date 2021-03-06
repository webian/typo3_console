<?php
namespace Helhum\Typo3Console\Extension;

/*
 * This file is part of the TYPO3 Console project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 *
 */

use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extensionmanager\Utility\EmConfUtility;

class ExtensionConstraintCheck
{
    /**
     * @param Package $package
     * @param string $typo3Version
     * @return string
     */
    public function matchConstraints(Package $package, $typo3Version = TYPO3_version)
    {
        $message = '';
        $extension = [
            'key' => $package->getPackageKey(),
            'siteRelPath' => PathUtility::stripPathSitePrefix($package->getPackagePath()),
        ];
        $extConf = (new EmConfUtility())->includeEmConf($extension);
        if (!empty($extConf['constraints']['depends']['typo3'])) {
            $versions = VersionNumberUtility::convertVersionsStringToVersionNumbers($extConf['constraints']['depends']['typo3']);
            $t3version = preg_replace('/-?(dev|alpha|beta|RC).*$/', '', $typo3Version);
            $parts = GeneralUtility::intExplode('.', $t3version . '..');
            $t3version = MathUtility::forceIntegerInRange($parts[0], 0, 999) . '.' .
                MathUtility::forceIntegerInRange($parts[1], 0, 999) . '.' .
                MathUtility::forceIntegerInRange($parts[2], 0, 999);
            if (!empty($versions[0]) && version_compare($t3version, $versions[0]) === -1) {
                $message = sprintf(
                    '"%s" requires TYPO3 versions %s - %s. It is not compatible with TYPO3 version "%s"',
                        $package->getPackageKey(),
                        $versions[0],
                        $versions[1],
                        $typo3Version
                );
            }
            if (!empty($versions[1]) && version_compare($versions[1], $t3version) === -1) {
                $message = sprintf(
                    '"%s" requires TYPO3 versions %s - %s. It is not compatible with TYPO3 version "%s"',
                        $package->getPackageKey(),
                        $versions[0],
                        $versions[1],
                        $typo3Version
                );
            }
        }
        return $message;
    }
}
