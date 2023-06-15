<?php

namespace App\Console\Commands;

use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiErrorResponseException;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFields\UrlCustomFieldModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use App\Models\Account;
use App\Models\Bitrix\CFCompany;
use App\Models\Bitrix\Company;
use App\Models\Bitrix\Company_CF;
use App\Services\amoCRM\Actions\SearchCompany;
use App\Services\amoCRM\Actions\SearchContact;
use App\Services\amoCRM\Client;
use App\Services\Fields\FieldsFactory;
use App\Services\Fields\Setters\Url;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendCompaniesCommand extends Command
{
    private static array $arrayNeedFields = [
            'UF_CRM_1630581647873' => '№ Договора',
            'WEB' => 'Web',
        'UF_CRM_1630430863899' => 'G:ЭДО',
        'INDUSTRY' => 'G:Сфера деятельности',
            'UF_CRM_1630430735841' => 'Адрес', //TODO textarea
            'UF_CRM_1630430689495' => 'Расчетный счет',
            'UF_CRM_1630430664310' => 'БИК банка',
            'UF_CRM_1630430649726' => 'КПП',
        'UF_CRM_1630430616508' => 'G:Срок действия',
            'UF_CRM_1630430572799' => 'Дата подписания Договора',
            'UF_CRM_1630430556180' => 'ИНН',
        'UF_CRM_1630429676791' => 'G:Количество магазинов (мини)',
        'UF_CRM_1630429662265' => 'G:Количество магазинов (супер)',
        'UF_CRM_1630429645413' => 'G:Количество магазинов (мега)',
        'UF_CRM_1630429616989' => 'G:Количество магазинов (всего)',
        'UF_CRM_1630429593605' => 'G:Количество городов',
        'UF_CRM_1630429580849' => 'G:Количество сотрудников (штат)',
        'UF_CRM_1630427491779' => 'G:Рассылка клиенту',
        'UF_CRM_1630427342496' => 'G:Penetration level (%)',
        'UF_CRM_1630426664798' => 'G:Неактивные локации',
        'UF_CRM_1630426644076' => 'G:Активные локации',
        'UF_CRM_1630426611965' => 'G:Кол-во локаций',
        'UF_CRM_1630426569510' => 'G:Сегмент',
        'UF_CRM_1630409299160' => 'G:Ссылка на папку с документами по клиенту',
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.companies.send';

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

        $entityModels = Company::query()
            ->where('status', 0)
            ->get();

        foreach ($entityModels as $entityModel) {

            $company = null;
            $email = null;
            $phone = null;

            $rawEmail = Company_CF::query()
                ->where('company_id', $entityModel->company_id)
                ->where('cf_id', 51)
                ->first()
                ?->value;

            if ($rawEmail)
                $email = json_decode($rawEmail, true)[0]['VALUE'];

            $rawPhone = Company_CF::query()
                ->where('company_id', $entityModel->company_id)
                ->where('cf_id', 50)
                ->first()
                ?->value;

            if ($rawPhone)
                $phone = json_decode($rawPhone, true)[0]['VALUE'];

            $name = Company_CF::query()
                ->where('company_id', $entityModel->company_id)
                ->where('cf_id', 2)
                ->first()
                ?->value;

            try {

                if ($email !== null) {
                    $company = SearchCompany::search($amoApi, $email);
                }
                if ($company == null && $phone !== null) {
                    $company = SearchCompany::search($amoApi, $phone);
                }
//                if ($company == null) {
//                    $company = SearchCompany::search($amoApi, $name);
//                }
                if ($company == null) {
                    $company = (new CompanyModel())
                        ->setName('битркис: '.$name)
                        ->setCustomFieldsValues(
                            new CustomFieldsValuesCollection()
                        );

                    $company = $amoApi
                        ->companies()
                        ->addOne($company);
                }

                $customFields = $company->getCustomFieldsValues();

                if ($customFields == null) {
                    $company->setCustomFieldsValues(
                        new CustomFieldsValuesCollection()
                    );
                }

                if ($phone) {
                    self::setPhone($customFields, $phone);
                }
                if ($email) {
                    self::setEmail($customFields, $email);
                }

                $company->setCreatedBy(0);
                $company->setUpdatedBy(0);

                try {
                    $company = $amoApi
                        ->companies()
                        ->updateOne($company);

                } catch (AmoCRMApiErrorResponseException $e) {

                    dd('!ERROR ', $e->getValidationErrors());
                }

                foreach (static::$arrayNeedFields as $fieldCode => $fieldName) {

                    $customField = CFCompany::where('code', $fieldCode)->first();

                    $value = Company_CF::query()
                        ->where('company_id', $entityModel->company_id)
                        ->where('cf_id', $customField->id)
                        ->first()
                        ?->value;

                    if ($value) {

                        if ($customField->id == 52) {

                            $valueRaw = json_decode($value, true);

                            $value = $valueRaw[0]['VALUE'];
                        }

                        $setterService = FieldsFactory::getSetters($customField->type);

                        $setterService->setId($customField->field_id);
                        $setterService->setValues($value);

                        $fieldCollection = $setterService->getFieldCollection();

                        if ($fieldCollection !== null) {

                            $customFields->add($fieldCollection);
                        }
                    }
                }

                $linkIdBitrix = new Url();
                $linkIdBitrix->setId(1155103);
                $linkIdBitrix->setValues('https://gigant.bitrix24.ru/crm/company/details/'.Company_CF::query()
                        ->where('company_id', $entityModel->company_id)
                        ->where('cf_id', 1)
                        ->first()
                        ?->value.'/');

                $customFields->add($linkIdBitrix->getFieldCollection());

                try {
                    $amoApi->companies()->updateOne($company);

                }  catch (AmoCRMApiErrorResponseException $exception) {

                    dd('!ERROR '. $exception->getValidationErrors(), $customFields->toArray());
                }

                $entityModel->entity_id = $company->getId();
//                dd($entityModel->company_id);
//                $contactId = Company_CF::query()
//                    ->where('company_id', $entityModel->company_id)
//                    ->where('cf_id', 40)
//                    ->first()
//                    ?->value;
//
//                $linksCollection = new LinksCollection();
//                $linksCollection
//                    ->add((new ContactModel())
//                        ->setId($contactId)
//                    );
//
//                try {
//                    $amoApi->companies()->link((new CompanyModel())->setId($contactId), $linksCollection);
//
//                } catch (AmoCRMApiException $e) {
//
//                    dd($e->getDescription());
//                }

                $entityModel->status = 1;
                $entityModel->save();

                print_r('SUCCESS '.Carbon::now()->format('H:i:s').' : '.$company->getId()."\n");

                if(!empty($company)) unset($company);
//                if(!empty($contact)) unset($contact);
                if(!empty($emailRaw)) unset($emailRaw);
                if(!empty($phoneRaw)) unset($phoneRaw);
                if(!empty($email)) unset($email);
                if(!empty($phone)) unset($phone);

            } catch (\Throwable $exception) {

                dd('!ERROR '. $exception->getMessage().' '.$exception->getFile().' '.$exception->getLine());
            }
        }
    }

    /**
     * @param $customFields
     * @param $phone
     * @return void
     */
    private static function setPhone(&$customFields, $phone)
    {
        $phoneField = (new MultitextCustomFieldValuesModel())
            ->setFieldCode('PHONE');

        $customFields->add($phoneField);

        $phoneField->setValues(
            (new MultitextCustomFieldValueCollection())
                ->add(
                    (new MultitextCustomFieldValueModel())
                        ->setEnum('WORKDD')
                        ->setValue($phone)
                )
        );
    }

    /**
     * @param $customFields
     * @param $email
     * @return void
     */
    private static function setEmail(&$customFields, $email)
    {
        $emailField = (new MultitextCustomFieldValuesModel())
            ->setFieldCode('EMAIL');

        $customFields->add($emailField);

        $emailField->setValues(
            (new MultitextCustomFieldValueCollection())
                ->add(
                    (new MultitextCustomFieldValueModel())
                        ->setEnum('WORK')
                        ->setValue($email)
                )
        );
    }
}
