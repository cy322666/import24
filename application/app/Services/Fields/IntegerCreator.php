<?php

namespace App\Services\Fields;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Collections\CustomFields\CustomFieldsCollection;
use AmoCRM\Exceptions\AmoCRMApiErrorResponseException;
use AmoCRM\Models\CustomFields\NumericCustomFieldModel;

class IntegerCreator
{
    private $amoApi;
    private $customFieldsCollection;

    public function __construct(AmoCRMApiClient $amoApi)
    {
        $this->amoApi = $amoApi;
        $this->customFieldsCollection = new CustomFieldsCollection();
    }

    /**
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     * @throws \AmoCRM\Exceptions\AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     */
    public function create($fieldName, $modelField, $entityType): CustomFieldsCollection|BaseApiCollection
    {
        $customFieldsService = $this->amoApi->customFields($entityType);

        $cf = new NumericCustomFieldModel();
        $cf->setName($fieldName);

        $this->customFieldsCollection->add($cf);

        try {
            return $customFieldsService->add($this->customFieldsCollection);

        } catch (AmoCRMApiErrorResponseException $exception) {

            dd($customFieldsService->getLastRequestInfo(), $exception->getValidationErrors());
        }
    }
}
