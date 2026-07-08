<?php
namespace Digidirect\AI\Model\Logger\Types;

/**
 * Interface TypesInterface
 * @package Digidirect\AI\Model\Logger\Types
 */
interface TypesInterface
{

    /**
     * @param string $message
     * @param array $data
     * @return mixed
     */
    public function record($message, $data = []);

    /**
     * @param string $message
     * @param string $level
     * @return $this
     */
    public function addHeader($message, $level = null);

    /**
     * @param string|array $identifiers
     * @return $this
     * @throws \Exception
     */
    public function addIdentifyingParams($identifiers);

    /**
     * @return mixed
     */
    public function getRecordIdentifier();
}
