<?php

namespace App\Services\Fields\Setters;

use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;

class Integer
{
    private $model;
    private $collection;

    public function __construct()
    {
        $this->model = new NumericCustomFieldValuesModel();
    }

    public function setId(int $id): static
    {
        $this->model->setFieldId($id);

        return $this;
    }

    public function setValues($value)
    {
        $this->collection = $this->model
            ->setValues((new NumericCustomFieldValueCollection())
                ->add((new NumericCustomFieldValueModel())
                    ->setValue($value)
                ),
            );
    }

    public function getFieldCollection()
    {
        return $this->collection;
    }
}
