<?php

namespace App\Console\Commands;

use AmoCRM\Collections\CompaniesCollection;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use App\Models\Account;
use App\Models\Bitrix\CFDeal;
use App\Models\Bitrix\Company;
use App\Models\Bitrix\Company_CF;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Deal;
use App\Models\Bitrix\Deal_CF;
use App\Services\amoCRM\Client;
use App\Services\Fields\FieldsFactory;
use App\Services\Fields\Setters\Url;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Laravel\Octane\Exceptions\DdException;

class SendDealsCommand extends Command
{
    private static array $arrayNeedFields = [
        'UF_CRM_1588767503'    => 'G:Потенциал заявки',
        'UF_CRM_1588767524'    => 'G:Категория',
        'UF_CRM_1588767541'    => 'G:Регион',
        'UF_CRM_1588841514'    => 'G:Ожидаемое кол-во у.е',
        'UF_CRM_1589116369715' => 'G:Вертикаль',
        'UF_CRM_1589116620760' => 'G:Клиентский сегмент',
        'UF_CRM_1589180430155' => 'G:Категория',
        'UF_CRM_1589183443194' => 'G:Возможные позиции',
        'UF_CRM_1589183650259' => 'G:Регион',
        'UF_CRM_1616155916559' => 'G:Пилот',
        'UF_CRM_1629791117263' => 'G:Тип возможности',
        'UF_CRM_1629792870866' => 'G:Ожидаемое количество локаций',
        'UF_CRM_1630063497997' => 'G:Внешняя ставка',
        'UF_CRM_1630063515692' => 'G:Внутренняя ставка',
        'UF_CRM_1630063579912' => 'G:Длинна смены',
        'UF_CRM_1639150785306' => 'G:Город',
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.deals.send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws AmoCRMoAuthApiException
     * @throws DdException|InvalidArgumentException
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     */
    public function handle()
    {
        //hunters - 19
        //accounting - 21
        $amoApi = (new Client())->getInstance(Account::all()->first());

        $entityModels = Deal::query()
            ->where('status', 0)
            ->get();

        foreach ($entityModels as $entityModel) {
//dd($entityModel);
            $pipelineId = Deal_CF::query()
                ->where('deal_id', $entityModel->deal_id)
                ->where('cf_id', 142)
                ->first()
                ->value;

            $contactId = Deal_CF::query()
                ->where('deal_id', $entityModel->deal_id)
                ->where('cf_id', 155)
                ->first()
                ?->value;

            if ($contactId) {

                $contactId = Contact::query()
                    ->where('contact_id', $contactId)
                    ->first()
                    ->entity_id;
            }

            $companyId = Deal_CF::query()
                ->where('deal_id', $entityModel->deal_id)
                ->where('cf_id', 154)
                ->first()
                ?->value;

            if ($companyId) {

                $companyId = Company::query()
                    ->where('company_id', $companyId)
                    ->first()
                    ->entity_id;
            }

            $lead = (new LeadModel())
                ->setName('битрикс : ' . Deal_CF::query()
                        ->where('deal_id', $entityModel->deal_id)
                        ->where('cf_id', 140)
                        ->first()
                        ->value)
                ->setStatusId(
                    self::getStatusId(Deal_CF::query()
                        ->where('deal_id', $entityModel->deal_id)
                        ->where('cf_id', 143)
                        ->first()
                        ->value
                    )
                )
                ->setPrice(explode('.', Deal_CF::query()
                    ->where('deal_id', $entityModel->deal_id)
                    ->where('cf_id', 151)
                    ->first()
                    ->value)[0]
                );

            if ($pipelineId == 21) {
                $lead->setPipelineId(5373343);
            }

            $customFields = new CustomFieldsValuesCollection;

            foreach (static::$arrayNeedFields as $fieldCode => $fieldName) {

                $customField = CFDeal::where('code', $fieldCode)->first();

                $value = Deal_CF::query()
                    ->where('deal_id', $entityModel->deal_id)
                    ->where('cf_id', $customField->id)
                    ->first()
                    ?->value;
//dd($entityModel);
                if ($value) {

//                    if($customField->type == 'enumeration') dd('asdsa');

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

            $linkIdBitrix = new Url();
            $linkIdBitrix->setId(1155033);
            $linkIdBitrix->setValues('https://gigant.bitrix24.ru/crm/deal/details/' . Deal_CF::query()
                    ->where('deal_id', $entityModel->deal_id)
                    ->where('cf_id', 139)
                    ->first()
                    ?->value . '/');

            $customFields->add($linkIdBitrix->getFieldCollection());

            $leadsService = $amoApi->leads();
//            dd($customFields->toArray(), $entityModel->deal_id);
            $lead->setCustomFieldsValues($customFields);

            try {
                $leadsCollection = $leadsService->addOne($lead);
                $leadId = $leadsCollection->getId();

                if($contactId) {
                    //Получим контакт по ID, сделку и привяжем контакт к сделке
                    try {
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

                $entityModel->entity_id = $leadId;
                $entityModel->status = 1;
                $entityModel->save();

                print_r('SUCCESS ' . Carbon::now()->format('H:i:s') . ' : ' . $lead->getId() . ' id : ' . $entityModel->deal_id . "\n");
//                dd(' end');

                if (!empty($lead)) unset($lead);
                if (!empty($company)) unset($company);
                if (!empty($contact)) unset($contact);
        }
    }

    private static function getStatusId(string $stage_name): int
    {
        return match ($stage_name) {
            'C19:LOSE', 'C19:2' => 47399623,
            'C19:NEW', 'C19:PREPARATION', 'C19:PREPAYMENT_INVOICE' => 47399605,
            'C19:FINAL_INVOICE', 'C19:1' => 47399620,
            'C19:WON' => 47399632,
            'C21:LOSE' => 143,
            'C21:WON' => 142,
            default => 47764411,
        };
    }
}
