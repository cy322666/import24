<?php

namespace App\Console\Commands;

use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\NoteType\CommonNote;
use App\Models\Account;
use App\Models\Bitrix\Company;
use App\Models\Bitrix\CompanyComment;
use App\Services\amoCRM\Client;
use Illuminate\Console\Command;
use Ramsey\Collection\Collection;

class SendCompaniesCommentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:crm.companies.comments.send';

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
        $companyComments = CompanyComment::query()
            ->where('status', 0)
            ->get()
            ->groupBy('company_id');

        $amoApi = (new Client())->getInstance(Account::all()->first());

        foreach ($companyComments as $companyComment) {

            $company = Company::query()
                ->where('company_id', $companyComment->first()->company_id)
                ->firstOrFail();

            if ($companyComment->count() > 1) {

                $text = $companyComment->implode('text', "\n\n");

            } else {
                $companyComment = $companyComment->first();
                $text = $companyComment->text;
            }

            $notesCollection = new NotesCollection();

            $serviceNote = new CommonNote();
            $serviceNote->setEntityId(1)
                ->setEntityId($company->entity_id)
                ->setText($text)
                ->setCreatedBy(0);

            $notesCollection->add($serviceNote);

            try {
                $companyNotesService = $amoApi->notes(EntityTypesInterface::COMPANIES);
                $companyNotesService->add($notesCollection);

                if ($companyComment instanceof Collection) {

                    foreach ($companyComment as $comment) {

                        $comment->status = 1;
                        $comment->save();
                    }
                } elseif($companyComment instanceof CompanyComment) {

                    $companyComment->status = 1;
                    $companyComment->save();
                }

                print_r($company->entity_id."\n");

            } catch (AmoCRMApiException $exception) {

                dd($exception->getDescription());
            }
        }
    }
}
