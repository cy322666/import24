<?php

namespace App\Services\Fields;

use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\Fields\Setters\Date;
use App\Services\Fields\Setters\Flag;
use App\Services\Fields\Setters\Integer;
use App\Services\Fields\Setters\Select;
use App\Services\Fields\Setters\Text;
use App\Services\Fields\Setters\Url;
use Laravel\Octane\Exceptions\DdException;

class FieldsFactory
{
    /**
     * @throws AmoCRMoAuthApiException
     * @throws DdException
     */
    public static function getFieldService(string $fieldType)
    {
        $amoApi = (new Client())->getInstance(Account::all()->first());

        return match ($fieldType) {

            'string', 'double', 'url' => new StringCreator($amoApi),
            'integer' => new IntegerCreator($amoApi),
            'enumeration' => new SelectCreator($amoApi),
            'boolean' => new BooleanCreator($amoApi),
            'date' => new DateCreator($amoApi),

            default  => dd('not found field type', $fieldType),
        };
    }

    /**
     * @throws DdException
     */
    public static function getSetters(string $fieldType)
    {
        return match ($fieldType) {

            'string', 'double', 'url' => new Text(),
            'integer'     => new Integer(),
            'enumeration' => new Select(),
            'boolean'     => new Flag(),
            'date'        => new Date(),
            'crm_multifield' => new Url(),

            default  => dd('not found field type', $fieldType),
        };
    }
}
