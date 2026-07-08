<?php

namespace Digidirect\AI\Model\Integrations;

class Integrations extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_PENDING = 'Pending';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_ERROR = 'Error';
    const STATUS_DISABLED = 'Disabled';
    const SUCCESS_FINISH_DATE = 'success_finish_date';

    const STATUS_ARR = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_ERROR,
        self::STATUS_DISABLED,
    ];

    /**
     * Is custom status used
     *
     * @var bool
     */
    protected $_customStatusUsed = false;

    /**
     * @var Rule\RulesRepository
     */
    protected $_rulesRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Digidirect\AI\Model\Integrations\Rule\RulesRepository $rulesRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\AI\Model\Integrations\Rule\RulesRepository $rulesRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_rulesRepository = $rulesRepository;
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\AI\Model\ResourceModel\Integrations\Integrations');
    }

    /**
     * Set processing status
     *
     * @return $this
     */
    public function setProcessingStatus()
    {
        $this->setStatus(\Digidirect\AI\Model\Integrations\Integrations::STATUS_PROCESSING);
        $this->getResource()->save($this);
        return $this;
    }

    /**
     * Set pending status
     *
     * @return $this
     */
    public function setPendingStatus()
    {
        $this->setStatus(\Digidirect\AI\Model\Integrations\Integrations::STATUS_PENDING);
        $this->getResource()->save($this);
        return $this;
    }

    /**
     * Set error status
     *
     * @return $this
     */
    public function setErrorStatus()
    {
        $this->setStatus(\Digidirect\AI\Model\Integrations\Integrations::STATUS_ERROR);
        $this->getResource()->save($this);
        return $this;
    }

    /**
     * Set disabled status
     *
     * @return $this
     */
    public function setDisabledStatus()
    {
        $this->setStatus(\Digidirect\AI\Model\Integrations\Integrations::STATUS_DISABLED);
        $this->getResource()->save($this);
        return $this;
    }

    /**
     * @return array
     */
    public function getEntityRules()
    {
        return $this->_rulesRepository->getRulesByIntegrationName($this->getProcessCode());
    }

    /**
     * @return array
     */
    public function getRunOptions()
    {
        return (array)$this->getData('run_options');
    }
}
