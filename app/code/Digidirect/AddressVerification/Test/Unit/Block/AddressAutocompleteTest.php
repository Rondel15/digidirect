<?php
namespace Digidirect\AddressVerification\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Digidirect\AddressVerification\Block\AddressAutocomplete;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Digidirect\AddressVerification\Helper\Autocomplete as AutocompleteHelper;

/**
 * Class AddressAutocompleteTest
 *
 * @package Digidirect\AddressVerification\Test\Unit
 */
class AddressAutocompleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $autocompleteHelper;

    /**
     * @var JsonHelper
     */
    protected $jsonHelperMock;

    /**
     * @var AddressAutocomplete
     */
    protected $autocompleteBlockOriginal;

    /**
     * Setup objects
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->jsonHelperMock = $this->_getMockObjectWithoutConstructor(
            JsonHelper::class,
            ['jsonEncode']
        );

        $this->autocompleteHelper = $this->_getMockObjectWithoutConstructor(
            AutocompleteHelper::class,
            ['isGoogleEnabled', 'getApiKey', 'getDefaultCountry', 'getAllowedCountries']
        );

        $this->autocompleteBlockOriginal = $this->objectManager->getObject(
            AddressAutocomplete::class,
            [
                'helper' => $this->autocompleteHelper,
                'jsonHelper' => $this->jsonHelperMock,
                'data' => [
                    'gplaces_config' => [
                        'fields' => [
                            'street_line_1' => 'street_1',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Get mock object without constructor
     *
     * @param string $className
     * @param [] $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMockObjectWithoutConstructor(string $className, array $methods = [])
    {
        return $this->getMockBuilder($className)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * {@inheritdoc}
     */
    public function testGetConfigDisabled()
    {
        $this->autocompleteHelper->expects($this->any())
            ->method('isGoogleEnabled')
            ->willReturn(false);

        $result = ['gplaces_config' => []];

        $data = new \magento\Framework\DataObject($result);

        $this->jsonHelperMock->expects($this->any())
            ->method('jsonEncode')
            ->with($data)
            ->willReturn($data);

        $this->assertEquals($data, $this->autocompleteBlockOriginal->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function testGetConfigEnabled()
    {
        $this->autocompleteHelper->expects($this->any())
            ->method('isGoogleEnabled')
            ->willReturn(true);

        $this->autocompleteHelper->expects($this->any())
            ->method('getApiKey')
            ->willReturn('test_api_key');

        $this->autocompleteHelper->expects($this->any())
            ->method('getDefaultCountry')
            ->willReturn('US');

        $this->autocompleteHelper->expects($this->any())
            ->method('getAllowedCountries')
            ->willReturn(['AU', 'NZ']);

        $result = [

            'countries' => [
                'AU',
                'NZ',
            ],
            'default_country' => 'US',
            'api_key' => 'test_api_key',
            'fields' => [
                'street_line_1' => 'street_1',
            ],
        ];

        $data = new \magento\Framework\DataObject($result);

        $this->jsonHelperMock->expects($this->any())
            ->method('jsonEncode')
            ->with($data)
            ->willReturn($data);

        $this->assertEquals($data, $this->autocompleteBlockOriginal->getConfig());
    }
}
