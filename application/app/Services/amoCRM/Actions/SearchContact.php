<?php

namespace App\Services\amoCRM\Actions;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Models\ContactModel;

/**
 * Статический класс - реализация поиска контакта.
 * Реализуется в стратегиях бизон
 */
abstract class SearchContact
{
    public static function searchContact(AmoCRMApiClient $apiClient, string $search_query): ?ContactModel
    {
        try {
            $contact = $apiClient->contacts()
                ->get(
                    (new ContactsFilter())->setQuery($search_query)
                );

        } catch (AmoCRMApiNoContentException) {

            return null;
        }

        return $contact->first();
    }
}
