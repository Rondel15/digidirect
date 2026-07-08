<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

use Plumrocket\Newsletterpopup\Model\Mailchimp\Error as PrMailchimpError;
use Plumrocket\Newsletterpopup\Model\Mailchimp\HttpError as PrMailchimpHttpError;

class Mcapi
{
    const ALL_LISTS = 'all_lists';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var false|resource
     */
    private $ch;

    /**
     * @var string
     */
    private $root    = 'https://api.mailchimp.com/3.0';

    /**
     * @var bool
     */
    private $debug   = false;

    const POST      = 'POST';
    const GET       = 'GET';
    const PATCH     = 'PATCH';
    const DELETE    = 'DELETE';
    const PUT       = 'PUT';

    const SUBSCRIBED = 'subscribed';
    const PENDING = 'pending';
    const UNSUBSCRIBED = 'unsubscribed';

    /**
     * @var bool
     */
    private $init = false;

    /**
     * @var string
     */
    public $errorMessage;

    /**
     * Mcapi constructor.
     *
     * @param string|null $apikey
     * @param bool        $secure
     */
    public function __construct($apikey = null, $secure = false) //@codingStandardsIgnoreLine
    {
        if (null !== $apikey) {
            $this->initApi($apikey);
        }
    }

    /**
     * @param string $apiKey
     * @return $this
     */
    public function initApi($apiKey)
    {
        if ($this->init) {
            return $this;
        }

        $this->ch = curl_init();

        $this->setApiKey($apiKey);

        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Ebizmart-MailChimp-PHP/3.0.0');
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);

        $this->init = true;

        return $this;
    }

    private function setApiKey($apiKey)
    {
        $this->root = 'https://api.mailchimp.com/3.0';
        $this->apiKey = $apiKey;
        $dc = 'us1';
        if (false !== strpos($this->apiKey, '-')) {
            list($key, $dc) = explode('-', $this->apiKey, 2);
            if (! $dc) {
                $dc = 'us1';
            }
        }
        $this->root = str_replace('https://api', 'https://' . $dc . '.api', $this->root);
        $this->root = rtrim($this->root, '/') . '/';
        curl_setopt($this->ch, CURLOPT_USERPWD, 'noname:' . $this->apiKey);
    }

    /**
     * @param string $url
     * @param array  $params
     * @param string $method
     * @return mixed
     * @throws PrMailchimpError
     * @throws PrMailchimpHttpError
     */
    public function call($url, $params, $method = self::GET)
    {
        $hasParams = true;
        if ((is_array($params) && empty($params)) || null === $params) {
            $hasParams = false;
        }

        if ($hasParams && self::GET !== $method) {
            $params = json_encode($params);
        }

        $ch = $this->ch;
        if ($hasParams && self::GET !== $method) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, null);
            if ($hasParams) {
                $_params = http_build_query($params);
                $url .= '?' . $_params;
            }
        }
        curl_setopt($ch, CURLOPT_URL, $this->root . $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $response_body = curl_exec($ch);

        $info = curl_getinfo($ch);
        if (curl_error($ch)) {
            throw new PrMailchimpHttpError($url, $method, $params, '', curl_error($ch));
        }

        $result = json_decode($response_body, true);

        if ((int) $info['http_code'] >= 400) {
            if (is_array($result)) {
                $detail = array_key_exists('detail', $result) ? $result['detail'] : '';
                $errors = array_key_exists('errors', $result) ? $result['errors'] : null;
                $title = array_key_exists('title', $result) ? $result['title'] : '';
                throw new PrMailchimpError(
                    $this->root . $url, $method, $params, $title, $detail, $errors
                );
            }

            throw new PrMailchimpError(
                $this->root . $url,
                $method,
                $params,
                $result
            );
        }

        return $result;
    }

    public function lists(
        $id = null,
        $fields = null,
        $excludeFields = null,
        $count = self::ALL_LISTS,
        $offset = null,
        $beforeDateCreated = null,
        $sinceDateCreated = null,
        $beforeCampaignLastSent = null,
        $sinceCampaignLastSent = null,
        $email = null
    ) {
        $_params = [];
        if ($fields) {
            $_params['fields'] = $fields;
        }
        if ($excludeFields) {
            $_params['exclude_fields'] = $excludeFields;
        }
        if ($count) {
            $_params['count'] = $count;
        }

        if ($count === self::ALL_LISTS) {
            $_params['count'] = 25;
        }

        if ($offset) {
            $_params['offset'] = $offset;
        }
        if ($beforeDateCreated) {
            $_params['before_date_created'] = $beforeDateCreated;
        }
        if ($sinceDateCreated) {
            $_params['since_date_created'] = $sinceDateCreated;
        }
        if ($beforeCampaignLastSent) {
            $_params['before_campaigns_last_sent'] = $beforeCampaignLastSent;
        }
        if ($sinceCampaignLastSent) {
            $_params['since_campaign_last_sent'] = $sinceCampaignLastSent;
        }
        if ($email) {
            $_params['email'] = $email;
        }

        if ($id) {
            return ['data' => $this->call('lists/' . $id, $_params)['lists']];
        }

        $result = $this->call('lists/', $_params);
        $loadedCount = count($result['lists']);
        $totalItems = (int)$result['total_items'];

        if ($count !== self::ALL_LISTS || $totalItems === $loadedCount) {
            return ['data' => $result['lists']];
        }

        // load all lists
        while ($totalItems > $loadedCount) {
            $newPart = $this->lists($id, $fields, $excludeFields, $_params['count'], $loadedCount);
            $loadedCount += count($newPart['data']);
            array_push($result['lists'], ...$newPart['data']);
        }

        return ['data' => $result['lists']];
    }

    /**
     * @return array
     */
    public function getAccountDetails()
    {
        $response = [];

        try {
            $response = $this->call('/', []);
        } catch (PrMailchimpError $exception) {
            $this->errorMessage = $exception->getMessage();
        }

        return $response;
    }

    /**
     * @return string
     */
    public function ping()
    {
        return $this->call('ping/', [])['health_status'];
    }

    /**
     * @param        $id
     * @param        $email_address
     * @param null   $merge_vars
     * @param string $email_type
     * @param bool   $double_optin
     * @return mixed
     */
    public function listSubscribe(
        $id,
        $email_address,
        $merge_vars = null,
        $email_type = 'html',
        $double_optin = true
    ) {
        $params = [];
        $params['email_address'] = $email_address;
        $params['status'] = $double_optin ? self::PENDING : self::SUBSCRIBED;
        $params['email_type'] = $email_type;

        if (! empty($merge_vars)) {
            $params['merge_fields'] = $merge_vars;
        }

        return $this->call('lists/' . $id . '/members', $params, self::POST);
    }
}
