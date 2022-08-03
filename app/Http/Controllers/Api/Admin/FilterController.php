<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\InvestorTransactionHelper;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessResource;
use App\Industries;
use App\Merchant;
use App\Rcode;
use App\SubStatus;
use App\User;
use Illuminate\Http\Request;

class FilterController extends AdminAuthController
{
    public function getMerchant(Request $request)
    {
        $limit = $request->input('limit', 20);
        $page = $request->input('page', 0);
        $search = $request->input('search', '');
        $merchants = Merchant::where('active_status', 1);
        if ($search) {
            $merchants->where(function ($query) use ($search) {
                $query->orWhere('name', 'like', '%'.$search.'%');
            });
        }
        $merchants = $merchants->orderBy('name')->select('name', 'id')->paginate($limit);
        $page = $merchants->hasMorePages() ? $page + 1 : 0;
        $hasMore = $merchants->hasMorePages();
        $merchants = $merchants->map(function ($merchant) {
            return ['id' => $merchant->id, 'text' => $merchant->name, 'name' => $merchant->name];
        });

        return new SuccessResource(['data' => $merchants, 'has_more' => $hasMore]);
    }

    public function getInvestor(Request $request)
    {
        $search = $request->input('search', '');
        $query = User::investors();
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('name', 'like', '%'.$search.'%');
                $inner->orWhere('email', 'like', '%'.$search.'%');
            });
        }
        $investors = $query->get()->map(function ($investor) {
            return ['id' => $investor->id, 'name' => $investor->name];
        });

        return new SuccessResource(['data' => $investors, 'has_more' => false]);
    }

    public function getLender(Request $request)
    {
        $search = $request->input('search', '');
        $query = User::getLenders();
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('name', 'like', '%'.$search.'%');
                $inner->orWhere('email', 'like', '%'.$search.'%');
            });
        }
        $lenders = $query->get()->map(function ($lender) {
            return ['id' => $lender->id, 'name' => $lender->name];
        });

        return new SuccessResource(['data' => $lenders, 'has_more' => false]);
    }

    public function getSubStatus(Request $request)
    {
        $search = $request->input('search', '');
        $query = SubStatus::where('id', '>', 0);
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('name', 'like', '%'.$search.'%');
            });
        }
        $statuses = $query->orderBy('name')->get()->map(function ($status) {
            return ['id' => $status->id, 'name' => $status->name];
        });

        return new SuccessResource(['data' => $statuses, 'has_more' => false]);
    }

    public function getAdvanceType(Request $request)
    {
        $search = $request->input('search', '');
        $advanceTypes = Merchant::getAdvanceTypes();
        $advanceTypes = $this->parseResponse($advanceTypes);
        if (! empty($search)) {
            $advanceTypes = collect($advanceTypes)->filter(function ($type) use ($search) {
                return strpos(optional($type)['name'], $search) !== false;
            });
        }

        return new SuccessResource(['data' => $advanceTypes, 'has_more' => false]);
    }

    public function getCompany(Request $request)
    {
        $search = $request->input('search', '');
        $query = User::getAllCompanies();
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('name', 'like', '%'.$search.'%');
                $inner->orWhere('email', 'like', '%'.$search.'%');
            });
        }
        $companies = $query->orderBy('name')->get()->map(function ($company) {
            return ['id' => $company->id, 'name' => $company->name];
        });

        return new SuccessResource(['data' => $companies, 'has_more' => false]);
    }

    public function getInvestorType(Request $request)
    {
        $search = $request->input('search', '');
        $types = User::getInvestorType();
        $types = $this->parseResponse($types);
        if (! empty($search)) {
            $types = collect($types)->filter(function ($type) use ($search) {
                return strpos(optional($type)['name'], $search) !== false;
            })->toArray();
        }

        return new SuccessResource(['data' => $types, 'has_more' => false]);
    }

    public function getRcode(Request $request)
    {
        $search = $request->input('search', '');
        $query = Rcode::where('id', '>', 0);
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('code', 'like', '%'.$search.'%');
                $inner->orWhere('description', 'like', '%'.$search.'%');
            });
        }
        $rCodes = $query->get()->map(function ($rCode) {
            return ['id' => $rCode->id, 'name' => $rCode->description.'('.$rCode->code.')'];
        });

        return new SuccessResource(['data' => $rCodes, 'has_more' => false]);
    }

    public function getTransactionType(Request $request)
    {
        $search = $request->input('search', '');
        $types = ['0' => 'Select Transaction Type', '1' => 'Debit', '2' => 'Credit'];
        $types = $this->parseResponse($types);

        return new SuccessResource(['data' => $types, 'has_more' => false]);
    }

    public function parseResponse($data)
    {
        return array_map(function ($id, $name) {
            return ['id' => $id, 'name' => $name];
        }, array_keys($data), $data);
    }

    public function getTransactionCategory(Request $request)
    {
        $search = $request->input('search', '');
        $categories = InvestorTransactionHelper::getAllOptions();
        $categories = $this->parseResponse($categories);
        if (! empty($search)) {
            $categories = collect($categories)->filter(function ($category) use ($search) {
                return strpos(optional($category['name']), $search) !== false;
            })->toArray();
        }

        return new SuccessResource(['data' => $categories, 'has_more' => false]);
    }

    public function getLabel()
    {
        $labels = ['MCA' => 'MCA (default)', 'Luthersales' => 'Luther Sales', 'Insurance' => 'Insurance'];
        $labels = $this->parseResponse($labels);

        return new SuccessResource(['data' => $labels, 'has_more' => false]);
    }

    public function getIndustry(Request $request)
    {
        $search = $request->input('search', '');
        $query = Industries::where('id', '>', 0);
        if (! empty($search)) {
            $query->where(function ($inner) use ($search) {
                $inner->orWhere('name', 'like', '%'.$search.'%');
            });
        }
        $industries = $query->get()->map(function ($industry) {
            return ['id' => $industry->id, 'name' => $industry->name];
        });

        return new SuccessResource(['data' => $industries, 'has_more' => false]);
    }

    public function getSubStatusFlag(Request $request)
    {
        $search = $request->input('search', '');
        $subStatusFlags = DB::table('sub_status_flags')->pluck('name','id')->toArray();
        $subStatusFlags = $this->parseResponse($subStatusFlags);

        return new SuccessResource(['data' => $subStatusFlags, 'has_more' => false]);
    }

    public function getOverpayment(Request $request)
    {
        $overPayments = [0 => 'All', 1 => 'Excluded', 2 => 'Included'];
        $overPayments = $this->parseResponse($overPayments);

        return new SuccessResource(['data' => $overPayments, 'has_more' => false]);
    }

    public function getDays()
    {
        $days = [0 => '0-60', 61 => '61-90', 91 => '91-120', 121 => '121-150', 150 => '150+'];
        $days = $this->parseResponse($days);

        return new SuccessResource(['data' => $days, 'has_more' => false]);
    }
}
