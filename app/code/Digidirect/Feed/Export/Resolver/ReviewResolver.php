<?php

namespace Digidirect\Feed\Export\Resolver;

use Magento\Review\Model\Review;

class ReviewResolver extends AbstractResolver
{
    const DEFAULT_RATING = 5;

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * Associated product
     *
     * @param Review $review
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($review)
    {
        return $review->getProductCollection()
            ->addFieldToFilter('entity_id', $review->getEntityPkValue())
            ->getFirstItem();
    }

    /**
     * @param Review $review
     * @return float
     */
    public function getRating($review)
    {
        if ($review->getRating()) {
            return $review->getRating();
        }
        return $this->_getDefaultRating();
    }

    /**
     * @return int
     */
    protected function _getDefaultRating()
    {
        return self::DEFAULT_RATING;
    }
}
