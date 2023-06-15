<?php

namespace App\Services\amoCRM\Actions;

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use App\Models\Api\Integrations\Bizon\BizonSetting;
use App\Models\Api\Integrations\Bizon\Viewer;
use Illuminate\Support\Facades\Log;

/**
 * Статический класс - реализация создания сделки
 * Реализуется в стратегиях бизон
 */
abstract class CreateLead
{
    public static function createLead(
        ContactModel $contactModel,
        Viewer $viewer,
        BizonSetting $setting,
        $amoApi
    ) : ?LeadModel
    {
        $leadsService = $amoApi->leads();

        $lead = (new LeadModel())
            ->setName('Новый посетитель вебинара')
            ->setStatusId($viewer->getStatusId($setting))
            ->setResponsibleUserId($setting->responsible_user_id)
            ->setContacts(
                (new ContactsCollection())
                    ->add(
                        (new ContactModel())
                            ->setId($contactModel->getId())
                            ->setIsMain(true)
                    )
            );

        try {
            return $leadsService->addOne($lead);

        } catch (AmoCRMApiException $exception) {

            Log::error(__METHOD__.' > '.$exception->getMessage().' : '.$exception->getLine());
        }
    }
}
