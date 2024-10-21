<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\TransactionsImport;
use App\Models\User;
use App\Services\ImportTransactions\ImportService;
use App\Services\ImportTransactions\NotificationService;
use App\Services\OwnerService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use Throwable;

class TransactionsImportJob implements ShouldQueue
{
    use Queueable;

    private User $user;
    private TransactionsImport $transactionsImport;
    private Account $account;
    private ImportService $importService;
    private NotificationService $notificationService;

    public function __construct(User $user, TransactionsImport $transactionsImport, Account $account)
    {
        $this->user = $user;
        $this->transactionsImport = $transactionsImport;
        $this->account = $account;
        $this->importService = ImportService::create();
        $this->notificationService = NotificationService::create();
        $this->onQueue('long-running');
    }

    public function tags(): array
    {
        return [(new ReflectionClass(static::class))->getShortName()];
    }

    public function handle(): void
    {
        $exception = null;
        $service = OwnerService::make();
        $service->setUser($this->user);

        try {
            $this->importService->handle($this->transactionsImport->file_path, $this->account);
            $imported = $this->importService->getImportedCount();

            $this->transactionsImport->imported_count = $imported;
            $this->transactionsImport->status_id = TransactionsImport::STATUS_ID_SUCCESS;
            $message = "Импорт транзакций для {$this->account->name} завершен. Импортировано {$imported}";
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $this->transactionsImport->status_id = TransactionsImport::STATUS_ID_FAILED;
            $this->transactionsImport->error = $e->getMessage();
            $exception = $e;
            $message = "Импорт транзакций для {$this->account->name} завершен с ошибками";
        }

        $this->transactionsImport->finished_at = now();
        $this->transactionsImport->save();

        $this->removeUploadedFile();

        $this->notificationService->addMessage($this->user, $message);

        if ($exception) {
            $this->fail($exception);
        }
    }

    public function failed(?Throwable $e): void
    {
        if ($e) {
            logger()->error($e->getMessage());
            $this->transactionsImport->error = $e->getMessage();
        }

        $this->transactionsImport->status_id = TransactionsImport::STATUS_ID_FAILED;
        $this->transactionsImport->finished_at = now();
        $this->transactionsImport->save();

        $this->removeUploadedFile();
        $this->notificationService->addMessage(
            $this->user,
            "Импорт транзакций для {$this->account->name} завершен с ошибками",
        );
    }

    private function removeUploadedFile(): void
    {
        if (Storage::exists($this->transactionsImport->file_path)) {
            Storage::delete($this->transactionsImport->file_path);
        }
    }
}
