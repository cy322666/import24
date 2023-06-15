<?php

namespace App\Console\Commands;

use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\NoteType\CommonNote;
use App\Models\Account;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\ContactComment;
use App\Services\amoCRM\Client;
use App\Services\Bitrix;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SendContactComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.contacts.comments.send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'dd';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $contactComments = ContactComment::query()
            ->where('status', 0)
            ->get()
            ->groupBy('contact_id');

        $amoApi = (new Client())->getInstance(Account::all()->first());

        foreach ($contactComments as $contactComment) {

            $contact = Contact::query()
                ->where('contact_id', $contactComment->first()->contact_id)
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
                $contactNotesService = $amoApi->notes(EntityTypesInterface::CONTACTS);
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
            } catch (AmoCRMApiException $exception) {

                dd($exception->getDescription());
            }
        }
    }
}
