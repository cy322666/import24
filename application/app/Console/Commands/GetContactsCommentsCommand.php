<?php

namespace App\Console\Commands;

use App\Jobs\GetDetailDealJob;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Deal;
use App\Services\Bitrix;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetContactsCommentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.contacts.comment';

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
        $contacts = Contact::query()->where('contact_id', '>', 3121)->get();

        foreach ($contacts as $contact) {

            sleep(1);

            print_r($contact->contact_id."\n");

            $contact_comments = Bitrix::init()
                ->call('crm.timeline.comment.list', [
                    'filter'  => [
                        'ENTITY_ID'   => $contact->contact_id,
                        "ENTITY_TYPE" => "contact",
                    ],
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($contact_comments as $contact_comment) {

                $contact->comments()->create([
                    'created'    => $contact_comment['CREATED'],
                    'comment_id' => $contact_comment['ID'],
                    'text'       => $contact_comment['COMMENT'],
                    'author_id'  => $contact_comment['AUTHOR_ID'],
                    'files'      => !empty($contact_comment['FILES']) ? json_encode($contact_comment['FILES'], true) : null,
                ]);
            }
        }
    }
}
