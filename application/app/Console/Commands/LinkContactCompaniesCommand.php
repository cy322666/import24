<?php

namespace App\Console\Commands;

use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use App\Models\Account;
use App\Models\Bitrix\Company;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Contact_CF;
use App\Services\amoCRM\Client;
use Illuminate\Console\Command;
use Laravel\Octane\Exceptions\DdException;

class LinkContactCompaniesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.contacts.link';

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
            ->where('value', '!=', null)
            ->where('cf_id', 32)
            ->get();

        foreach ($cfContacts as $cfContact) {

            $company = Company::query()
                ->where('company_id', $cfContact->value)
                ->first();

//            if($company->status !== 1) continue;

            $companyId = $company->entity_id;

            $contactId = Contact::query()
                ->where('contact_id', $cfContact->contact_id)
                ->first()
                    ->entity_id;

            $linksCollection = new LinksCollection();
            $linksCollection
                ->add((new CompanyModel())
                    ->setId($companyId));

            try {
                $linksCollection = $amoApi->contacts()
                    ->link((new ContactModel())
                        ->setId($contactId), $linksCollection);

                $company->contact_id = $contactId;
                $company->status = 2;
                $company->save();

                print_r('SUCCESS : companyId '.$companyId."\n");// dd('end');

            } catch (AmoCRMApiException $e) {
                dd($e->getMessage());
            }
        }
    }
}
