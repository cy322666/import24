<?php

namespace App\Console\Commands;

use App\Models\Bitrix\CFContact;
use App\Services\Bitrix;
use Illuminate\Console\Command;

class GetCFContactCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.contact.fields';

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
        $custom_fields = Bitrix::init()
            ->call('crm.contact.fields')
            ->getResponseData()
            ->getResult()
            ->getResultData();

        foreach ($custom_fields as $code => $custom_field) {

            $cf_model = CFContact::query()->create([
                'code' => $code,
                'type' => $custom_field['type'],
                'isRequired' => $custom_field['isRequired'],
                'isReadOnly' => $custom_field['isReadOnly'],
                'isImmutable' => $custom_field['isImmutable'],
                'isMultiple' => $custom_field['isMultiple'],
                'isDynamic' => $custom_field['isDynamic'],
                'title' => $custom_field['title'],
                'listLabel' => $custom_field['listLabel'] ?? null,
                'formLabel' => $custom_field['formLabel'] ?? null,
                'filterLabel' => $custom_field['filterLabel'] ?? null,
                'settings' => !empty($custom_field['settings']) ? json_encode($custom_field['settings'], true) : null,
            ]);

            if(!empty($custom_field['items'])) {

                foreach ($custom_field['items'] as $item) {

                    $cf_model->items()->create([
                        'item_type_id' => $item['ID'],
                        'value' => $item['VALUE'],
                    ]);
                }
            }
        }
    }
}
