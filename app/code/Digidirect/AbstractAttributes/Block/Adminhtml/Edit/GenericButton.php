<?php
namespace Digidirect\AbstractAttributes\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * GenericButton constructor.
     * @param Context $context
     * @param OptionRepositoryInterface $optionRepository
     */
    public function __construct(
        Context $context,
        OptionRepositoryInterface $optionRepository
    ) {
        $this->context = $context;
        $this->optionRepository = $optionRepository;
    }

    /**
     * Return CMS block ID
     * @return int|null
     */
    public function getOptionId()
    {
        try {
            return $this->optionRepository->get(
                $this->context->getRequest()->getParam('option_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     * @param  string $route
     * @param  array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
