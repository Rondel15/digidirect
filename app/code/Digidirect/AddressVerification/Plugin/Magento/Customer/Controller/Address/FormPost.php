<?php
namespace Digidirect\AddressVerification\Plugin\Magento\Customer\Controller\Address;

use Digidirect\AddressVerification\Helper\Autocomplete;
use Digidirect\AddressVerification\Helper\Aupost;
use Magento\Customer\Controller\Address\FormPost as MagentoAddressSaveController;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Session;

/**
 * Class FormPost
 *
 * @package Digidirect\AddressVerification\Plugin\Magento\Customer\Controller\Address
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormPost
{
    /**
     * @var Aupost
     */
    protected $auPost;

    /**
     * @var Autocomplete
     */
    protected $autocomplete;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * FormPost constructor.
     *
     * @param Autocomplete $autocomplete
     * @param Aupost $aupost
     * @param ManagerInterface $manager
     * @param RedirectFactory $redirectFactory
     * @param UrlFactory $urlFactory
     * @param Session $session
     */
    public function __construct(
        Autocomplete $autocomplete,
        Aupost $aupost,
        ManagerInterface $manager,
        RedirectFactory $redirectFactory,
        UrlFactory $urlFactory,
        Session $session
    ) {
        $this->autocomplete = $autocomplete;
        $this->auPost = $aupost;
        $this->urlFactory = $urlFactory;
        $this->messageManager = $manager;
        $this->redirectFactory = $redirectFactory;
        $this->customerSession = $session;
    }

    /**
     * @param MagentoAddressSaveController $formPost
     * @param \Closure $function
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(MagentoAddressSaveController $formPost, \Closure $function)
    {
        if ($this->autocomplete->isAuPostEnabled()) {
            $postValue = $formPost->getRequest()->getPostValue();
            $address = new DataObject($postValue);

            $isValid = $this->auPost->isCombinationValid(
                $address->getCountryId(),
                $address->getPostcode(),
                $address->getCity(),
                $address->getRegion(),
                $address->getRegionId()
            );

            if (!$isValid) {
                /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                $resultRedirect = $this->redirectFactory->create();
                $this->messageManager->addErrorMessage(
                    __('You entered invalid Postcode and/or Suburb. Please, check and try again.')
                );
                $this->customerSession->setAddressFormData($postValue);
                $urlModel = $this->urlFactory->create();
                $defaultUrl = $urlModel->getUrl('*/*/edit', ['id' => $formPost->getRequest()->getParam('id')]);
                $resultRedirect->setUrl($defaultUrl);
                return $resultRedirect;
            }
        }

        return $function();
    }
}
