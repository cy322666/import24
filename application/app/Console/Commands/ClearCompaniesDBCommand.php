<?php

namespace App\Console\Commands;

use App\Models\Bitrix\CFCompany;
use App\Models\Bitrix\Company_CF;
use Illuminate\Console\Command;

class ClearCompaniesDBCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:companies.clear';

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
        $fields = CFCompany::all();

        foreach ($fields as $field) {

            $contact_cf = Company_CF::query()
                ->where('cf_id', $field->id)
                ->where('value', '!=', null)
                ->where('value', '!=', '')
                ->count();

            if($contact_cf < 5) {

                $field->count_5 = 1;
                $field->save();
//                Contact_CF::query()
//                    ->where('cf_id', $field->id)
//                    ->delete();
//
//                $field->delete();
            }
        }
    }
}
