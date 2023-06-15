<?php

namespace App\Console\Commands;

use App\Jobs\GetDetailLeadJob;
use App\Models\Bitrix\Lead;
use App\Services\Bitrix;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Illuminate\Console\Command;

class GetLeadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.leads.list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @throws BaseException
     */
    public function handle()
    {
        $pagination = Bitrix::init()
            ->call('crm.lead.list')
            ->getResponseData()
            ->getPagination();

        for ($offset = 0; $offset < $pagination->getTotal(); $offset += 50) {

            $leads = Bitrix::init()
                ->call('crm.lead.list', [
                    'select' => ['ID'],
                    'start'  => $offset,
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($leads as $lead) {

                $lead_model = Lead::query()->create([
                    'lead_id' => $lead['ID'],
                ]);

                GetDetailLeadJob::dispatch($lead_model);
            }
        }
    }
}
