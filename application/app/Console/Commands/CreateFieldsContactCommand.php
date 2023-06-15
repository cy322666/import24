<?php

namespace App\Console\Commands;

use AmoCRM\Helpers\EntityTypesInterface;
use App\Models\Bitrix\CFContact;
use App\Services\Fields\FieldsFactory;
use Illuminate\Console\Command;

class CreateFieldsContactCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:contacts.fields';

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
            'COMMENTS'     => 'G:комментарий',
            'UTM_SOURCE'   => 'G:utm_source',
            'UTM_MEDIUM'   => 'G:utm_medium',
            'UTM_CAMPAIGN' => 'G:utm_campaign',
            'UTM_CONTENT'  => 'G:utm_content',
            'UTM_TERM'     => 'G:utm_term',
            'UF_CRM_1589185637' => 'G:За что отвечает',
        ];

        foreach ($arrayNeedFields as $fieldCode => $arrayNeedField) {

            $modelField = CFContact::query()
                ->where('code', $fieldCode)
                ->first();

            $strategy = FieldsFactory::getFieldService($modelField->type);

            $field = $strategy->create(
                $arrayNeedField,
                $modelField,
                EntityTypesInterface::CONTACTS
            );

            $modelField->status = 1;
            $modelField->name   = $arrayNeedField;
            $modelField->field_id = $field->first()->getId();
            $modelField->save();
        }
    }
}
