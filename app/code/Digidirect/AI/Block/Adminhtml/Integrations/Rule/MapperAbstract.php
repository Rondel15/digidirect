<?php
namespace Digidirect\AI\Block\Adminhtml\Integrations\Rule;

use Digidirect\AI\Model\Integrations\Rule\Mapping\MapperInterface;
use Digidirect\AI\Api\MapperDataRepositoryInterface;

abstract class MapperAbstract extends \Magento\Backend\Block\Template
{
    /**
     * @var MapperInterface
     */
    protected $_mapper;

    /**
     * @var MapperDataRepositoryInterface
     */
    protected $_mapperDataRepository;

    /**
     * @var string
     */
    protected $_template = 'Digidirect_AI::integrations/rule/mapper.phtml';

    /**
     * MapperAbstract constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param MapperInterface $mapper
     * @param MapperDataRepositoryInterface $mapperDataRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        MapperInterface $mapper,
        MapperDataRepositoryInterface $mapperDataRepository,
        array $data = []
    ) {
        $this->_mapper = $mapper;
        $this->_mapperDataRepository = $mapperDataRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    abstract public function getEntityId();

    /**
     * @return string
     */
    abstract public function getDescription();

    /**
     * @return MapperInterface
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    /**
     * @return array
     */
    public function getSavedData()
    {
        return $this->_mapperDataRepository->load(
            $this->getEntityId(),
            $this->_mapper->getMapperCode()
        )->getMapperData();
    }
}
