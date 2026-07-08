<?php
namespace Digidirect\AI\Model\Integrations\Rule\Mapping;

abstract class MapperAbstract implements MapperInterface
{
    /**
     * @var \Digidirect\AI\Api\MapperDataRepositoryInterface
     */
    protected $_mapperDataRepository;

    /**
     * MappingAbstract constructor.
     * @param \Digidirect\AI\Api\MapperDataRepositoryInterface $mapperDataRepository
     */
    public function __construct(
        \Digidirect\AI\Api\MapperDataRepositoryInterface $mapperDataRepository
    ) {
        $this->_mapperDataRepository = $mapperDataRepository;
    }

    /**
     * @return mixed
     */
    public function getSavedData()
    {
        return $this->_mapperDataRepository->loadByMappingCode($this->getMapperCode());
    }
}
