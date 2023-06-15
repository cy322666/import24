<?php

namespace App\Services\Fields;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Collections\CustomFields\CustomFieldEnumsCollection;
use AmoCRM\Collections\CustomFields\CustomFieldsCollection;
use AmoCRM\Exceptions\AmoCRMApiErrorResponseException;
use AmoCRM\Models\CustomFields\EnumModel;
use AmoCRM\Models\CustomFields\SelectCustomFieldModel;
use AmoCRM\Models\CustomFields\TextCustomFieldModel;

class SelectCreator
{
    private $amoApi;
    private $customFieldsCollection;
    private $enumsCollection;

    public function __construct(AmoCRMApiClient $amoApi)
    {
        $this->amoApi = $amoApi;
        $this->customFieldsCollection = new CustomFieldsCollection();
        $this->enumsCollection = new CustomFieldEnumsCollection();
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

        $cf = (new SelectCustomFieldModel())
            ->setName($fieldName)
//            ->setSort(510)
//            ->setCode($modelField->code);
            ->setEntityType($entityType);

        $items = $modelField->items;
        $sort  = 0;

        foreach ($items as $item) {

            $sort = $sort + 1;

            $this->enumsCollection->add((new EnumModel())
                ->setValue($item->value)
                ->setSort($sort)
//                ->setId($sort)
            );
        }

        $cf->setEnums($this->enumsCollection);

        $this->customFieldsCollection->add($cf);

        try {
            return $customFieldsService->add($this->customFieldsCollection);

        } catch (AmoCRMApiErrorResponseException $exception) {

            dd($exception->getValidationErrors());
        }

    }
}
