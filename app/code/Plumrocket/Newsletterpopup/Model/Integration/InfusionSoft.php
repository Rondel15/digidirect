<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

use Plumrocket\Newsletterpopup\Helper\Data as DataHelper;

/**
 * Class InfusionSoft
 * Integration for https://www.infusionsoft.com
 */
class InfusionSoft extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'infusionsoft';

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();
        $this->setAppName($this->getConfigValue('app_name'));
        $this->setApiEndpoint('/api/xmlrpc');
        $this->setDataFormat(self::DATA_FORMAT_XML);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegrationId()
    {
        return self::INTEGRATION_ID;
    }

    /**
     * @return bool
     */
    public function canUseGeneralContactList()
    {
        return true;
    }

    /**
     * @param \Magento\Framework\HTTP\ClientInterface $curlClient
     * @return $this
     */
    protected function beforeMakeRequest(\Magento\Framework\HTTP\ClientInterface $curlClient)
    {
        parent::beforeMakeRequest($curlClient);
        $curlClient->setOption(CURLOPT_HEADERFUNCTION, [$this, 'parseHeadersFix']);

        return $this;
    }

    /**
     * @param resource $ch curl handle, not needed
     * @param string $data
     * @return int
     */
    public function parseHeadersFix($ch, $data)
    {
        return strlen($data);
    }

    /**
     * Add contact to InfusionSoft
     * $listIds parameter is never used because integration does not support lists
     *
     * @param $email
     * @param $listIds
     * @param null $data
     * @return mixed
     */
    public function addContactToList($email, $listIds, $data = null)
    {
        $email = trim($email);
        $listIds = is_array($listIds) ? $listIds : [(string)$listIds];

        if (empty($email) || empty($listIds)) {
            return false;
        }

        return in_array(DataHelper::DEFAULT_GENERAL_LIST_NAME, $listIds)
            ? $this->addContact($email, $data)
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function callAPIResource($url, $params = null, $method = "GET", $encodeParams = null)
    {
        if (! $params) {
            return false;
        }

        if (null === $encodeParams) {
            $encodeParams = self::DATA_FORMAT_XML;
        }

        $url = $this->getBaseApiUrl();

        return $this->sendRequestByCurl($url, $params, $encodeParams);
    }

    /**
     * Set App name and change API URL
     *
     * @param $appName
     * @return $this
     */
    public function setAppName($appName)
    {
        parent::setAppName($appName);
        $this->setApiUrl('https://' . $this->getAppName() . '.infusionsoft.com');

        return $this;
    }

    /**
     * Retrieve data of account
     *
     * @return mixed
     */
    public function getAccountInfo()
    {
        $apiKey = $this->getApiKey();
        $payload = <<<XML
<?xml version="1.0"?>
<methodCall>
  <methodName>DataService.getAppSetting</methodName>
  <params>
    <param>
      <value><string>{$apiKey}</string></value>
    </param>
    <param>
      <value><string>Application</string></value>
    </param>
    <param>
      <value><string>api_passphrase</string></value>
    </param>
  </params>
</methodCall>
XML;

        return $this->callAPIResource(null, $payload);
    }

    /**
     * Add contact to service
     *
     * @param $email
     * @param null $data
     * @return mixed
     */
    public function addContact($email, $data = null)
    {
        $apiKey = $this->getApiKey();
        $members = $this->prepareContactDataMembers($data);
        $payload = <<<XML
<?xml version="1.0"?>
<methodCall>
    <methodName>ContactService.addWithDupCheck</methodName>
        <params>
            <param>
                <value><string>{$apiKey}</string></value>
            </param>
            <param>
                <value>
                    <struct>
                        <member>
                            <name>Email</name>
                            <value><string>{$email}</string></value>
                        </member>
                        {$members}
                    </struct>
                </value>
            </param>
            <param>
                <value><string>EmailAndName</string></value>
            </param>
        </params>
</methodCall>
XML;

        $apiResult = $this->callAPIResource(null, $payload);

        if (! empty($apiResult['params']['param']['value']['i4'])) {
            return (int)$apiResult['params']['param']['value']['i4'];
        } else {
            $this->logFailAddContact($apiResult, $email);
        }

        return false;
    }

    /**
     * @param $data
     * @return string
     */
    private function prepareContactDataMembers($data)
    {
        $result = '';
        $data = $this->prepareDataForContact($data);

        foreach ($data as $key => $value) {
            if ('email' == mb_strtolower($key)) {
                continue;
            }

            $result .= '<member><name>' . $key . '</name><value><string>' . $value . '</string></value></member>';
        }

        return $result;
    }
}
