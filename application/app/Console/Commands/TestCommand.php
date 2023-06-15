<?php

namespace App\Console\Commands;

use AmoCRM\Collections\LinksCollection;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiErrorResponseException;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\NoteType\CommonNote;
use App\Models\Account;
use App\Models\Bitrix\Company;
use App\Models\Bitrix\CompanyComment;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Contact_CF;
use App\Services\amoCRM\Client;
use App\Services\Bitrix;
use Illuminate\Console\Command;
use Ramsey\Collection\Collection;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:test';

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
        $amoApi = (new Client())->getInstance(Account::all()->first());

        $cfContacts = Contact_CF::query()
            ->where('value', "1")
            ->where('cf_id', 32)
            ->where('updated_at', null)
            ->get();

        $company = Company::query()
            ->where('entity_id', 47120451)
            ->first();

        $i = 1;

        foreach ($cfContacts as $cfContact) {

            $contact = Contact::query()
                ->where('contact_id', $cfContact->contact_id)
                ->first();

            $contactId = $contact->entity_id;

            $name1 = Contact_CF::query()
                ->where('contact_id', $cfContact->contact_id)
                ->where('cf_id', 5)
                ->first()
                ?->value;

            $name2 = Contact_CF::query()
                ->where('contact_id', $cfContact->contact_id)
                ->where('cf_id', 3)
                ->first()
                ?->value;

            $name3 = Contact_CF::query()
                ->where('contact_id', $cfContact->contact_id)
                ->where('cf_id', 4)
                ->first()
                ?->value;

            $name = $name1.' '.$name2.' '.$name3;

            $text[] = implode("\n", [
                $i.'. '.$name,
                'https://orps.amocrm.ru/contacts/detail/'.$contactId,
                "\n",
            ]);

            if ($i == 54) {

                try {
                    $notesCollection = new NotesCollection();

                    $serviceNote = new CommonNote();
                    $serviceNote
                        ->setEntityId(1)
                        ->setEntityId($company->entity_id)
                        ->setText(implode("\n", $text))
                        ->setCreatedBy(0);

                    $notesCollection->add($serviceNote);

                    $companyNotesService = $amoApi->notes(EntityTypesInterface::COMPANIES);
                    $companyNotesService->add($notesCollection);

                    dd('end');

                } catch (AmoCRMApiException $exception) {

                    dd($exception->getDescription());
                }
            } else {
                $i = $i + 1;

                $cfContact->params = json_encode([]);
                $cfContact->save();
            }
        }
    }
}
