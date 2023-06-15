<?php

namespace App\Console\Commands\Dev;

use AmoCRM\Collections\EventsCollections;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Filters\EventsFilter;
use AmoCRM\Models\EventModel;
use AmoCRM\Models\LeadModel;
use App\Models\Account;
use App\Models\Dev\Leads;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:events';

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
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     */
    public function handle()
    {
        $client_id = '0680f730-a5f7-4f87-9a85-0de007238081';
        $client_secret = 'JmG03ygoWkTh0fH2y0pG0iLsczH75cbXNiupzRbIKTsz7Xzepzp1S4QbNfCXJEmS';
        $access_code = 'def502003c3a4f8aa17fe296b30cb86b6e22d4a525ee3d228554c0d9142a464188b41e2099754aec14ae6680175e44a1772917a82947de4c8967e0d147c0e8fdbe84432e16ad1b3da836fbc29717ce889d0ac1d6cb6e336b3d0c3901661e37a8b1e74d459144552292b232b5c01fe490417a116f9c43fdfed0d5680290597992aefce6a300d9b5877767445cd1881ec730110257838c1939db5766f92c524a381b6630a9c14f6027d3d6cc28a9dc624da6659e238bd4d125f42c2c63fb50019f33bc2870a73b5bb6a0af1e83614336348c3b808222af83b6388b430c4f5a0beaad59f6a72e8d85730e32a4bc8f483da3fb6a52eae8a48729c7068522ea584b891b826ab80fe18cf11632ff237e483fefea61302ca488cc301d69d54b459f10aa237358d4c5f97362180ad458ee0f7c5dcfbaca7074a3160ae07709b875966c9f9689163049590eb567227d670392afb3e3e4ab87e1556f9b395ef95cdf50bc95ea757a3434d905307bee5b6912e777602dd4d113a007ca87699ecef04b63d3b0938996f9a5d5250c15c57659fde929d4a55624adc4dd7ec826be8e47e0b4e0141f437f47b31087c91e3a51e55f53ba26c7da33292e89d093a6c542e40ef97ce876';
        $amoCRM = \Ufee\Amo\Oauthapi::setInstance([
            'domain'        => 'misterstolicayandexru',
            'client_id'     => $client_id, // id приложения
            'client_secret' => $client_secret,
            'redirect_uri'  => 'https://misterstolicayandexru.amocrm.ru/settings/widgets/',
        ]);

        $amoCRM = \Ufee\Amo\Oauthapi::getInstance($client_id);

        try {

//            for (; ;) {

//                $date = Carbon::create('2022-07-30')->addDay();

//                if ($date->format('Y-m-d') == date('Y-m-d')) die('закончили');
                $page = 19;

                for (; ;) {

                    echo "\n page {$page}";

                    $events = $amoCRM->ajax()->get('/api/v4/events', [
                        'filter' => [
                            'created_at' => Carbon::create('2022-07-01')->timezone('Europe/Moscow')->timestamp,
                            'entity' => 'leads',
                            'value_after' => [
                                'leads_statuses' => [
                                    [
                                        'pipeline_id' => 4307329,
                                        'status_id'   => 40223485,
                                    ],
                                ],
                            ],
                        ],
                        'page' => $page,
                        'limit' => 100,
                    ]);

                    foreach ($events->_embedded->events as $event) {

                        try {

//                            sleep(1);

                            $lead = $amoCRM->leads()->find($event->entity_id);
//                            $lead = $amoApi->leads()->getOne($event->getEntityId());

                            if ($lead !== null) {

                                echo "\n". date('H:i:s'). " {$lead->id}";

                                try {
                                    Leads::query()->create([
                                        'event_id' => $event->id,
                                        'name' => $lead->name,
                                        'link' => 'https://misterstolicayandexru.amocrm.ru/leads/detail/' . $lead->id,
                                        'createdAt' => Carbon::parse($event->created_at)->timezone('Europe/Moscow')->format('Y-m-d H:i:s'),
                                        'createdBy' => $event->created_by,
                                        'date' => $lead->cf('Дата и время для пробного')->getValue(),
                                        'price' => $lead->sale,
                                        'contact_link' => 'https://misterstolicayandexru.amocrm.ru/contacts/detail/' . $lead->contact?->id ?? null,
                                    ]);

                                } catch (\Throwable $exception) {

                                    echo "\n BUG {$event->id}";

                                    continue;
                                }
                            } else
                                echo "\n нет сделки";

                        } catch (\Throwable $e) {

                            dd($e->getMessage());
                        }
                    }

                    unset($events);

                    $page++;
                }
//            }
        } catch (\Throwable $exception) {

            dd($exception->getMessage());
        }
    }

    private function getDate()
    {

    }
}
