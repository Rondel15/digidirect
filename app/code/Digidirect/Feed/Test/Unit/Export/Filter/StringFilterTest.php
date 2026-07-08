<?php

namespace Digidirect\Feed\Test\Unit\Export\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManager;

/**
 * @covers \Digidirect\Feed\Export\Filter\StringFilter
 */
class StringFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Model
     *
     * @var \Digidirect\Feed\Export\Filter\StringFilter
     */
    protected $model;

    /**
     * Set Up
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->model = $this->objectManager->getObject('\Digidirect\Feed\Export\Filter\StringFilter', []);
    }

    /**
     * Test for csv
     *
     * @param string $expected
     * @param string $input
     * @param string $delimiter
     * @param string $enclosure
     *
     * @dataProvider csvProvider
     * @covers       \Digidirect\Feed\Export\Filter\StringFilter::csv
     */
    public function testCsv($expected, $input, $delimiter, $enclosure)
    {
        $this->assertEquals($expected, $this->model->csv($input, $delimiter, $enclosure));
    }

    /**
     * @return array
     */
    public function csvProvider()
    {
        return [
            ['string', 'string', '', ''],
            ['string', 'string', ';', "'"],
            ['text', 'text', ',', "'"],
            ["'text'", 'text', 'e', "'"],
            ["'with space'", 'with space', ',', "'"],
            ["''''", "'", ',', "'"]
        ];
    }
}
