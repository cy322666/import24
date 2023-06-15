<?php

namespace App\Console\Commands;

use App\Models\Bitrix\CFLead;
use App\Services\Bitrix;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Illuminate\Console\Command;

class GetCFLeadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.lead.fields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        try {
            $custom_fields = Bitrix::init()
                ->call('crm.lead.fields')
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($custom_fields as $code => $custom_field) {

                $cf_model = CFLead::query()->create([
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

        } catch (InvalidArgumentException $e) {
        } catch (TransportException $e) {
        } catch (BaseException $e) {
        }
    }
}
