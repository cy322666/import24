<?php

namespace App\Console\Commands;

use AmoCRM\Helpers\EntityTypesInterface;
use App\Models\Bitrix\CFDeal;
use App\Services\Fields\FieldsFactory;
use Illuminate\Console\Command;

class CreateFieldsDealCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:deals.fields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $arrayNeedFields = [
            'UF_CRM_1588767503' => 'G:Потенциал заявки',
            'UF_CRM_1588767524' => 'G:Категория',
            'UF_CRM_1588767541' => 'G:Регион',
            'UF_CRM_1588841514' => 'G:Ожидаемое кол-во у.е',
            'UF_CRM_1589115885705' => 'G:Потенциал заявки',
            'UF_CRM_1589116369715' => 'G:Вертикаль',
            'UF_CRM_1589116620760' => 'G:Клиентский сегмент',
            'UF_CRM_1589180430155' => 'G:Категория',
            'UF_CRM_1589183443194' => 'G:Возможные позиции',
            'UF_CRM_1589183650259' => 'G:Регион',
            'UF_CRM_1613888857'    => 'G:Скоринг',
            'UF_CRM_1616155916559' => 'G:Пилот',
            'UF_CRM_1629791117263' => 'G:Тип возможности',
            'UF_CRM_1629792870866' => 'G:Ожидаемое количество локаций',
            'UF_CRM_1630063497997' => 'G:Внешняя ставка',
            'UF_CRM_1630063515692' => 'G:Внутренняя ставка',
            'UF_CRM_1630063579912' => 'G:Длинна смены',
            'UF_CRM_1639150785306' => 'G:Город',
        ];

        foreach ($arrayNeedFields as $fieldCode => $arrayNeedField) {

            $modelField = CFDeal::query()
                ->where('code', $fieldCode)
                ->first();

            if($modelField->status == 1 || $modelField->type == 'enumeration') continue;

            $strategy = FieldsFactory::getFieldService($modelField->type);

            $field = $strategy->create(
                $arrayNeedField,
                $modelField,
                EntityTypesInterface::LEADS
            );

            $modelField->status = 1;
            $modelField->name   = $arrayNeedField;
            $modelField->field_id = $field->first()->getId();
            $modelField->save();

            dd('end');
        }
    }
}
