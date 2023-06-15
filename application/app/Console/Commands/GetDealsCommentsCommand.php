<?php

namespace App\Console\Commands;

use App\Jobs\GetDetailDealJob;
use App\Models\Bitrix\Deal;
use App\Services\Bitrix;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetDealsCommentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.deals.comment';

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
        $deals = Deal::all();

        foreach ($deals as $deal) {

            sleep(1);

            print_r($deal->deal_id."\n");

            $deal_comments = Bitrix::init()
                ->call('crm.timeline.comment.list', [
                    'filter'  => [
                        'ENTITY_ID'   => $deal->deal_id,
                        "ENTITY_TYPE" => "deal",
                    ],
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($deal_comments as $deal_comment) {

                $deal->comments()->create([
                    'created' => $deal_comment['CREATED'],
                    'comment_id' => $deal_comment['ID'],
                    'text' => $deal_comment['COMMENT'],
                    'author_id' => $deal_comment['AUTHOR_ID'],
                    'files' => !empty($deal_comment['FILES']) ? json_encode($deal_comment['FILES'], true) : null,
                ]);
            }
        }
    }
}
