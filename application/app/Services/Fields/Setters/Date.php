<?php

namespace App\Services\Fields\Setters;

use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Models\CustomFieldsValues\DateCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\DateCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\DateCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;

class Date
{
    private $model;
    private $collection;

    public function __construct()
    {
        $this->model = new DateCustomFieldValuesModel();
    }

    public function setId(int $id): static
    {
        $this->model->setFieldId($id);

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setValues($value)
    {
        $this->collection = $this->model
            ->setValues((new DateCustomFieldValueCollection())
                ->add((new DateCustomFieldValueModel())
                    ->setValue($value)
                )
            );
    }

    public function getFieldCollection()
    {
        return $this->collection;
    }
}
