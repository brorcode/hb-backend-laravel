<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TransactionsImportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int id
 * @property int user_id
 * @property int account_id
 * @property int status_id
 * @property string file_name
 * @property string file_path
 * @property int imported_count
 * @property string error
 * @property Carbon started_at
 * @property Carbon finished_at
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property-read User user
 * @property-read Account account
 *
 * @method static TransactionsImportFactory factory($count = null, $state = [])
 */
class TransactionsImport extends Model
{
    use HasFactory;

    const STATUS_ID_PROCESS = 1;
    const STATUS_ID_SUCCESS = 2;
    const STATUS_ID_FAILED = 3;

    const STATUSES = [
        self::STATUS_ID_PROCESS => 'В процессе',
        self::STATUS_ID_SUCCESS => 'Успешно',
        self::STATUS_ID_FAILED => 'Неуспешно',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function isProcess(): bool
    {
        return $this->status_id === self::STATUS_ID_PROCESS;
    }

    public function isSuccess(): bool
    {
        return $this->status_id === self::STATUS_ID_SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status_id === self::STATUS_ID_FAILED;
    }
}
