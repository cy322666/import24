<?php

namespace App\Services\Fields;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Collections\CustomFields\CustomFieldsCollection;
use AmoCRM\Models\CustomFields\TextCustomFieldModel;

class StringCreator
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

        $cf = new TextCustomFieldModel();
        $cf->setName($fieldName);

        $this->customFieldsCollection->add($cf);

        return $customFieldsService->add($this->customFieldsCollection);
    }
}
