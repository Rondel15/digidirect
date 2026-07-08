<?php

namespace Digidirect\Localization\Model\Import\Parser;

use Digidirect\Localization\Model\Import\ParserProcessorInterface;

class Csv implements ParserProcessorInterface
{
    /**
     * @param string $filePath
     * @return array
     */
    public function execute($filePath)
    {
        $result = $fields = [];
        $row = 0;
        if (($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $num = count($data);
                if (empty($fields)) {
                    for ($c=0; $c < $num; $c++) {
                        $fields[$c]  = $data[$c];
                    }
                } else {
                    foreach ($data as $key => $item) {
                        $result[$row][$fields[$key]] = $item;
                    }
                }
                $row++;
            }
            fclose($handle);
        }
        return $result;
    }
}
