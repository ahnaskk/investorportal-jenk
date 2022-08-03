<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Library\Repository\Interfaces\IMarketOfferRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use APP\MarketOffers;
use App\Merchant;
use App\Template;
use App\User;
use Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Permissions;
use Yajra\DataTables\Html\Builder;
use MarketOfferHelper;

class MarketOfferController extends Controller
{
    public function __construct(IMarketOfferRepository $offer, IRoleRepository $role, ISubStatusRepository $subStatus)
    {
        $this->offer = $offer;
        $this->role = $role;
        $this->subStatus = $subStatus;
    }

    public function merchantMarketOfferList(Builder $tableBuilder)
    {
        $page_title = 'Merchant Marketing Offers';
        $result=MarketOfferHelper::merchantMarketOfferList($tableBuilder);
        return view('admin.market_offers.merchantOffersList', compact('page_title', 'tableBuilder'));
    }

    public function investorMarketOfferList(Builder $tableBuilder)
    {
        $page_title = 'Investor Marketing Offers';
        $result=MarketOfferHelper::investorMarketOfferList($tableBuilder);
        return view('admin.market_offers.investorsOffersList', compact('page_title', 'tableBuilder'));
    }

    public function merchantMarketOfferDataAction()
    {
        return $result=MarketOfferHelper::merchantMarketOfferEdit();
        
    }

    public function investorMarketOfferDataAction()
    {
        return $result=MarketOfferHelper::investorMarketOfferEdit();        
    }

    public function merchantDeleteOfferAction(Request $request, $id)
    {  
       try { 
            DB::beginTransaction();           
            $result=MarketOfferHelper::merchantOfferDelete($id);            
            if($result['result']!='success') throw new \Exception($result['result'], 1);            
            $request->session()->flash('message', 'Merchant Marketing offer deleted!');
            DB::commit();
            return redirect()->route('admin::merchantMarketOfferList');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }       
        
    }

    public function investorDeleteOfferAction(Request $request, $id)
    {
        
      try { 
            DB::beginTransaction();           
            $result=MarketOfferHelper::investorOfferDelete($id);            
            if($result['result']!='success') throw new \Exception($result['result'], 1);            
            $request->session()->flash('message', 'Investor Marketing offer deleted!');
            DB::commit();
            return redirect()->route('admin::investorMarketOfferList');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }  
       
    }

    public function addEditMerchantsOfferAction(Request $request)
    {
        $id = $request->get('id');
        if($id){
        $page_title = 'Edit Merchants Marketing Offers';    
        }else{
        $page_title = 'Create Merchants Marketing Offers';
        }
        $compact=MarketOfferHelper::createOrEditMerchantOffer($id);        
        $compact['page_title'] = $page_title;      
        return view('admin.market_offers.addEditMerchantsOffers')->with($compact);
    }

    public function addEditInvestorsOfferAction(Request $request)
    {
        $id = $request->get('id');
        if($id){
        $page_title = 'Edit Investor Marketing Offers';    
        }else{
        $page_title = 'Create Investor Marketing Offers';
        }
        $compact=MarketOfferHelper::createOrEditInvestorOffer($id);        
        $compact['page_title'] = $page_title;  
        return view('admin.market_offers.addEditInvestorsOffers')->with($compact);
    }

    public function addUpdateMerchantMarketOfferAction(Requests\AdminCreateOfferRequest $request)
    {
        $merchants = $request->merchants;
        $investors = $request->investors;
        $status = 0;
        if (! empty($merchants)) {
            $status = 1;
        } elseif (! empty($investors)) {
            $status = 1;
        }
        if ($status == 0) {
            return redirect()->back()->withErrors(['Enter at least one Merchants '])->withInput();
        }
        try {
            if ($request->offer_id) {
                $offer = $this->offer->updateRequest($request);
                if ($offer['msg']) {
                    $request->session()->flash('message', $offer['msg']);
                }

                return redirect()->route('admin::merchantMarketOfferList');
            } else {
                $offer = $this->offer->createRequest($request);
                if ($offer['msg']) {
                    $request->session()->flash('message', $offer['msg']);
                }

                return redirect()->route('admin::merchantMarketOfferList');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function addUpdateInvestorMarketOfferAction(Requests\AdminCreateOfferRequest $request)
    {
        $merchants = $request->merchants;
        $investors = $request->investors;
        $status = 0;
        if (! empty($merchants)) {
            $status = 1;
        } elseif (! empty($investors)) {
            $status = 1;
        }
        if ($status == 0) {
            return redirect()->back()->withErrors(['Enter at least one Investors'])->withInput();
        }
        try {
            if ($request->offer_id) {
                $offer = $this->offer->updateRequest($request);
                if ($offer['msg']) {
                    $request->session()->flash('message', $offer['msg']);
                }

                return redirect()->route('admin::investorMarketOfferList');
            } else {
                $offer = $this->offer->createRequest($request);
                if ($offer['msg']) {
                    $request->session()->flash('message', $offer['msg']);
                }

                return redirect()->route('admin::investorMarketOfferList');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
