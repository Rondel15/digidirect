<?php

namespace Digidirect\Blog\Ui\DataProvider\Post\Form\Modifier;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Model\Post;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Model\StoreContent\DataModifier;
use Digidirect\Blog\Sql\PostInformationJoin;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class DefaultData implements ModifierInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CurrentStoreFetcher
     */
    protected $currentStoreFetcher;

    /**
     * @var DataModifier
     */
    protected $dataModifier;

    /**
     * DefaultData constructor.
     *
     * @param Registry $registry
     * @param CurrentStoreFetcher $currentStoreFetcher
     * @param DataModifier $dataModifier
     */
    public function __construct(
        Registry $registry,
        CurrentStoreFetcher $currentStoreFetcher,
        DataModifier $dataModifier
    ) {
        $this->dataModifier = $dataModifier;
        $this->currentStoreFetcher = $currentStoreFetcher;
        $this->registry = $registry;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $id = $this->getCurrentPost()->getId();
        if (!$id) {
            return $data;
        }
        $dataToModify = $data[$id] ?? [];
        if (!empty($dataToModify)) {
            $dataToModify = $this->dataModifier->modifyData(
                $dataToModify,
                PostInformationJoin::DEFAULT_STORE_COLUMN_PREFIX
            );
        }

        $data[$id] = $dataToModify;
        return $data;
    }

    /**
     * @return PostInterface|Post
     */
    protected function getCurrentPost()
    {
        return $this->registry->registry(PostInterface::CURRENT_ITEM);
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
