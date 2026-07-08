<?php
namespace Digidirect\ExtendedShippingRates\Test\Unit\Model\Carrier\Method;

use Digidirect\ExtendedShippingRates\Test\Unit\ExtendedShippingRatesTestUnitTrait;
use Digidirect\ExtendedShippingRates\Model\Config\Source\WeightType;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method\Rate;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method;
use Magento\Quote\Model\Quote\Address\RateRequest;

class RateTest extends \PHPUnit_Framework_TestCase
{
    use ExtendedShippingRatesTestUnitTrait;

    /**
     * @param $data
     * @param $expected
     * @dataProvider provideWeight
     */
    public function testValidatePackagingWeight($data, $expected)
    {
        $rateModelMock = $this->getMockObjectWithoutConstructor(
            Rate::class,
            null
        );
        $methodMock = $this->getMockObjectWithoutConstructor(
            Method::class,
            ['getPackagingWeightType', 'getPackagingWeightValue']
        );
        $methodMock->method('getPackagingWeightType')
            ->willReturn($data['packaging_weight_type']);
        $methodMock->expects($this->once())
            ->method('getPackagingWeightValue')
            ->willReturn($data['packaging_weight_value']);

        $result = $rateModelMock->validatePackagingWeight($data['original_weight'], $methodMock);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function provideWeight()
    {
        $firstData = [
            'original_weight' => 20,
            'packaging_weight_type' => WeightType::FIXED_CODE,
            'packaging_weight_value' => 10
        ];
        $firstExpected = 30;
        $secondData = [
            'original_weight' => 20,
            'packaging_weight_type' => WeightType::PERCENTAGE_CODE,
            'packaging_weight_value' => 10
        ];
        $secondExpected = 22;
        return [
            [$firstData, $firstExpected],
            [$secondData, $secondExpected]
        ];
    }

    /**
     * @param $data
     * @param $expected
     * @dataProvider providePrices
     */
    public function testPriceCalculationExpectsCorrect($data, $expected)
    {
        $rateModelMock = $this->getMockObjectWithoutConstructor(
            Rate::class,
            [
                'calculateItemsTotalPrice',
                'validatePackagingWeight',
                'getPrice',
                'getPricePerProduct',
                'getPricePerItem',
                'getPricePercentPerProduct',
                'getPricePercentPerItem',
                'getItemPricePercent',
                'getPricePerWeight',
                'getRateMethodPrice'
            ]
        );
        $rateModelMock->method('validatePackagingWeight')
            ->willReturn($data['package_weight']);
        $rateModelMock->method('calculateItemsTotalPrice')
            ->willReturn($data['total_item_price']);
        $rateModelMock->method('getPrice')
            ->willReturn($data['total_price']);
        $rateModelMock->method('getPricePerProduct')
            ->willReturn($data['price_per_product']);
        $rateModelMock->method('getPricePerItem')
            ->willReturn($data['price_per_item']);
        $rateModelMock->method('getPricePercentPerProduct')
            ->willReturn($data['price_percent_per_product']);
        $rateModelMock->method('getPricePercentPerItem')
            ->willReturn($data['price_percent_per_item']);
        $rateModelMock->method('getItemPricePercent')
            ->willReturn($data['total_item_price_percent']);
        $rateModelMock->method('getPricePerWeight')
            ->willReturn($data['price_per_weight']);
        $rateModelMock->method('getRateMethodPrice')
            ->willReturn(Rate::PRICE_CALCULATION_SUM);
        $rateRequestMock = $this->getMockObjectWithoutConstructor(
            RateRequest::class,
            [
                'getAllItems',
                'getPackageWeight',
                'getPackageQty'
            ]
        );
        $rateRequestMock->method('getAllItems')
            ->willReturn($data['products']);
        $rateRequestMock->method('getPackageQty')
            ->willReturn($data['items_number']);
        $methodMock = $this->getMockObjectWithoutConstructor(
            Method::class,
            ['getData']
        );
        $methodMock->expects($this->once())
            ->method('getData')
            ->willReturn(0);

        $result = $rateModelMock->getCalculatedPrice($rateRequestMock, $methodMock);

        $this->assertEquals($result, $expected);
    }

    /**
     * @return array
     */
    public function providePrices()
    {
        $productData = [
            'products' => [1,2,3,4,5],
            'items_number' => 6,
            'package_weight' => 13
        ];
        $rateData = [
            'total_price' => 100,
            'total_item_price' => 220,
            'total_item_price_percent' => 10
        ];
        $firstData = [
            'price_per_product' => 10,
            'price_per_item' => 10,
            'price_percent_per_product' => 10,
            'price_percent_per_item' => 10,
            'price_per_weight' => 10
        ];
        $firstExpected = $this->calculatePrice($productData + $rateData + $firstData);
        $secondData = [
            'price_per_product' => 0,
            'price_per_item' => 0,
            'price_percent_per_product' => 0,
            'price_percent_per_item' => 0,
            'price_per_weight' => 0
        ];
        $secondExpected = $this->calculatePrice($productData + $rateData + $secondData);
        return [
            [$productData + $rateData + $firstData, $firstExpected],
            [$productData + $rateData + $secondData, $secondExpected]
        ];
    }

    public function calculatePrice($data)
    {
        return $data['total_price']
            + count($data['products']) * $data['price_per_product']
            + $data['items_number'] * $data['price_per_item']
            + count($data['products']) * $data['price_percent_per_product'] / 100
            + $data['items_number'] * $data['price_percent_per_item'] / 100
            + $data['total_item_price'] * $data['total_item_price_percent'] / 100
            + $data['package_weight'] * $data['price_per_weight'];
    }
}
