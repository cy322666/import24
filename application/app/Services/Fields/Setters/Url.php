<?php

namespace App\Services\Fields\Setters;

use AmoCRM\Models\CustomFields\UrlCustomFieldModel;
use AmoCRM\Models\CustomFieldsValues\UrlCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\UrlCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\UrlCustomFieldValueModel;

class Url
{
    private $model;
    private $collection;

    public function __construct()
    {
        $this->model = new UrlCustomFieldValuesModel();
    }

    public function setId(int $id): static
    {
        $this->model->setFieldId($id);

        return $this;
    }

    public function setValues($value)
    {
        $this->collection = $this->model
            ->setValues((new UrlCustomFieldValueCollection())
                ->add((new UrlCustomFieldValueModel())
                    ->setValue($value)
                )
            );
    }

    public function getFieldCollection()
    {
        return $this->collection;
    }
}
