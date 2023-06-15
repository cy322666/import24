<?php

namespace App\Services;

use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Monolog\Logger;

class Bitrix
{
    private $services;
    /**
     * @throws InvalidArgumentException
     * @throws BaseException
     */
    public function init()
    {
        $this->services = (new \Bitrix24\SDK\Core\CoreBuilder())
            ->withCredentials(
                new Credentials(
                    new WebhookUrl(env('B24_WEBHOOK_URL')),
                    null,
                    null,
                    null,
                    ))
            ->withLogger(new Logger('stack'))//INFO
            ->build();

        return $this;
    }

    public function searchOrCreateContact()
    {
        $contacts = $this->searchContact();

        if (count($contacts)) {

        } else {

            $this->createContact();
        }
    }

    public function createLead(array $params)
    {
        return $this
            ->services
            ->call('crm.deal.add', [
                'fields' => [
                    'TITLE'    => $params['title'],
                    "TYPE_ID"  => $params['type_id'],//"GOODS",
                    "STAGE_ID" => $params['stage_id'],// "NEW",
                    "CONTACT_ID" => $params['contact_id'],
                    "OPENED" => "Y",
                    //"ASSIGNED_BY_ID": 1,
                ],
            ])
            ->getResponseData()
            ->getResult()
            ->getResultData();
    }

    private function searchContact(string $query)
    {
        return $this
            ->services
            ->call('crm.contact.list', [
                'filter' => ["PHONE" => $query ],
                'select' => ["ID"]
            ])
            ->getResponseData()
            ->getResult()
            ->getResultData();
    }

    private function createContact(array $params)
    {
        return $this
            ->services
            ->call('crm.contact.add', [
                'fields' => [
                    'NAME' => $params['name'],
                    'SECOND_NAME' => $params['surname'],
                    'PHONE' => [["VALUE" => $params['phone'], "VALUE_TYPE" => "WORK"]],
                    'EMAIL' => [["VALUE" => $params['email'], "VALUE_TYPE" => "WORK"]],
                ],
            ])
            ->getResponseData()
            ->getResult()
            ->getResultData();
    }
}
