<?php
namespace Digidirect\SEO\Test\Unit\Model;

use Digidirect\SEO\Test\Unit\AbstractTestUnit;
use Digidirect\SEO\Model\CategoryMetadata;
use Magento\Catalog\Model\Category;
use Digidirect\SEO\Helper\Category as CategoryMetadataHelper;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager as UnitObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CategoryMetadataTest
 * @package Digidirect\SEO\Test\Unit\Model
 */
class CategoryMetadataTest extends AbstractTestUnit
{
    /**
     * Test meta data modifying
     *
     * @dataProvider provideCategoryData
     * @param array $data
     * @param array $expected
     */
    public function testSetMetadata(array $data, array $expected)
    {
        $actual = $data['object']->setMetadata($data['category'])->getData();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Provide processed objects
     *
     * @return []
     */
    public function provideCategoryData()
    {
        $helper1 = $this->getHelperMock(true);
        $data1 = [
            'meta_description' => 'Existing meta description',
            'meta_title' => 'Existing meta title',
            'description' => 'DESCRIPTION CAPS ENTERED',
            'name' => 'NAME CAPS ENTERED'
        ];

        $categoryMock1 = $this->getCategoryMock($data1);

        $om = new ObjectManager($this);
        $object1 = $om->getObject(
            CategoryMetadata::class,
            [
                'helper' => $helper1,
                'metadataConfig' => [
                    'meta_description' => 'description',
                    'meta_title' => 'name',
                    'description' => 'DESCRIPTION CAPS ENTERED',
                    'name' => 'NAME CAPS ENTERED'
                ]
            ]
        );

        $helper2 = $this->getHelperMock(true);
        $data2 = [
            'description' => 'Changed description',
            'name' => 'Changed title'
        ];

        $categoryMock2 = $this->getCategoryMock($data2);

        $om = new ObjectManager($this);
        $object2 = $om->getObject(
            CategoryMetadata::class,
            [
                'helper' => $helper2,
                'metadataConfig' => [
                    'meta_description' => 'description',
                    'meta_title' => 'name',
                    'description' => 'DESCRIPTION CAPS ENTERED',
                    'name' => 'NAME CAPS ENTERED'
                ]
            ]
        );

        $helper3 = $this->getHelperMock(false);
        $data3 = [
            'description' => 'Changed description',
            'name' => 'Changed title'
        ];

        $categoryMock3 = $this->getCategoryMock($data3);

        $om = new ObjectManager($this);
        $object3 = $om->getObject(
            CategoryMetadata::class,
            [
                'helper' => $helper3,
                'metadataConfig' => [
                    'meta_description' => 'description',
                    'meta_title' => 'name',
                    'description' => 'DESCRIPTION CAPS ENTERED',
                    'name' => 'NAME CAPS ENTERED'
                ]
            ]
        );

        return [
            [
                ['category' => $categoryMock1, 'object' => $object1],
                [
                    'meta_description' => 'Existing meta description',
                    'meta_title' => 'Existing meta title',
                    'description' => 'DESCRIPTION CAPS ENTERED',
                    'name' => 'NAME CAPS ENTERED'
                ]
            ],
            [
                ['category' => $categoryMock2, 'object' => $object2],
                [
                    'meta_description' => 'Changed description',
                    'meta_title' => 'Changed title',
                    'description' => 'Changed description',
                    'name' => 'Changed title'
                ]
            ],
            [
                ['category' => $categoryMock3, 'object' => $object3],
                [
                    'description' => 'Changed description',
                    'name' => 'Changed title'
                ]
            ]
        ];
    }

    /**
     * @param bool $isEnabled
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHelperMock(bool $isEnabled = true)
    {
        $helperMock = $this->getMockObjectWithoutConstructor(
            CategoryMetadataHelper::class,
            ['isMetaAutoGenerationEnabled']
        );

        $helperMock->expects($this->any())
            ->method('isMetaAutoGenerationEnabled')
            ->willReturn($isEnabled);

        return $helperMock;
    }

    protected function getCategoryMock($data)
    {
        $categoryMock = $this->getMockObjectWithoutConstructor(Category::class, ['getId']);
        $reflectionCategory = $this->getReflectionClass(Category::class);
        $dataProperty = $reflectionCategory->getProperty('_data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($categoryMock, $data);
        return $categoryMock;
    }
}
