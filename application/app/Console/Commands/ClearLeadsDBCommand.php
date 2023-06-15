<?php

namespace App\Console\Commands;

use App\Models\Bitrix\CFLead;
use App\Models\Bitrix\Lead_CF;
use Illuminate\Console\Command;

class ClearLeadsDBCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:leads.clear';

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
        $fields = CFLead::all();

        foreach ($fields as $field) {

            $deal_cf = Lead_CF::query()
                ->where('cf_id', $field->id)
                ->where('value', '!=', null)
                ->where('value', '!=', '')
                ->count();

            if($deal_cf < 5) {

                $field->count_5 = 1;
                $field->save();
//                Lead_CF::query()
//                    ->where('cf_id', $field->id)
//                    ->delete();
//
//                $field->delete();
            }
        }
    }
}
