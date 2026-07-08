<?php

namespace Digidirect\ExtendedCartPriceRules\Test\Unit\Model\Rule\Condition;

use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\Sales\Order as OrderResource;
use Magento\Checkout\Model\Session as CheckoutSession;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Condition\LastOrderStatus;

class LastOrderStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LastOrderStatus
     */
    protected $_lastOrderStatus;

    /**
     * @var OrderResource
     */
    protected $_orderResource;

    /**
     * @var OrderConfig
     */
    protected $_orderConfig;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function _setProtectedProperty($object, $property, $value)
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * Set up required common objects
     *
     * @return void
     */
    public function setUp()
    {
        $this->_orderResource = $this->getMockBuilder(OrderResource::class)
            ->setMethods(['getLastCustomersOrderStatus'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_orderConfig = $this->getMockBuilder(OrderConfig::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_checkoutSession = $this->getMockBuilder(CheckoutSession::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_lastOrderStatus = $this->getMockBuilder(LastOrderStatus::class)
            ->setMethods([
                'getAttribute',
                'getValueParsed',
                'getOperatorForValidate',
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_lastOrderStatus->expects($this->any())
            ->method('getAttribute')
            ->willReturn('last_order_status');

        $this->_lastOrderStatus->expects($this->any())
            ->method('getOperatorForValidate')
            ->willReturn('==');

        $this->_setProtectedProperty($this->_lastOrderStatus, '_orderResource', $this->_orderResource);
        $this->_setProtectedProperty($this->_lastOrderStatus, '_orderConfig', $this->_orderConfig);
        $this->_setProtectedProperty($this->_lastOrderStatus, '_checkoutSession', $this->_checkoutSession);

        $this->_quote = $this->getMockBuilder(Quote::class)
            ->setMethods(['getCustomerId', 'getCustomerEmail'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @param int|null $customerId
     * @param string|null $customerEmail
     * @param string|null $lastCheckedEmail
     * @return void
     */
    protected function _setExpectedCustomerInfo($customerId, $customerEmail, $lastCheckedEmail)
    {
        $this->_quote->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->_quote->expects($this->any())
            ->method('getCustomerEmail')
            ->willReturn($customerEmail);

        $this->_checkoutSession->expects($this->any())
            ->method('getData')
            ->with('last_checked_email')
            ->willReturn($lastCheckedEmail);
    }

    /**
     * @return array
     */
    public function provideDataValidate()
    {
        return [
            [null, null, 'test@test.com', 'test@test.com', null, 'completed', false],
            [null, null, 'test@test.com', 'test@test.com', 'pending', 'completed', false],
            [null, null, 'test@test.com', 'test@test.com', 'completed', 'completed', true],
            [1, 'test@test.com', null, 'test@test.com', null, 'completed', false],
            [1, 'test@test.com', null, 'test@test.com', 'pending', 'completed', false],
            [1, 'test@test.com', null, 'test@test.com', 'completed', 'completed', true],
        ];
    }

    /**
     * @dataProvider provideDataValidate
     * @param int|null $customerId
     * @param string|null $customerEmail
     * @param string|null $lastCheckedEmail
     * @param string|null $resultEmail
     * @param string|null $status
     * @param string $conditionValue
     * @param bool $expectedResult
     * @return void
     */
    public function testValidate(
        $customerId,
        $customerEmail,
        $lastCheckedEmail,
        $resultEmail,
        $status,
        $conditionValue,
        $expectedResult
    ) {
        $this->_setExpectedCustomerInfo($customerId, $customerEmail, $lastCheckedEmail);

        $this->_orderResource->expects($this->once())
            ->method('getLastCustomersOrderStatus')
            ->with($customerId, $resultEmail)
            ->willReturn($status);

        $this->_lastOrderStatus->expects($this->any())
            ->method('getValueParsed')
            ->willReturn($conditionValue);

        $this->assertEquals($expectedResult, $this->_lastOrderStatus->validate($this->_quote));
    }

    /**
     * @return void
     */
    public function testValidateUndefined()
    {
        $this->_setExpectedCustomerInfo(null, null, null);

        $this->_orderResource->expects($this->never())
            ->method('getLastCustomersOrderStatus');

        $this->_lastOrderStatus->expects($this->never())
            ->method('getValueParsed');

        $this->assertFalse($this->_lastOrderStatus->validate($this->_quote));
    }
}
