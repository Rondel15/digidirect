<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Observer;

use Magento\Backend\Block\Widget\Grid\Column\Extended;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Block\Adminhtml\Subscriber\Grid as SubscriberGrid;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollectionFactory;
use Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid as CouponsGrid;
use Plumrocket\Newsletterpopup\Helper\Config;

class PrepareGridBeforeObserver implements ObserverInterface
{
    /**
     * @var string[]
     */
    protected $_columnsOrder = [
        'subscriber_prefix' => 'type',
        'subscriber_middlename' => 'firstname',
        'subscriber_suffix' => 'lastname',

        'subscriber_postcode' => 'status',
        'subscriber_street' => 'status',
        'subscriber_city' => 'status',
        'subscriber_region' => 'status',
        'subscriber_country_id' => 'status',
        'subscriber_company' => 'status',
        'subscriber_fax' => 'status',
        'subscriber_telephone' => 'status',
        'subscriber_taxvat' => 'status',
        'subscriber_gender' => 'status',
        'subscriber_dob' => 'status',
    ];

    /**
     * @var CustomerResource
     */
    private $_resourceCustomer;

    /**
     * @var Country
     */
    private $_directoryCountry;

    /**
     * @var SubscriberCollectionFactory
     */
    private $_subscriberCollectionFactory;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer                       $resourceCustomer
     * @param \Magento\Directory\Model\Config\Source\Country                       $directoryCountry
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory
     * @param \Plumrocket\Newsletterpopup\Helper\Config                            $config
     */
    public function __construct(
        CustomerResource $resourceCustomer,
        Country $directoryCountry,
        SubscriberCollectionFactory $subscriberCollectionFactory,
        Config $config
    ) {
        $this->_resourceCustomer = $resourceCustomer;
        $this->_directoryCountry = $directoryCountry;
        $this->_subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->config = $config;
    }

    /**
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (! $this->config->isModuleEnabled()) {
            return;
        }

        $grid = $observer->getEvent()->getGrid();

        if ($grid instanceof SubscriberGrid) {
            foreach ($this->_columnsOrder as $columnId => $after) {
                if (! $grid->getColumn($columnId)) {
                    $this->_processAdditionalField($grid, $columnId);
                }
            }

            // If standard firstname and lastname is empty then use our columns
            $grid->getCollection()
                ->addFilterToMap(
                    'firstname',
                    new \Zend_Db_Expr('IFNULL(`customer`.`firstname`, `main_table`.`subscriber_firstname`)')
                )
                ->addFilterToMap(
                    'lastname',
                    new \Zend_Db_Expr('IFNULL(`customer`.`lastname`, `main_table`.`subscriber_lastname`)')
                );

            $grid->getCollection()->getSelect()
                ->columns('IF(`main_table`.`customer_id` = 0, `main_table`.`subscriber_firstname`, `customer`.`firstname`) AS firstname')
                ->columns('IF(`main_table`.`customer_id` = 0, `main_table`.`subscriber_lastname`, `customer`.`lastname`) AS lastname');

            $this->_sortColumnsByOrder($grid);
        }

        /* It will displaying np_expiration_date on Manage Coupons Grid */
        if ($grid instanceof CouponsGrid) {
            $grid->addColumn(
                'np_expiration_date',
                [
                    'header' => __('Expires On'),
                    'index' => 'np_expiration_date',
                    'type' => 'datetime',
                    'align' => 'center',
                    'width' => '160',
                ]
            );
        }

        return $this;
    }

    /**
     * Process to add the field to grid
     *
     * @param  SubscriberGrid $grid
     * @param  string $field
     * @return boolean
     */
    private function _processAdditionalField(SubscriberGrid $grid, $field)
    {
        if (! $this->_showColumn($field)) {
            return false;
        }

        switch ($field) {
            case 'subscriber_middlename':
                $this->_addColumn($grid, 'subscriber_middlename', [
                    'header'    => __('Middle Name'),
                    'index'     => 'subscriber_middlename',
                    'default'   => '----'
                ]);
                break;
            case 'subscriber_suffix':
                $this->_addColumn($grid, 'subscriber_suffix', [
                    'header'    => __('Suffix'),
                    'index'     => 'subscriber_suffix',
                ]);
                break;
            case 'subscriber_dob':
                $this->_addColumn($grid, 'subscriber_dob', [
                    'header'    => __('Date of Birth'),
                    'index'     => 'subscriber_dob',
                    'type'      => 'date',
                    'timezone'  => false,
                ]);
                break;
            case 'subscriber_gender':
                $options = $this->_resourceCustomer
                        ->getAttribute('gender')
                        ->getSource()
                        ->getAllOptions(false);

                $this->_addColumn($grid, 'subscriber_gender', [
                    'header'    => __('Gender'),
                    'index'     => 'subscriber_gender',
                    'type'      => 'options',
                    'options'   => $this->_getOptions($options),
                ]);
                break;
            case 'subscriber_taxvat':
                $this->_addColumn($grid, 'subscriber_taxvat', [
                    'header'    => __('Tax/VAT Number'),
                    'index'     => 'subscriber_taxvat',
                ]);
                break;
            case 'subscriber_prefix':
                $this->_addColumn($grid, 'subscriber_prefix', [
                    'header'    => __('Prefix'),
                    'index'     => 'subscriber_prefix',
                ]);
                break;
            case 'subscriber_telephone':
                $this->_addColumn($grid, 'subscriber_telephone', [
                    'header'    => __('Telephone'),
                    'index'     => 'subscriber_telephone'
                ]);
                break;
            case 'subscriber_fax':
                $this->_addColumn($grid, 'subscriber_fax', [
                    'header'    => __('Fax'),
                    'index'     => 'subscriber_fax'
                ]);
                break;
            case 'subscriber_company':
                $this->_addColumn($grid, 'subscriber_company', [
                    'header'    => __('Company'),
                    'index'     => 'subscriber_company'
                ]);
                break;
            case 'subscriber_street':
                $this->_addColumn($grid, 'subscriber_street', [
                    'header'    => __('Street'),
                    'index'     => 'subscriber_street'
                ]);
                break;
            case 'subscriber_city':
                $this->_addColumn($grid, 'subscriber_city', [
                    'header'    => __('City'),
                    'index'     => 'subscriber_city'
                ]);
                break;
            case 'subscriber_country_id':
                $options = $this->_directoryCountry->toOptionArray();
                unset($options[0]);

                $this->_addColumn($grid, 'subscriber_country_id', [
                    'header'    => __('Country'),
                    'index'     => 'subscriber_country_id',
                    'type'      => 'options',
                    'options'   => $this->_getOptions($options),
                ]);
                break;
            case 'subscriber_region':
                $this->_addColumn($grid, 'subscriber_region', [
                    'header'    => __('Region'),
                    'index'     => 'subscriber_region'
                ]);
                break;
            case 'subscriber_postcode':
                $this->_addColumn($grid, 'subscriber_postcode', [
                    'header'    => __('Postcode'),
                    'index'     => 'subscriber_postcode'
                ]);
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * Add column to grid
     *
     * @param   SubscriberGrid $grid
     * @param   string $columnId
     * @param   array|\Magento\Framework\DataObject $column
     * @return  void
     * @throws  \Exception
     */
    protected function _addColumn(SubscriberGrid $grid, $columnId, $column)
    {
        if (is_array($column)) {
            $grid->getColumnSet()->setChild(
                $columnId,
                $grid->getLayout()
                    ->createBlock(Extended::class)
                    ->setData($column)
                    ->setId($columnId)
                    ->setGrid($grid)
            );
            $grid->getColumnSet()->getChildBlock($columnId)->setGrid($grid);
        } else {
            throw new \Exception(__('Please correct the column format and try again.'));
        }
    }

    /**
     * Sort columns by predefined order
     *
     * @param  SubscriberGrid $grid
     * @return void
     */
    protected function _sortColumnsByOrder(SubscriberGrid $grid)
    {
        foreach ($this->_columnsOrder as $columnId => $after) {
            if (! $after || ! $grid->getColumn($columnId) || ! $grid->getColumn($after)) {
                continue;
            }

            $grid->getLayout()->reorderChild(
                $grid->getColumnSet()->getNameInLayout(),
                $grid->getColumn($columnId)->getNameInLayout(),
                $grid->getColumn($after)->getNameInLayout()
            );
        }
    }

    /**
     * Check if need to show column in the grid
     *
     * @param  string $field
     * @return boolean
     */
    protected function _showColumn($field)
    {
        if ('subscriber_dob' == $field) {
            $size = $this->_subscriberCollectionFactory->create()
                ->addFieldToFilter($field, ['neq' => '0000-00-00'])
                ->getSize();
        } else {
            $size = $this->_subscriberCollectionFactory->create()
                ->addFieldToFilter($field, ['neq' => ''])
                ->addFieldToFilter($field, ['neq' => '0000-00-00'])
                ->getSize();
        }

        return $size > 0;
    }

    /**
     * Convert OptionsValue array to Options array
     *
     * @param array $optionsArray
     * @return array
     */
    protected function _getOptions($optionsArray)
    {
        $options = [];
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
}
