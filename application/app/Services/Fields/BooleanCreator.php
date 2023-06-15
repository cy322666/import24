<?php

namespace App\Services\Fields;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Collections\CustomFields\CustomFieldsCollection;
use AmoCRM\Exceptions\AmoCRMApiErrorResponseException;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Models\CustomFields\CheckboxCustomFieldModel;
use AmoCRM\Models\CustomFields\NumericCustomFieldModel;
use Laravel\Octane\Exceptions\DdException;

class BooleanCreator
{
    private AmoCRMApiClient $amoApi;
    private CustomFieldsCollection $customFieldsCollection;

    public function __construct(AmoCRMApiClient $amoApi)
    {
        $this->amoApi = $amoApi;
        $this->customFieldsCollection = new CustomFieldsCollection();
    }

    /**
     * @throws InvalidArgumentException
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     * @throws AmoCRMoAuthApiException|DdException
     */
    public function create($fieldName, $modelField, $entityType): CustomFieldsCollection|BaseApiCollection
    {
        $customFieldsService = $this->amoApi->customFields($entityType);

        $cf = new CheckboxCustomFieldModel();
        $cf->setName($fieldName);

        $this->customFieldsCollection->add($cf);

        try {
            return $customFieldsService->add($this->customFieldsCollection);

        } catch (AmoCRMApiErrorResponseException $exception) {

            dd($customFieldsService->getLastRequestInfo(), $exception->getValidationErrors());
        }
    }
}
