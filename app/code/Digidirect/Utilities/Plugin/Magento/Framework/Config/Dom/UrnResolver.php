<?php
namespace Digidirect\Utilities\Plugin\Magento\Framework\Config\Dom;

use Magento\Framework\Config\Dom\UrnResolver as UrnResolverOriginal;

class UrnResolver
{
    /**
     * @var array
     */
    protected $overwrittenSchemas;

    /**
     * @param array $overwrittenSchemas
     */
    public function __construct(
        array $overwrittenSchemas = []
    ) {
        $this->overwrittenSchemas = $overwrittenSchemas;
    }

    /**
     * Before GetRealPath plugin
     * @param UrnResolverOriginal $subject
     * @param string $schema
     * @return string
     */
    public function beforeGetRealPath(UrnResolverOriginal $subject, $schema)
    {
        if (isset($this->overwrittenSchemas[$schema])) {
            $schema = $this->overwrittenSchemas[$schema];
        }
        return [$schema];
    }
}
