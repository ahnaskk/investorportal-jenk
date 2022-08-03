<?php
namespace App\Library\Helpers;
use App\Library\Repository\Interfaces\IMessageRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ITransactionsRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Merchant;
use App\Models\Transaction;
use App\Settings;
use App\User;
use Carbon\Carbon;
use DataTables;
use FFM;
use Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Permissions;
class TransactionTableBuilder
{
    public function __construct(ITransactionsRepository $transaction)
    {
        $this->Transaction = $transaction;
        $this->loggedUser = Auth::user();
    }
}
