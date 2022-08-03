<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MTB;
use Yajra\DataTables\Html\Builder;
use App\Helpers\MerchantHelper;

class MarketplaceController extends Controller
{
    protected $role;
    protected $user;

    public function __construct(IRoleRepository $role, IUserRepository $user)
    {
        $this->role = $role;
        $this->user = $user;
        $this->user2 = Auth::user();
    }

    public function list(Request $request)
    {
        $page_title = 'Marketplace';        
        $funds = MerchantHelper::getMarketplaceMerchants($request);
        if (isset($request->filter)) {
            $filter_id = $request->filter;
        } else {
            $filter_id = 0;
        }

        return view('admin.marketplace.list', compact('funds', 'filter_id', 'page_title'));
    }

    public function listdocs($mid, Request $request, Builder $tableBuilder)
    {   
        $page_title = 'Investor Dashboard';
        if ($request->ajax() || $request->wantsJson()) {
            return MTB::marketPlaceDocumentsView($mid);
        }
        
        $tableBuilder = $tableBuilder->columns(MTB::marketPlaceDocumentsView($mid, true));
        $tableBuilder->parameters(['sDom' => 't']);
        $merchantId = $mid;

        return view('admin.marketplace.documents', compact('page_title', 'tableBuilder', 'merchantId'));
    }
}
