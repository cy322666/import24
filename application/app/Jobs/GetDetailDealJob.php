<?php

namespace App\Jobs;

use App\Models\Bitrix\CFDeal;
use App\Models\Bitrix\CFLead;
use App\Models\Bitrix\Deal;
use App\Services\Bitrix;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GetDetailDealJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Deal $deal;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $deal = Bitrix::init()
                ->call('crm.deal.get', [
                    'ID' => $this->deal->deal_id,
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($deal as $field_name => $field_value) {

                $custom_field = CFDeal::query()
                    ->where('code', $field_name)
                    ->firstOrFail();

                DB::table('deal_custom_field')
                    ->insert([
                        'deal_id' => $this->deal->deal_id,
                        'cf_id'   => $custom_field->id,
                        'value'   => is_array($field_value) ? json_encode($field_value, true) : $field_value,
                    ]);
            }
        } catch (InvalidArgumentException $e) {
        } catch (TransportException $e) {
        } catch (BaseException $e) {
        }
    }
}
