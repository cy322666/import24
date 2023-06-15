<?php

namespace App\Console\Commands;

use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use App\Models\Account;
use App\Models\Bitrix\Company;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Contact_CF;
use App\Models\Bitrix\Lead;
use App\Models\Bitrix\Lead_CF;
use App\Services\amoCRM\Client;
use Illuminate\Console\Command;

class LinkLeadContactCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.leads.link';

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

        //TODO contact 40
        $cfContacts = Lead_CF::query()
            ->where('value', '!=', null)
            ->where('cf_id', 40)
            ->get();

        foreach ($cfContacts as $cfContact) {

            if($cfContact->value == 0 || null) continue;

            $contact = Contact::query()
                ->where('contact_id', $cfContact->value)
                ->first();

//            if($company->status !== 1) continue;

            $contactId = $contact->entity_id;

            $lead = Lead::query()
                ->where('lead_id', $cfContact->lead_id)
                ->first();

            if($lead->status == 5) continue;
//            dd($lead);
            $leadId = Lead::query()
                ->where('lead_id', $cfContact->lead_id)
                ->first()
                ->entity_id;

            $linksCollection = new LinksCollection();
            $linksCollection
                ->add((new ContactModel())
                    ->setId($contactId));

            try {
                $amoApi->leads()
                    ->link((new LeadModel())
                        ->setId($leadId), $linksCollection);

                $lead->status = 5;
                $lead->save();

                print_r('SUCCESS : leadId '.$leadId.' companyId '.$contactId. "\n");// dd('end');

                unset($contact);
                unset($contactId);
                unset($lead);
                unset($leadId);

            } catch (AmoCRMApiException $e) {
                dd($e->getLastRequestInfo());
            }
        }
    }
}
