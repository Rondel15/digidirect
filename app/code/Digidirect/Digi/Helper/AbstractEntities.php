<?php
namespace Digidirect\Digi\Helper;

use \Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;

/**
 * Class AbstractEntities
 * @package Digidirect\Digi\Helper
 */
class AbstractEntities extends \Magento\Framework\App\Helper\AbstractHelper
{
    const KF_ATTRIBUTE_SET_NAME = 'Key Feature';
    const NAME_ATTRIBUTE_CODE = 'name';

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * @var AbstractEntityResource
     */
    protected $abstractEntityResource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Digidirect\AbstractEntity\Helper\Image
     */
    protected $imageHelper;

    /**
     * AbstractEntities constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param AbstractEntityResource $abstractEntityResource
     * @param \Digidirect\AbstractEntity\Helper\Image $imageHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        AbstractEntityResource $abstractEntityResource,
        \Digidirect\AbstractEntity\Helper\Image $imageHelper
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->abstractEntityResource = $abstractEntityResource;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     * @throws LocalizedException
     */
    public function getProductKeyFeatures(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $options = [];
        if ($product->getId()) {
            $features = "";
            if($product->getKeyFeatures())
            {
                $features = explode(',', $product->getKeyFeatures());  
            }
            
            if (!empty($features)) {
                $attributeSetId = $this->abstractEntityResource->getAttributeSetIdByName(
                    self::KF_ATTRIBUTE_SET_NAME
                );

                if (!empty($attributeSetId)) {
                    $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, $attributeSetId)
                        ->addFilter(AbstractEntityInterface::STATUS, 1)
                        ->addFilter(AbstractEntityInterface::ENTITY_ID, $features, 'in')
                        ->create();

                    try {
                        $stores = $this->abstractEntityRepository->getList(
                            $searchCriteria,
                            AbstractEntity::ENTITY_TYPE,
                            [
                                self::NAME_ATTRIBUTE_CODE,
                                'image'
                            ]
                        );

                        foreach ($stores->getItems() as $item) {
                            $imageHelper = $this->imageHelper->init($item, 'image');
                            $item->setImageUrl($imageHelper->getOriginalImageUrl());
                            $options[] = $item;
                        }
                    } catch (LocalizedException $e) {

                    }
                }
            }
        }

        return $options;
    }
}
