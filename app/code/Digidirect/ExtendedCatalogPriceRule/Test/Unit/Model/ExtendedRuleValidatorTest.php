<?php
namespace Digidirect\ExtendedCatalogPriceRule\Test\Unit\Model;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Digidirect\ExtendedCatalogPriceRule\Model\Magento\Rule\ExtendedRuleValidator;

/**
 * Class ExtendedRuleValidatorTest
 * @package Digidirect\ExtendedCatalogPriceRule\Test\Unit\Model
 */
class ExtendedRuleValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var ExtendedRuleValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleValidatorModel;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formFactoryMock = $this->getMockBuilder(\Magento\Framework\Data\FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeDateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ruleValidatorModel = $this->objectManager->getObject(
            ExtendedRuleValidator::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'formFactory' => $this->formFactoryMock,
                'localeDate' => $this->localeDateMock,
            ]
        );
    }

    /**
     * @dataProvider validateDataDataProvider
     * @param array $data
     * @param array $expected
     * @return void
     */
    public function testValidateData($data, $expected)
    {
        $result = $this->ruleValidatorModel->validateData(new \Magento\Framework\DataObject($data));
        $this->assertEquals($result, $expected);
    }

    /**
     * Data provider for testValidateData
     * @return array
     */
    public function validateDataDataProvider()
    {
        return [
            [
                [
                    'simple_action' => RuleDisplayMessageInterface::ACTION_CODE,
                    'discount_amount' => 25,
                ],
                true
            ],
            [
                [
                    'simple_action' => RuleDisplayMessageInterface::ACTION_CODE,
                    'discount_amount' => -100,
                ],
                [
                    'Discount value should be 0 or greater.'
                ]
            ],
            [
                [
                    'simple_action' => RuleDisplayMessageInterface::ACTION_CODE,
                    'discount_amount' => 25,
                    'website_ids' => [1],
                ],
                true
            ],
            [
                [
                    'simple_action' => RuleDisplayMessageInterface::ACTION_CODE,
                    'discount_amount' => 25,
                    'website_ids' => [],
                ],
                [
                    'Please specify a website.'
                ]
            ],
            [
                [
                    'simple_action' => RuleDisplayMessageInterface::ACTION_CODE,
                    'discount_amount' => 25,
                    'customer_group_ids' => [0, 1],
                ],
                true
            ],
            [
                [
                    'simple_action' => RuleDisplayMessageInterface::ACTION_CODE,
                    'discount_amount' => 25,
                    'customer_group_ids' => [],
                ],
                [
                    'Please specify Customer Groups.'
                ]
            ],
        ];
    }
}
