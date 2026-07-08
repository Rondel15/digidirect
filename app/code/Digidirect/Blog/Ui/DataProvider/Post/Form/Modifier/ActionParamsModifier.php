<?php

namespace Digidirect\Blog\Ui\DataProvider\Post\Form\Modifier;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Class ActionParamsModifier
 */
class ActionParamsModifier implements ModifierInterface
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
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * CategoryDataProvider constructor.
     *
     * @param Registry $registry
     * @param CurrentStoreFetcher $currentStoreFetcher
     * @param UrlInterface $url
     */
    public function __construct(
        Registry $registry,
        CurrentStoreFetcher $currentStoreFetcher,
        UrlInterface $url
    ) {
        $this->urlBuilder = $url;
        $this->currentStoreFetcher = $currentStoreFetcher;
        $this->registry = $registry;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $actionParameters = [
            'entity_id' => $this->getCurrentPost()->getId(),
            CurrentStoreFetcher::PARAM_STORE => $this->currentStoreFetcher->getCurrentStoreId(),
        ];

        $submitUrl = $this->urlBuilder->getUrl('digidirect_blog/post/save', $actionParameters);

        $data = array_replace_recursive(
            $data,
            [
                'config' => [
                    'submit_url' => $submitUrl,
                ],
            ]
        );

        return $data;
    }

    /**
     * @return mixed
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
