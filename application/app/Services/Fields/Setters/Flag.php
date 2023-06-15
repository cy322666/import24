<?php

namespace App\Services\Fields\Setters;

use AmoCRM\Models\CustomFieldsValues\CheckboxCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\CheckboxCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\CheckboxCustomFieldValueModel;

class Flag
{
    private $model;
    private $collection;

    public function __construct()
    {
        $this->model = new CheckboxCustomFieldValuesModel();
    }

    public function setId(int $id): static
    {
        $this->model->setFieldId($id);

        return $this;
    }

    public function setValues($value)
    {
        if ($value === 0 || $value === null) {

            $value = false;
        } else
            $value = true;

        $this->collection = $this->model
            ->setValues((new CheckboxCustomFieldValueCollection())
                ->add((new CheckboxCustomFieldValueModel())
                    ->setValue($value)
                )
            );
    }

    public function getFieldCollection()
    {
        return $this->collection;
    }
}
