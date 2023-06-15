<?php

namespace App\Console\Commands;

use App\Jobs\GetDetailCompanyJob;
use App\Models\Bitrix\Company;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Illuminate\Console\Command;
use App\Services\Bitrix;

class GetCompaniesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.companies.list';

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
            ->call('crm.company.list')
            ->getResponseData()
            ->getPagination();

        for ($offset = 0; $offset < $pagination->getTotal(); $offset += 50) {

            $companies = Bitrix::init()
                ->call('crm.company.list', [
                    'select' => ['ID'],
                    'start'  => $offset,
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($companies as $company) {

                $company_model = Company::query()->create([
                    'company_id' => $company['ID'],
                ]);

                GetDetailCompanyJob::dispatch($company_model);
            }
        }
    }
}
