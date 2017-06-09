<?php

/**
 * LICENSE: The MIT License (the "License")
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * https://github.com/azure/azure-storage-php/LICENSE
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * PHP version 5
 *
 * @category  Microsoft
 * @package   MicrosoftAzure\Storage\Tests\Unit\Common
 * @author    Azure Storage PHP SDK <dmsh@microsoft.com>
 * @copyright 2017 Microsoft Corporation
 * @license   https://github.com/azure/azure-storage-php/LICENSE
 * @link      https://github.com/azure/azure-storage-php
 */

namespace MicrosoftAzure\Storage\Tests\unit\Common\Internal\Authentication;

use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Tests\framework\TestResources;
use MicrosoftAzure\Storage\Tests\Framework\ReflectionTestBase;
use MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper;

/**
* Unit tests for class SharedAccessSignatureHelper
*
* @category  Microsoft
* @package   MicrosoftAzure\Storage\Tests\Unit\Common
* @author    Azure Storage PHP SDK <dmsh@microsoft.com>
* @copyright 2017 Microsoft Corporation
* @license   https://github.com/azure/azure-storage-php/LICENSE
* @link      https://github.com/azure/azure-storage-php
*/
class SharedAccessSignatureHelperTest extends ReflectionTestBase
{
    /**
    * @covers MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper::__construct
    */
    public function testConstruct()
    {
        // Setup
        $accountName = TestResources::ACCOUNT_NAME;
        $accountKey = TestResources::KEY4;

        // Test
        $sasHelper = new SharedAccessSignatureHelper($accountName, $accountKey);

        // Assert
        $this->assertNotNull($sasHelper);

        return $sasHelper;
    }

    /**
     * @covers MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper::validateAndSanitizeSignedService
     */
    public function testValidateAndSanitizeSignedService()
    {
        // Setup
        $sasHelper = $this->testConstruct();
        $validateAndSanitizeSignedService = self::getMethod('validateAndSanitizeSignedService', $sasHelper);

        $authorizedSignedService = array();
        $authorizedSignedService[] = "BqtF";
        $authorizedSignedService[] = "bQtF";
        $authorizedSignedService[] = "fqTb";
        $authorizedSignedService[] = "ffqq";
        $authorizedSignedService[] = "BbbB";
        
        $expected = array();
        $expected[] = "bqtf";
        $expected[] = "bqtf";
        $expected[] = "bqtf";
        $expected[] = "qf";
        $expected[] = "b";
        
        for ($i = 0; $i < count($authorizedSignedService); $i++) {
            // Test
            $actual = $validateAndSanitizeSignedService->invokeArgs($sasHelper, array($authorizedSignedService[$i]));

            // Assert
            $this->assertEquals($expected[$i], $actual);
        }
    }

    /**
     * @covers MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper::validateAndSanitizeSignedService
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The string should only be a combination of
     */
    public function testValidateAndSanitizeSignedServiceThrowsException()
    {
        // Setup
        $sasHelper = $this->testConstruct();
        $validateAndSanitizeSignedService = self::getMethod('validateAndSanitizeSignedService', $sasHelper);
        $unauthorizedSignedService = "BqTfG";

        // Test: should throw an InvalidArgumentException
        $validateAndSanitizeSignedService->invokeArgs($sasHelper, array($unauthorizedSignedService));
    }

    /**
     * @covers MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper::validateAndSanitizeSignedResourceType
     */
    public function testValidateAndSanitizeSignedResourceType()
    {
        // Setup
        $sasHelper = $this->testConstruct();
        $validateAndSanitizeSignedResourceType = self::getMethod('validateAndSanitizeSignedResourceType', $sasHelper);

        $authorizedSignedResourceType = array();
        $authorizedSignedResourceType[] = "sCo";
        $authorizedSignedResourceType[] = "Ocs";
        $authorizedSignedResourceType[] = "OOsCc";
        $authorizedSignedResourceType[] = "OOOoo";
        
        $expected = array();
        $expected[] = "sco";
        $expected[] = "sco";
        $expected[] = "sco";
        $expected[] = "o";
        
        for ($i = 0; $i < count($authorizedSignedResourceType); $i++) {
            // Test
            $actual = $validateAndSanitizeSignedResourceType->invokeArgs(
                $sasHelper,
                array($authorizedSignedResourceType[$i])
            );

            // Assert
            $this->assertEquals($expected[$i], $actual);
        }
    }

    /**
     * @covers MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper::validateAndSanitizeSignedResourceType
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The string should only be a combination of
     */
    public function testValidateAndSanitizeSignedResourceTypeThrowsException()
    {
        // Setup
        $sasHelper = $this->testConstruct();
        $validateAndSanitizeSignedResourceType = self::getMethod('validateAndSanitizeSignedResourceType', $sasHelper);

        $unauthorizedSignedResourceType = "oscB";

        // Test: should throw an InvalidArgumentException
        $validateAndSanitizeSignedResourceType->invokeArgs($sasHelper, array($unauthorizedSignedResourceType));
    }

    /**
     * @covers MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper::validateAndSanitizeSignedProtocol
     */
    public function testValidateAndSanitizeSignedProtocol()
    {
        // Setup
        $sasHelper = $this->testConstruct();
        $validateAndSanitizeSignedProtocol = self::getMethod('validateAndSanitizeSignedProtocol', $sasHelper);

        $authorizedSignedProtocol = array();
        $authorizedSignedProtocol[] = "hTTpS";
        $authorizedSignedProtocol[] = "httpS,hTtp";
        
        $expected = array();
        $expected[] = "https";
        $expected[] = "https,http";
        
        for ($i = 0; $i < count($authorizedSignedProtocol); $i++) {
            // Test
            $actual = $validateAndSanitizeSignedProtocol->invokeArgs($sasHelper, array($authorizedSignedProtocol[$i]));

            // Assert
            $this->assertEquals($expected[$i], $actual);
        }
    }

    /**
     * @covers MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper::validateAndSanitizeSignedProtocol
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage is invalid
     */
    public function testValidateAndSanitizeSignedProtocolThrowsException()
    {
        // Setup
        $sasHelper = $this->testConstruct();
        $validateAndSanitizeSignedProtocol = self::getMethod('validateAndSanitizeSignedProtocol', $sasHelper);
        
        $unauthorizedSignedProtocol = "htTp";

        // Test: should throw an InvalidArgumentException
        $validateAndSanitizeSignedProtocol->invokeArgs($sasHelper, array($unauthorizedSignedProtocol));
    }

    /**
    * @covers MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper::__construct
    * @covers MicrosoftAzure\Storage\Common\SharedAccessSignatureHelper::generateAccountSharedAccessSignatureToken
    */
    public function testGenerateAccountSharedAccessSignatureToken()
    {
        // Setup
        $accountName = TestResources::ACCOUNT_NAME;
        $accountKey = TestResources::KEY4;

        // Test
        $sasHelper = new SharedAccessSignatureHelper($accountName, $accountKey);

        // create the test cases
        $testCases = TestResources::getSASInterestingUTCases();
        
        foreach ($testCases as $testCase) {
            // test
            $actualSignature = $sasHelper->generateAccountSharedAccessSignatureToken(
                $testCase[0],
                $testCase[1],
                $testCase[2],
                $testCase[3],
                $testCase[4],
                $testCase[5],
                $testCase[6],
                $testCase[7]
            );

            // assert
            $this->assertEquals($testCase[8], urlencode($actualSignature));
        }
    }
}
