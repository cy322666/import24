<?php

namespace App\Console\Commands;

use App\Jobs\GetDetailDealJob;
use App\Jobs\GetDetailLeadJob;
use App\Models\Bitrix\CFDeal;
use App\Models\Bitrix\CFLead;
use App\Models\Bitrix\Deal;
use App\Models\Bitrix\Lead;
use App\Services\Bitrix;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetDealsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.deals.list';

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
     * @throws BaseException
     */
    public function handle()
    {
        $pagination = Bitrix::init()
            ->call('crm.deal.list', [
                'filter'  => ['CATEGORY_ID' => "21"],
            ])
            ->getResponseData()
            ->getPagination();

        for ($offset = 0; $offset < $pagination->getTotal(); $offset += 50) {

            $deals = Bitrix::init()
                ->call('crm.deal.list', [
                    'filter'  => ['CATEGORY_ID' => "21"],
                    'select' => ['ID'],
                    'start'  => $offset,
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($deals as $deal) {

                try {

                    $deal_model = Deal::query()->create([
                        'deal_id' => $deal['ID'],
                    ]);

                    GetDetailDealJob::dispatch($deal_model);

                } catch (\Throwable $exception) {

                    Log::alert(__METHOD__.' : '.$exception->getMessage());
                }
            }
        }
    }
}
