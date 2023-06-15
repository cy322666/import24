<?php

namespace App\Services\amoCRM\Actions;

/**
 * Статический класс - реализация поиска сделки
 * Реализуется в стратегиях бизон
 */
abstract class SearchLead
{
    public static function searchLead($contact)
    {
        $leads = $contact->getLeads();

        if($leads !== null && $leads->first()) {

            foreach ($leads as $lead) {

                if($lead->getStatusId() != 142 && $lead->getStatusId()) {

                    break;
                }
            }
        }

        return $lead ?? null;
    }
}
