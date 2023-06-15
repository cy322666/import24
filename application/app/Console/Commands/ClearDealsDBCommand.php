<?php

namespace App\Console\Commands;

use App\Models\Bitrix\CFDeal;
use App\Models\Bitrix\Deal;
use App\Models\Bitrix\Deal_CF;
use App\Services\Bitrix;
use Illuminate\Console\Command;

class ClearDealsDBCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:deals.clear';

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
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function handle()
    {
        $fields = CFDeal::all();

        foreach ($fields as $field) {

            $deal_cf = Deal_CF::query()
                ->where('cf_id', $field->id)
                ->where('value', '!=', null)
                ->where('value', '!=', '')
                ->count();

            if($deal_cf < 5) {

                $field->count_5 = 1;
                $field->save();
                Deal_CF::query()
                    ->where('cf_id', $field->id)
                    ->delete();

                $field->delete();
            }
        }
    }
}
