<?php

namespace App\Helpers;

use App\MerchantUser;
use App\ParticipentPayment;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Merchant;
use App\MerchantBankAccount;
use Yajra\DataTables\Html\Builder;
use Permissions;
use FFM;
use APP\MarketOffers;
use Form;
use App\Template;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ISubStatusRepository;
use App\MerchantMarketOffers;
use App\InvestorsOffers;


class MarketOfferHelper
{
	public function __construct(IRoleRepository $role, ISubStatusRepository $subStatus)
    {
        
        $this->role = $role;
        $this->subStatus = $subStatus;
       
    }
	public function merchantMarketOfferList($tableBuilder){
	$tableBuilder->ajax(['url' => route('admin::merchant_market_offer_data')]);
	$tableBuilder = $tableBuilder->columns([['data' => 'id', 'name' => 'id', 'title' => '#'], ['data' => 'offers', 'name' => 'offers', 'title' => 'Market Offer', 'orderable' => false], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
	$tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }", 'pagingType' => 'input']);
	
	}
	public function investorMarketOfferList($tableBuilder){
	    $tableBuilder->ajax(['url' => route('admin::investor_market_offer_data')]);
        $tableBuilder = $tableBuilder->columns([['data' => 'id', 'name' => 'id', 'title' => '#'], ['data' => 'offers', 'name' => 'offers', 'title' => 'Market Offer', 'orderable' => false], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(0)', nRow).html(index).addClass('txt-center');\n           }", 'pagingType' => 'input']);
	
	}
	public function merchantMarketOfferEdit(){        
		$tem_ids = MerchantMarketOffers::groupBy('offer_id')->pluck('offer_id')->toArray();
        $data = MarketOffers::whereIn('id', $tem_ids);

        return \DataTables::of($data)->editColumn('offers', function ($data) {
            return $data->offers;
        })->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Marketing Offers', 'Edit')) {
                $return .= '<a href="'.route('admin::addEditMerchantsOffers', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (Permissions::isAllow('Marketing Offers', 'Delete')) {
                $return .= Form::open(['route' => ['admin::merchant_delete_Offers', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }

            return $return;
        })->rawColumns(['action'])->make(true);
	}
	public function investorMarketOfferEdit(){
		$tem_ids = InvestorsOffers::groupBy('offer_id')->pluck('offer_id');
        $data = MarketOffers::whereIn('id', $tem_ids);

        return \DataTables::of($data)->editColumn('offers', function ($data) {
            return $data->offers;
        })->addColumn('action', function ($data) {
            $return = '';
            if (Permissions::isAllow('Marketing Offers', 'Edit')) {
                $return .= '<a href="'.route('admin::addEditInvestorsOffers', ['id' => $data->id]).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            }
            if (Permissions::isAllow('Marketing Offers', 'Delete')) {
                $return .= Form::open(['route' => ['admin::investor_delete_Offers', 'id' => $data->id], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
            }

            return $return;
        })->rawColumns(['action'])->make(true);
	}
	public function merchantOfferDelete($id){
		try {
			if(!MarketOffers::where('id', $id)->delete()){
				throw new \Exception("Something went wrong in Market Offers", 1);	
			}
            if(MerchantMarketOffers::where('id', $id)->first()){
			if(!MerchantMarketOffers::where('id', $id)->delete()){
				throw new \Exception("Something went Wrong in Merchant Market Offers", 1);	
			}
            }
			$return['result']='success';
		} catch (\Exception $e) {
			$return['result']=$e->getMessage();
		}
		return $return;
    }
    public function investorOfferDelete($id){
    	try {
			if(!MarketOffers::where('id', $id)->delete()){
				throw new \Exception("Something went wrong in Market Offers", 1);	
			}
            if(MerchantMarketOffers::where('id', $id)->first()){
			if(!MerchantMarketOffers::where('id', $id)->delete()){
				throw new \Exception("Something went Wrong in Investor Market Offers", 1);	
			}
            }
			$return['result']='success';
		} catch (\Exception $e) {
			$return['result']=$e->getMessage();
		}
		return $return;

    }
    public function createOrEditMerchantOffer($id){
    	$selected_merchant = [];
        $compact = [];
        $investors = $this->role->allInvestors()->pluck('name', 'id');
        $compact['investors'] = $investors;
        
        $template_types = Template::getTypes();
        $statuses = $this->subStatus->getAll()->pluck('name', 'id');
        $template = Template::whereNull('temp_code')->pluck('title', 'id');
        $compact['template'] = $template;
        $compact['template_types'] = $template_types;
        $compact['statuses'] = $statuses;
        if ($id) {
            $selected_merchant = MerchantMarketOffers::where('offer_id', $id)->pluck('merchant_id');
            $selected_merchant = Merchant::whereIn('id', $selected_merchant)->select('name', 'id')->get();
            $offer = MarketOffers::where('id', $id)->first()->toArray();
            $compact['offers'] = $offer;
            $compact['page_title'] = 'Edit Merchants Marketing Offers';
            $compact['selected_merchant'] = $selected_merchant;
        }
        return $compact;

    }
    public function createOrEditInvestorOffer($id){
    	$selected_merchant = [];
        $compact = [];
        $investors = $this->role->allInvestors()->pluck('name', 'id');
        $compact['investors'] = $investors;        
        $template_types = Template::getTypes();
        $investor_types = User::getInvestorType();
        $template = Template::whereNull('temp_code')->pluck('title', 'id');
        $compact['template'] = $template;
        $compact['template_types'] = $template_types;
        $compact['investor_types'] = $investor_types;
        if ($id) {
            $selected_investors = InvestorsOffers::where('offer_id', $id)->pluck('investor_id');
            $offer = MarketOffers::where('id', $id)->first()->toArray();
            $compact['offers'] = $offer;
            $compact['page_title'] = 'Edit Investor Marketing Offers';
            $compact['selected_investor'] = $selected_investors;
        }
        return $compact;

    }


}