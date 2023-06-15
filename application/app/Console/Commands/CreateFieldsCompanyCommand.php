<?php

namespace App\Console\Commands;

use AmoCRM\Helpers\EntityTypesInterface;
use App\Models\Bitrix\CFCompany;
use App\Models\Bitrix\CFContact;
use App\Services\Fields\FieldsFactory;
use Illuminate\Console\Command;

class CreateFieldsCompanyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:companies.fields';

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
//            'UF_CRM_1630581647873' => '№ Договора',
            'UF_CRM_1630430863899' => 'G:ЭДО',
            'INDUSTRY' => 'G:Сфера деятельности',
//            'UF_CRM_1630430735841' => 'Адрес',
//            'UF_CRM_1630430689495' => 'Расчетный счет',
//            'UF_CRM_1630430664310' => 'БИК банка',
//            'UF_CRM_1630430649726' => 'КПП',
            'UF_CRM_1630430616508' => 'G:Срок действия',
//            'UF_CRM_1630430572799' => 'Дата подписания Договора',
//            'UF_CRM_1630430556180' => 'ИНН',
            'UF_CRM_1630429676791' => 'G:Количество магазинов (мини)',
            'UF_CRM_1630429662265' => 'G:Количество магазинов (супер)',
            'UF_CRM_1630429645413' => 'G:Количество магазинов (мега)',
            'UF_CRM_1630429616989' => 'G:Количество магазинов (всего)',
            'UF_CRM_1630429593605' => 'G:Количество городов',
            'UF_CRM_1630429580849' => 'G:Количество сотрудников (штат)',
            'UF_CRM_1630427491779' => 'G:Рассылка клиенту',
            'UF_CRM_1630427342496' => 'G:Penetration level (%)',
            'UF_CRM_1630426664798' => 'G:Неактивные локации',
            'UF_CRM_1630426644076' => 'G:Активные локации',
            'UF_CRM_1630426611965' => 'G:Кол-во локаций',
            'UF_CRM_1630426569510' => 'G:Сегмент',
            'UF_CRM_1630409299160' => 'G:Ссылка на папку с документами по клиенту',
        ];

        foreach ($arrayNeedFields as $fieldCode => $arrayNeedField) {

            $modelField = CFCompany::query()
                ->where('code', $fieldCode)
                ->first();

            if($modelField->status == 1) continue;

            $strategy = FieldsFactory::getFieldService($modelField->type);

            $field = $strategy->create(
                $arrayNeedField,
                $modelField,
                EntityTypesInterface::COMPANIES
            );

            $modelField->status = 1;
            $modelField->name = $arrayNeedField;
            $modelField->field_id = $field->first()->getId();
            $modelField->save();
        }
    }
}
