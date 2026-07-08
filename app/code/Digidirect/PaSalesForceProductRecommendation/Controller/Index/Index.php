<?php

declare(strict_types=1);

namespace Digidirect\PaSalesForceProductRecommendation\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\RawFactory;

class Index implements ActionInterface {

    /**
     * @var  RawFactory
    */

    protected $resultFactory;

    /**
     * Index constructor
     * 
     * @param RawFactory $resultFactory
    */


    public function __construct(RawFactory $resultFactory) {
        $this->resultFactory = $resultFactory;
    }

    public function execute() {
        // die('Test module');
        return $this->resultFactory->create()->setContents('

          <p id="retrievepa_id2"></p>
          <input type="text" id="retrievepa_id">

          <script type="text/javascript">

            const loadconfigdata = localStorage.getItem("configData-dbd6c84f-2332-ec11-aae9-02dca44cceec");

            const myObj = JSON.parse(loadconfigdata);

            document.getElementById("retrievepa_id").value = myObj.c;

            var magento_pa_id = document.getElementById("retrievepa_id").value;

            localStorage.setItem("Magento_PA_Id", magento_pa_id); 

          </script>
        

        ');
    }
}