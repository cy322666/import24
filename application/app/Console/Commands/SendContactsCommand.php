<?php

namespace App\Console\Commands;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Exceptions\AmoCRMApiErrorResponseException;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use App\Models\Account;
use App\Models\Bitrix\CFContact;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Contact_CF;
use App\Services\amoCRM\Actions\SearchContact;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendContactsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.contacts.send';

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
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     */
    public function handle()
    {
        $arrayNeedFields = [
            'COMMENTS'     => 'G:комментарий',
            'UTM_SOURCE'   => 'G:utm_source',
            'UTM_MEDIUM'   => 'G:utm_medium',
            'UTM_CAMPAIGN' => 'G:utm_campaign',
            'UTM_CONTENT'  => 'G:utm_content',
            'UTM_TERM'     => 'G:utm_term',
            'UF_CRM_1589185637'    => 'G:За что отвечает',
        ];

        $amoApi = (new Client())->getInstance(Account::all()->first());

        $entityModels = Contact::query()
            ->where('status', 0)
            ->get();

        foreach ($entityModels as $entityModel) {

            $contact = null;
            $email = null;
            $phone = null;

            $rawEmail = Contact_CF::query()
                ->where('contact_id', $entityModel->contact_id)
                ->where('cf_id', 45)
                ->first()
                ?->value;

            if ($rawEmail)
                $email = json_decode($rawEmail, true)[0]['VALUE'];

            $rawPhone = Contact_CF::query()
                ->where('contact_id', $entityModel->contact_id)
                ->where('cf_id', 44)
                ->first()
                ?->value;

            if ($rawPhone)
                $phone = json_decode($rawPhone, true)[0]['VALUE'];

            $name1 = Contact_CF::query()
                ->where('contact_id', $entityModel->contact_id)
                ->where('cf_id', 5)
                ->first()
                ?->value;

            $name2 = Contact_CF::query()
                ->where('contact_id', $entityModel->contact_id)
                ->where('cf_id', 3)
                ->first()
                ?->value;

            $name3 = Contact_CF::query()
                ->where('contact_id', $entityModel->contact_id)
                ->where('cf_id', 4)
                ->first()
                ?->value;

            $name = $name1.' '.$name2.' '.$name3;

            try {

                if ($email !== null) {
                    $contact = SearchContact::searchContact($amoApi, $email);
                }

                if ($contact == null && $phone !== null) {
                    $contact = SearchContact::searchContact($amoApi, $phone);
                }
                if ($contact == null) {
                    $contact = (new ContactModel())
                        ->setName($name)
                        ->setCustomFieldsValues(
                            new CustomFieldsValuesCollection()
                        );

                    $contact = $amoApi
                        ->contacts()
                        ->addOne($contact);
                }

                $customFields = $contact->getCustomFieldsValues();

                if ($phone && empty($customFields->getBy('fieldCode', 'PHONE'))) {
                    self::setPhone($customFields, $phone);
                }

                if ($email && empty($customFields->getBy('fieldCode', 'EMAIL'))) {
                    self::setEmail($customFields, $email);
                }
//                dd($contact->toApi());
                $contact->setCreatedBy(0);
                $contact->setUpdatedBy(0);

                try {
                    $contact = $amoApi
                        ->contacts()
                        ->updateOne($contact);

                } catch (AmoCRMApiErrorResponseException $e) {

                    dd('!ERROR ', $e->getValidationErrors());
                }

                foreach ($arrayNeedFields as $fieldCode => $fieldName) {

                    $customField = CFContact::where('code', $fieldCode)->first();

                    $value = Contact_CF::query()
                        ->where('contact_id', $entityModel->contact_id)
                        ->where('cf_id', $customField->id)
                        ->first()
                        ?->value;

                    if ($value) {

                        $cfModel = (new TextCustomFieldValuesModel)
                            ->setFieldId($customField->field_id);

                        $customFields->add($cfModel);

                        $cfModel->setValues(
                            (new TextCustomFieldValueCollection())
                                ->add(
                                    (new TextCustomFieldValueModel())
                                        ->setValue($value)
                                )
                        );
                    }
                }

                try {
                    $amoApi->contacts()->updateOne($contact);

                }  catch (AmoCRMApiErrorResponseException $exception) {

                    dd('!ERROR '. $exception->getValidationErrors(), $customFields->toArray());
                }


                $entityModel->entity_id = $contact->getId();
                $entityModel->status = 1;
                $entityModel->save();

                print_r('SUCCESS '.Carbon::now()->format('H:i:s').' : '.$contact->getId()."\n");

                if(!empty($contact)) unset($contact);
                if(!empty($emailRaw)) unset($emailRaw);
                if(!empty($phoneRaw)) unset($phoneRaw);
                if(!empty($email)) unset($email);
                if(!empty($phone)) unset($phone);
//                dd('SUCCESS', $customFields->toArray(), $entityModel->contact_id);

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
