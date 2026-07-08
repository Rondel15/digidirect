<?php
namespace Digidirect\AbstractEntity\Plugin\Framework\Acl\AclResource\Config\Reader;

use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity as AbstractEntityController;
use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity\Save as SaveController;
use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity\Delete as DeleteController;
use Digidirect\AbstractEntity\Model\AttributeSetRepository;
use Magento\Framework\Exception\LocalizedException;

class Filesystem
{
    /**
     * @var AttributeSetRepository
     */
    protected $attributeSetRepository;

    /**
     * @param AttributeSetRepository $attributeSetRepository
     */
    public function __construct(
        AttributeSetRepository $attributeSetRepository
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * @param \Magento\Framework\Acl\AclResource\Config\Reader\Filesystem $subject
     * @param array $result
     * @return array
     */
    public function afterRead(
        \Magento\Framework\Acl\AclResource\Config\Reader\Filesystem $subject,
        array $result
    ) {
        try {
            /** \Magento\Eav\Model\Entity\Attribute\Set[] $set */
            $sets = $this->attributeSetRepository->getList()->getItems();
        } catch (LocalizedException $e) {
            return $result;
        }

        if (empty($sets)) {
            return $result;
        }

        foreach ($result['config']['acl']['resources'] as &$adminResource) {
            if ($adminResource['id'] == 'Magento_Backend::admin') {
                foreach ($adminResource['children'] as &$adminAcl) {
                    if ($adminAcl['id'] == 'Digidirect_Utilities::Digidirect') {
                        foreach ($adminAcl['children'] as &$digidirectAcl) {
                            if ($digidirectAcl['id'] == 'Digidirect_AbstractEntity::menu') {
                                foreach ($sets as $set) {
                                    $id = $set->getAttributeSetId();
                                    $digidirectAcl['children'][] = [
                                        'id' => AbstractEntityController::ADMIN_RESOURCE_PREFIX . $id,
                                        'title' => $set->getAttributeSetName(),
                                        'sortOrder' => $id * 10,
                                        'disabled' => false,
                                        'children' => [
                                            [
                                                'id' => SaveController::ADMIN_RESOURCE_PREFIX . $id,
                                                'title' => __('Save'),
                                                'sortOrder' => 10,
                                                'disabled' => false,
                                                'children' => []
                                            ],
                                            [
                                                'id' => DeleteController::ADMIN_RESOURCE_PREFIX . $id,
                                                'title' => __('Delete'),
                                                'sortOrder' => 20,
                                                'disabled' => false,
                                                'children' => []
                                            ],
                                        ]
                                    ];
                                }
                                break(3);
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
}
