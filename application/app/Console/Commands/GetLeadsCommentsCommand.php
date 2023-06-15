<?php

namespace App\Console\Commands;

use App\Jobs\GetCommentsDealJob;
use App\Jobs\GetDetailDealJob;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Deal;
use App\Models\Bitrix\Lead;
use App\Services\Bitrix;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetLeadsCommentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.leads.comment';

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
        $leads = Lead::all();

        foreach ($leads as $lead) {

            sleep(1);

            print_r($lead->lead_id."\n");

            $lead_comments = Bitrix::init()
                ->call('crm.timeline.comment.list', [
                    'filter'  => [
                        'ENTITY_ID'   => $lead->lead_id,
                        "ENTITY_TYPE" => "lead",
                    ],
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($lead_comments as $lead_comment) {

                $lead->comments()->create([
                    'created'    => $lead_comment['CREATED'],
                    'comment_id' => $lead_comment['ID'],
                    'text'       => $lead_comment['COMMENT'],
                    'author_id'  => $lead_comment['AUTHOR_ID'],
                    'files'      => !empty($lead_comment['FILES']) ? json_encode($lead_comment['FILES'], true) : null,
                ]);
            }
        }
    }
}
