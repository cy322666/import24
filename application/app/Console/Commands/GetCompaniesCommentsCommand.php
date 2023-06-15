<?php

namespace App\Console\Commands;

use App\Jobs\GetCommentsDealJob;
use App\Jobs\GetDetailDealJob;
use App\Models\Bitrix\Company;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Deal;
use App\Services\Bitrix;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetCompaniesCommentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.companies.comment';

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
        $companies = Company::all();

        foreach ($companies as $company) {

            sleep(1);

            print_r($company->company_id."\n");

            $company_comments = Bitrix::init()
                ->call('crm.timeline.comment.list', [
                    'filter'  => [
                        'ENTITY_ID'   => $company->company_id,
                        "ENTITY_TYPE" => "company",
                    ],
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($company_comments as $company_comment) {

                $company->comments()->create([
                    'created'    => $company_comment['CREATED'],
                    'comment_id' => $company_comment['ID'],
                    'text'       => $company_comment['COMMENT'],
                    'author_id'  => $company_comment['AUTHOR_ID'],
                    'files'      => !empty($contact_comment['FILES']) ? json_encode($contact_comment['FILES'], true) : null,
                ]);
            }
        }
    }
}
