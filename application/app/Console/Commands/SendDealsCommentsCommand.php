<?php

namespace App\Console\Commands;

use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\NoteType\CommonNote;
use App\Models\Account;
use App\Models\Bitrix\ContactComment;
use App\Models\Bitrix\Deal;
use App\Models\Bitrix\DealComment;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Collection\Collection;

class SendDealsCommentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.deals.comments.send';

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
        $contactComments = DealComment::query()
            ->where('status', 0)
            ->get()
            ->groupBy('deal_id');

        $amoApi = (new Client())->getInstance(Account::all()->first());

        foreach ($contactComments as $contactComment) {

            $contact = Deal::query()
                ->where('deal_id', $contactComment->first()->deal_id)
                ->firstOrFail();

            if ($contactComment->count() > 1) {

                $text = $contactComment->implode('text', "\n\n");

            } else {
                $contactComment = $contactComment->first();
                $text = $contactComment->text;
            }

            $notesCollection = new NotesCollection();

            $serviceNote = new CommonNote();
            $serviceNote->setEntityId(1)
                ->setEntityId($contact->entity_id)
                ->setText($text)
                ->setCreatedBy(0);

            $notesCollection->add($serviceNote);

            try {
                $contactNotesService = $amoApi->notes(EntityTypesInterface::LEADS);
                $contactNotesService->add($notesCollection);

                if ($contactComment instanceof Collection) {

                    foreach ($contactComment as $comment) {

                        $comment->status = 1;
                        $comment->save();
                    }
                } elseif($contactComment instanceof ContactComment) {

                    $contactComment->status = 1;
                    $contactComment->save();
                }

                DB::table('deal_comments')
                    ->where('deal_id', $contactComment->first()->deal_id)
                    ->update([
                        'status' => 1
                    ]);

                sleep(1);
                print_r('SUCCESS ' . Carbon::now()->format('H:i:s') . ' : ' . $contact->entity_id. ' deal id : '.$contactComment->first()->deal_id."\n");//dd('end');

            } catch (AmoCRMApiException $exception) {

                dd($exception->getDescription());
            }
        }
    }
}
