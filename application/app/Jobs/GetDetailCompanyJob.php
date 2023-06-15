<?php

namespace App\Jobs;

use App\Models\Bitrix\CFCompany;
use App\Models\Bitrix\CFLead;
use App\Models\Bitrix\Company;
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

class GetDetailCompanyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Company $company;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $company = Bitrix::init()
                ->call('crm.company.get', [
                    'ID' => $this->company->company_id,
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($company as $field_name => $field_value) {

                $custom_field = CFCompany::query()
                    ->where('code', $field_name)
                    ->firstOrFail();

                DB::table('company_custom_field')
                    ->insert([
                        'company_id' => $this->company->company_id,
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
