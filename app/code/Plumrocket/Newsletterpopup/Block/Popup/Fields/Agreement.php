<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Popup\Fields;

class Agreement extends Field
{
    /**
     * Cms page identifiers that will show in modal window
     *
     * @var array
     */
    protected $pageIdentifiers = [];

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context      $context
     * @param \Magento\Customer\Helper\Address                      $addressHelper
     * @param \Magento\Customer\Api\CustomerMetadataInterface       $customerMetadata
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Magento\Customer\Model\Options                       $customerOptions
     * @param \Magento\Customer\Model\Customer                      $customer
     * @param \Magento\Cms\Model\PageFactory                        $pageFactory
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadata,
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Customer\Model\Options $customerOptions,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Cms\Model\PageFactory $pageFactory,
        array $data = []
    ) {
        $this->pageFactory = $pageFactory;
        parent::__construct(
            $context,
            $addressHelper,
            $customerMetadata,
            $attributeMetadataDataProvider,
            $customerOptions,
            $customer,
            $data
        );
    }

    /**
     * Retrieve lable of checkbox
     *
     * @return string
     */
    public function getLabel()
    {
        $html = parent::getLabel();
        $html = $this->prepareLinks($html);

        return $html;
    }

    /**
     * Find all links and collect cms page identifiers
     *
     * @param  string $html
     * @return string
     */
    public function prepareLinks($html)
    {
        preg_match_all(
            '#<a.+?href=["\']\#([^"\']+?)["\'].*?>.+?</a>#uis',
            $html,
            $matches
        );

        foreach ($matches[1] as $identifier) {
            $this->pageIdentifiers[] = $identifier;
        }

        return $html;
    }

    /**
     * Retrieve html element id
     *
     * @param  string|int $identifier
     * @return string
     */
    public function getAgreementContentId($identifier)
    {
        return 'nl_agreement_' . $this->getPopup()->getId() . '_page_' . $identifier;
    }

    /**
     * Retrieve list of all identifiers
     *
     * @return array
     */
    public function getPageIdentifiers()
    {
        return array_unique($this->pageIdentifiers);
    }

    /**
     * Retrieve cms page content by identifier
     *
     * @param  string|int $identifier
     * @return string
     */
    public function getPageContent($identifier)
    {
        return $this->pageFactory->create()
            ->load($identifier)
            ->getContent();
    }
}
