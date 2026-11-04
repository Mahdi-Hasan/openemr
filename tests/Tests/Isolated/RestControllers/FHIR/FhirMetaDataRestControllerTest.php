<?php

/**
 * Isolated tests for the software block of the FHIR capability statement
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Mahdi Hasan <43575088+Mahdi-Hasan@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Mahdi Hasan <43575088+Mahdi-Hasan@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\FHIR;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\RestControllers\FHIR\FhirMetaDataRestController;
use PHPUnit\Framework\TestCase;

final class FhirMetaDataRestControllerTest extends TestCase
{
    /**
     * software.version comes from version.php (via globals), not from a
     * top-level "version" key that composer.json does not have.
     */
    public function testSoftwareVersionComesFromVersionGlobals(): void
    {
        $software = $this->buildController([
            'v_major' => '8',
            'v_minor' => '6',
            'v_patch' => '0',
            'v_tag' => '-dev',
            'v_realpatch' => '0',
        ])->getSoftware();

        $name = $software->getName();
        $this->assertInstanceOf(FHIRString::class, $name);
        $this->assertSame('OpenEMR', $name->getValue());

        $version = $software->getVersion();
        $this->assertInstanceOf(FHIRString::class, $version);
        $this->assertSame('8.6.0-dev', $version->getValue());
    }

    public function testSoftwareVersionIncludesRealPatch(): void
    {
        $version = $this->buildController([
            'v_major' => '8',
            'v_minor' => '0',
            'v_patch' => '0',
            'v_tag' => '',
            'v_realpatch' => '3',
        ])->getSoftware()->getVersion();

        $this->assertInstanceOf(FHIRString::class, $version);
        $this->assertSame('8.0.0.3', $version->getValue());
    }

    /**
     * @param array<string, string> $versionGlobals
     */
    private function buildController(array $versionGlobals): FhirMetaDataRestController
    {
        return new FhirMetaDataRestController(
            $this->createStub(ServerConfig::class),
            new OEGlobalsBag($versionGlobals + ['v_database' => '566', 'v_acl' => '13']),
        );
    }
}
