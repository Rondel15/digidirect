<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Category;

use Digidirect\AI\Model\Lib\Entity\Import\ImportAbstract;
use Digidirect\AI\Model\Lib\Entity\Import\Category\Model\ProcessorFactory;
use Digidirect\AI\Model\Lib\Entity\Import\Category\Model\Processor;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Digidirect\AI\Model\Logger\LoggerInterface;

class Category extends ImportAbstract implements CategoryInterface
{
    const ENTITY_NAME = 'category';

    /**
     * @var Processor
     */
    protected $categoryProcessor;

    /**
     * ImportAbstract constructor.
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param ProcessorFactory $categoryProcessorFactory
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        ProcessorFactory $categoryProcessorFactory,
        array $preparers = []
    ) {
        parent::__construct(
            $beforeSaveValidator,
            $beforeUpdateValidator,
            $logger,
            $preparers
        );
        $this->categoryProcessor = $categoryProcessorFactory->create([
            'logger' => $logger
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::ENTITY_NAME;
    }

    /**
     * @param array $category
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _save(array $category, $updateOnDuplicate = true)
    {
        return $this->_saveBunch([$category], $updateOnDuplicate);
    }

    /**
     * @param array $categories
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _saveBunch(array $categories, $updateOnDuplicate = true)
    {
        return $this->categoryProcessor->save($categories, $updateOnDuplicate);
    }

    /**
     * @param array $category
     * @return bool
     */
    protected function _update(array $category)
    {
        return $this->_updateBunch([$category]);
    }

    /**
     * @param array $categories
     * @return bool
     */
    protected function _updateBunch(array $categories)
    {
        return $this->categoryProcessor->update($categories);
    }

    /**
     * @param array $category
     * @return bool
     */
    public function delete(array $category)
    {
        return $this->deleteBunch([$category]);
    }

    /**
     * @param array $categories
     * @return bool
     */
    public function deleteBunch(array $categories)
    {
        $categories = $this->getValidBunch($this->_beforeUpdateValidator, $categories);
        if (!empty($categories)) {
            return $this->categoryProcessor->delete($categories);
        }
        return false;
    }
}
