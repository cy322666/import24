<?php

namespace App\Services\amoCRM\Actions;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Filters\CompaniesFilter;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;

/**
 * Статический класс - реализация поиска контакта.
 * Реализуется в стратегиях бизон
 */
abstract class SearchCompany
{
    public static function search(AmoCRMApiClient $apiClient, string $search_query): ?CompanyModel
    {
        try {
            $company = $apiClient->companies()
                ->get(
                    (new CompaniesFilter())->setQuery($search_query)
                );

            return $company?->first();

        } catch (AmoCRMApiNoContentException) {

            return null;
        } catch (AmoCRMoAuthApiException $e) {
        } catch (AmoCRMApiException $e) {

            dd($e->getDescription());
        }
    }
}
