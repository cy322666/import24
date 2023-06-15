<?php

namespace App\Console\Commands;

use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Models\LeadModel;
use App\Models\Account;
use App\Models\Bitrix\CFDeal;
use App\Models\Bitrix\CFLead;
use App\Models\Bitrix\Company;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Contact_CF;
use App\Models\Bitrix\Deal;
use App\Models\Bitrix\Deal_CF;
use App\Models\Bitrix\Lead;
use App\Models\Bitrix\Lead_CF;
use App\Services\amoCRM\Actions\SearchContact;
use App\Services\amoCRM\Client;
use App\Services\Fields\FieldsFactory;
use App\Services\Fields\Setters\Text;
use App\Services\Fields\Setters\Url;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendLeadsCommand extends Command
{
    private static array $arrayNeedFields = [
        'LAST_NAME'             => 'G:ФИО',
        'COMPANY_TITLE'         => 'G:Название компании',
        'POST'                  => 'G:Должность',
//        'PHONE'                 => 'G:Телефон',
//        'EMAIL'                 => 'G:E-mail',
        'UF_CRM_1639146505011'  => 'G:Поток',
        'UF_CRM_1639146929022'  => 'G:Поток',
        'UF_CRM_1639147214162'  => 'G:Город',
        'UTM_CAMPAIGN'          => 'Lead - G:utm_content',
//        'ID'                    => 'G:Ссылка на битрикс',
//        'STATUS_ID'             => 'G:Этап воронки',
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.leads.send';

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

        $entityModels = Lead::query()
            ->where('status', 0)
            ->get();

        foreach ($entityModels as $entityModel) {
//dd($entityModel);
            $pipelineId = 5323129;

            $contactId = Lead_CF::query()
                ->where('lead_id', $entityModel->deal_id)
                ->where('cf_id', 40)
                ->first()
                ?->value;

            if ($contactId) {

                $contactId = Contact::query()
                    ->where('contact_id', $contactId)
                    ->first()
                    ->entity_id;
            }

            $companyId = Lead_CF::query()
                ->where('lead_id', $entityModel->deal_id)
                ->where('cf_id', 39)
                ->first()
                ?->value;

            if ($companyId) {

                $companyId = Company::query()
                    ->where('company_id', $companyId)
                    ->first()
                    ->entity_id;
            }

            $lead = (new LeadModel())
                ->setName('битрикс : ' . Lead_CF::query()
                        ->where('lead_id', $entityModel->lead_id)
                        ->where('cf_id', 2)
                        ->first()
                        ->value)
                ->setStatusId(
                    self::getStatusId(Lead_CF::query()
                        ->where('lead_id', $entityModel->lead_id)
                        ->where('cf_id', 11)
                        ->first()
                        ->value
                    )
                )
                ->setPipelineId(5323129);

            $customFields = new CustomFieldsValuesCollection;

            foreach (static::$arrayNeedFields as $fieldCode => $fieldName) {

                $customField = CFLead::where('code', $fieldCode)->first();

                $value = Lead_CF::query()
                    ->where('lead_id', $entityModel->lead_id)
                    ->where('cf_id', $customField->id)
                    ->first()
                    ?->value;

                if ($value) {

                    $setterService = FieldsFactory::getSetters($customField->type);

                    $setterService->setId($customField->field_id);
                    $setterService->setValues($value);

                    $fieldCollection = $setterService->getFieldCollection();

                    if ($fieldCollection == null) {
//                        dd($customField, $entityModel->deal_id);
                    } else
                        $customFields->add($fieldCollection);
                }
            }

            $rawEmail = Lead_CF::query()
                ->where('lead_id', $entityModel->lead_id)
                ->where('cf_id', 52)
                ->first()
                ?->value;

            if ($rawEmail)
                $email = json_decode($rawEmail, true)[0]['VALUE'];

            $rawPhone = Lead_CF::query()
                ->where('lead_id', $entityModel->lead_id)
                ->where('cf_id', 51)
                ->first()
                ?->value;

            if ($rawPhone)
                $phone = json_decode($rawPhone, true)[0]['VALUE'];

            $linkIdBitrix = new Url();
            $linkIdBitrix->setId(1155033);
            $linkIdBitrix->setValues('https://gigant.bitrix24.ru/crm/lead/details/' . Lead_CF::query()
                    ->where('lead_id', $entityModel->lead_id)
                    ->where('cf_id', 1)
                    ->first()
                    ?->value . '/');

            $statusBitrix = new Text();
            $statusBitrix->setId(1155031);
            $statusBitrix->setValues(self::getNameStatus(Lead_CF::query()
                ->where('lead_id', $entityModel->lead_id)
                ->where('cf_id', 11)
                ->first()
                ?->value));

            $emailBitrix = new Text();
            $emailBitrix->setId(1155041);
            $emailBitrix->setValues($email ?? null);

            $phoneBitrix = new Text();
            $phoneBitrix->setId(1155039);
            $phoneBitrix->setValues($phone ?? null);

            $customFields->add($linkIdBitrix->getFieldCollection());
            $customFields->add($phoneBitrix->getFieldCollection());
            $customFields->add($emailBitrix->getFieldCollection());
            $customFields->add($statusBitrix->getFieldCollection());

            $leadsService = $amoApi->leads();
//            dd($customFields->toArray(), $entityModel->deal_id);
            $lead->setCustomFieldsValues($customFields);

            try {
                if($companyId == null && $contactId == null) {

                    if (!empty($email)) {
                        $contact = SearchContact::searchContact($amoApi, $email);
                    }

                    if (empty($contact) && !empty($phone)) {

                        $contact = SearchContact::searchContact($amoApi, $phone);
                    }
                }

                $leadsCollection = $leadsService->addOne($lead);
                $leadId = $leadsCollection->getId();

                if($contactId || !empty($contact)) {
                    //Получим контакт по ID, сделку и привяжем контакт к сделке
                    try {
                        if (empty($contact))
                            $contact = $amoApi->contacts()->getOne($contactId);

                        $links = new LinksCollection();
                        $links->add($contact);
                        try {
                            $amoApi->leads()->link($lead, $links);

                            print_r('CONTACT : ' . $contactId . "\n");

                        } catch (\Throwable $exception) {
                            dd(' 333 '.$exception->getMessage());
                        }
                    } catch (\Throwable $exception) {
                        dd(' 333 '.$exception->getMessage());
                    }
                }

                if($companyId) {
                    try {
                        $company = $amoApi->companies()->getOne($companyId);

                        $links = new LinksCollection();
                        $links->add($company);

                        try {
                            $amoApi->leads()->link($lead, $links);

                            print_r('COMPANY : ' . $companyId . "\n");

                        } catch (\Throwable $exception) {
                            dd(' 333 '.$exception->getMessage());
                        }
                    } catch (\Throwable $exception) {
                        dd(' 444 '.$exception->getMessage());
                    }
                }

            } catch (\Throwable $exception) {

                dd(' 555 '.$exception->getMessage(). ' ' .$exception->getLine().' '.$exception->getTraceAsString());
            }

            if (empty($contact)) {

                $entityModel->status = 3;
            } else {
                $entityModel->status = 1;
            }

            $entityModel->entity_id = $leadId;
            $entityModel->save();

            print_r('SUCCESS ' . Carbon::now()->format('H:i:s') . ' : ' . $lead->getId() . ' id : ' . $entityModel->lead_id . "\n");

//                dd(' end');

            if (!empty($lead)) unset($lead);
            if (!empty($companyId)) unset($companyId);
            if (!empty($contactId)) unset($contactId);
            if (!empty($email)) unset($email);
            if (!empty($rawEmail)) unset($rawEmail);
            if (!empty($rawPhone)) unset($rawPhone);
            if (!empty($phone)) unset($phone);
            if (!empty($company)) unset($company);
            if (!empty($contact)) unset($contact);
        }
    }

    private static function getStatusId(string $stage_name): int
    {
        return match ($stage_name) {
            'JUNK'      => 143,
            'CONVERTED' => 142,
            default => 47764762,
        };
    }

    private static function getNameStatus(string $stage_name): string
    {
        return match ($stage_name) {
            'JUNK'      => 'Некачественный лид',
            'CONVERTED' => 'Качественный лид',
            'UC_5Q1LGD' => 'Отложенный спрос',
            'PROCESSED' => 'Initiator found',
            'IN_PROCESS' => 'Prospect',
            'UC_TO8SUC' => 'Qualification/reserch',
            'NEW' => 'Suspect',
        };
    }
}
