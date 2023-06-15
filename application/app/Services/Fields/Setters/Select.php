<?php

namespace App\Services\Fields\Setters;

use AmoCRM\Models\CustomFieldsValues\SelectCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\SelectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\SelectCustomFieldValueModel;
use App\Models\Bitrix\CFCompany;
use App\Models\Bitrix\Company_CF;
use App\Models\Bitrix\Items_CFCompany;
use App\Models\Bitrix\Items_CFDeal;
use App\Models\Bitrix\Items_CFLead;

class Select
{
    private $model;
    private $collection;

    public function __construct()
    {
        $this->model = new SelectCustomFieldValuesModel();
    }

    public function setId(int $id): static
    {
        $this->model->setFieldId($id);

        return $this;
    }

    public function setValues($value)
    {
        if (!$value || $value == 0 || $value == ' ') {//is_string($value) == true ||

            return false;
        }

        try {

            if ($this->model->getFieldId() == 1154979 ||
                $this->model->getFieldId() == 1154975 ||
                $this->model->getFieldId() == 1155011) {

                $value = json_decode($value);

                if (!is_string($value) && !is_int($value)) {

                    $values = $value;

                    foreach ($values as $value) {

                        $field = Items_CFLead::query()
                            ->where('item_type_id', (int)$value)
                            ->first();

                        if ($field == null) {

                            print_r('Не нашли значение для '."\n");
                            print_r($this->model);
                        } else {
                            $field = $field->amo_enum_id;
                        }

                        $this->collection = $this->model
                            ->setValues((new SelectCustomFieldValueCollection())
                                ->add(
                                    (new SelectCustomFieldValueModel())
                                        ->setEnumId($field)
                                )
                            );
                    }
                    return true;
                }
            }

            $field = Items_CFLead::query()
                ->where('item_type_id', (int)$value)
                ->first();

            if ($field == null) {

                print_r('Не нашли значение для '."\n");
                print_r($this->model);
            } else {
                $field = $field->amo_enum_id;
            }

            $this->collection = $this->model
                ->setValues((new SelectCustomFieldValueCollection())
                    ->add(
                        (new SelectCustomFieldValueModel())
                            ->setEnumId($field)
                    )
                );

        } catch (\Throwable $exception){
            dd($exception->getMessage(), $this->model);
        }
    }

    public function getFieldCollection()
    {
        return $this->collection;
    }
}
