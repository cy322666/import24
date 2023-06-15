<?php

namespace App\Jobs;

use App\Models\Bitrix\CFCompany;
use App\Models\Bitrix\CFContact;
use App\Models\Bitrix\Contact;
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

class GetDetailContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Contact $contact;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $contact = Bitrix::init()
                ->call('crm.contact.get', [
                    'ID' => $this->contact->contact_id,
                ])
                ->getResponseData()
                ->getResult()
                ->getResultData();

            foreach ($contact as $field_name => $field_value) {

                $custom_field = CFContact::query()
                    ->where('code', $field_name)
                    ->firstOrFail();

                DB::table('contact_custom_field')
                    ->insert([
                        'contact_id' => $this->contact->contact_id,
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
