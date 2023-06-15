<?php

namespace App\Services\amoCRM\Validations;

use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;

/**
 * Сервис валидации контакта и сделки на предмет теста (!нарушение SRP)
 */
final class CheckHookTest
{
    /**
     * @var LeadModel
     */
    private LeadModel $lead;

    /**
     * @var ContactModel
     */
    private ContactModel $contact;

    /**
     * @var CustomFieldsValuesCollection|null
     */
    private ?CustomFieldsValuesCollection $fieldsContact;

    /**
     * @var CustomFieldsValuesCollection|null
     */
    private ?CustomFieldsValuesCollection $fieldsLead;

    /**
     * @var array|string[] Массив с тестовыми почтами
     */
    private array $array_test_emails = [
        'test@ya.ru',
    ];

    /**
     * @var array|string[] Массив с тестовыми телефонами
     */
    private array $array_test_phones = [
        '79996373955',
    ];

    /**
     * @param ContactModel|null $model
     * @return $this
     */
    public function setContact(?ContactModel $model): CheckHookTest
    {
        $this->contact = $model;
        $this->fieldsContact = $model->getCustomFieldsValues();

        return $this;
    }

    /**
     * @param LeadModel|null $model
     * @return $this
     */
    public function setLead(?LeadModel $model): CheckHookTest
    {
        $this->lead = $model;
        $this->fieldsLead = $model->getCustomFieldsValues();

        return $this;
    }

    /**
     * Метод в котором будут проводиться все проверки
     * @return bool
     * - true если проверки не пройдены
     * - false если проверки пройдены
     */
    public function validate() : bool
    {
        return $this->validateContactEmails();
    }

    /**
     * Проверка на присутствие у контакта тестового email
     * @return bool
     */
    private function validateContactEmails() : bool
    {
        $emails = $this->fieldsContact->getBy('fieldCode', 'EMAIL')
            ?->getValues()
            ?->all();

        foreach ($this->array_test_emails as $array_test_email) {

            foreach ($emails as $email) {

                if($array_test_email == $email->value) {

                    return true;
                }
            }
        }
        return false;
    }
}
