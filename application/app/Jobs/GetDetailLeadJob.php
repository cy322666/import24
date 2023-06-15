<?php

namespace App\Jobs;

use App\Models\Bitrix\CFLead;
use App\Models\Bitrix\Lead;
use App\Services\Bitrix;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GetDetailLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Lead $lead;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $lead = Bitrix::init()
                ->call('crm.lead.get', [
                    'ID' => $this->lead->lead_id,
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($lead as $field_name => $field_value) {

                $custom_field = CFLead::query()
                    ->where('code', $field_name)
                    ->firstOrFail();

                DB::table('lead_custom_field')
                    ->insert([
                        'lead_id' => $this->lead->lead_id,
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
