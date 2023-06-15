<?php

namespace App\Services\Fields\Setters;

use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;

class Text
{
    private $model;
    private $collection;

    public function __construct()
    {
        $this->model = new TextCustomFieldValuesModel();
    }

    public function setId(int $id): static
    {
        $this->model->setFieldId($id);

        return $this;
    }

    public function setValues($value)
    {
        $this->collection = $this->model
            ->setValues((new TextCustomFieldValueCollection())
                ->add((new TextCustomFieldValueModel())
                    ->setValue($value)
                ),
            );
    }

    public function getFieldCollection()
    {
        return $this->collection;
    }
}
