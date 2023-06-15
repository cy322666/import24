<?php

namespace App\Console\Commands;

use App\Jobs\GetDetailContactJob;
use App\Jobs\GetDetailLeadJob;
use App\Models\Bitrix\Contact;
use App\Models\Bitrix\Lead;
use App\Services\Bitrix;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Illuminate\Console\Command;

class GetContactsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.contacts.list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @throws BaseException
     */
    public function handle()
    {
        $pagination = Bitrix::init()
            ->call('crm.contact.list')
            ->getResponseData()
            ->getPagination();

        for ($offset = 0; $offset < $pagination->getTotal(); $offset += 50) {

            try {
                $contacts = Bitrix::init()
                    ->call('crm.contact.list', [
                        'select' => ['ID'],
                        'start' => $offset,
                    ])
                    ->getResponseData()
                    ->getResult()
                    ->getResultData();

                foreach ($contacts as $contact) {

                    $contact_model = Contact::query()->create([
                        'contact_id' => $contact['ID'],
                    ]);

                    GetDetailContactJob::dispatch($contact_model);
                }

            } catch (InvalidArgumentException $e) {
            } catch (TransportException $e) {
            } catch (BaseException $e) {
            }
        }
    }
}
