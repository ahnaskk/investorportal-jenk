<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqMerchantRequest;
use App\Http\Requests\Admin\UpdateFaqMerchantRequest;
use App\Library\Facades\FaqMerchantHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder;

class FaqMerchantController extends Controller
{
    /**
     * Individual merchant's faqs page.
     *
     * @param  $tableBuilder
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function index(Builder $tableBuilder, $merchant_id)
    {
        $result = FaqMerchantHelper::index($tableBuilder, $merchant_id);

        return view('admin.merchants.faq.index', $result);
    }

    /**
     * Create faq for merchant page.
     *
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function create($merchant_id)
    {
        $result = FaqMerchantHelper::create($merchant_id);

        return view('admin.merchants.faq.create', $result);
    }

    /**
     * Show faq for merchant page.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $result = FaqMerchantHelper::show($id);

        return view('faq.show', $result);
    }

    /**
     * Store faq for merchant link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFaqMerchantRequest $request, $merchant_id)
    {
        try {
            DB::beginTransaction();
            $result = FaqMerchantHelper::store($request, $merchant_id);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $url = $result['url'];
            DB::commit();
            return redirect()->to($url)->with('message', $message);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Edit faq for merchant page.
     *
     * @param  $merchant_id
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($merchant_id, $id)
    {
        $result = FaqMerchantHelper::edit($merchant_id, $id);

        return view('admin.merchants.faq.edit', $result);
    }

    /**
     * Update faq for merchant link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFaqMerchantRequest $request, $merchant_id, $id)
    {
        try {
            DB::beginTransaction();
            $result = FaqMerchantHelper::update($request, $merchant_id, $id);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $url = $result['url'];
            DB::commit();
            return redirect()->to($url)->with('message', $message);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Delete faq for merchant link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($merchant_id, $id)
    {
        try {
            DB::beginTransaction();
            $result = FaqMerchantHelper::delete($merchant_id, $id);
            $message = $result['message'];
            if (!$result['status']) {
                throw new Exception($message ?? 'Something Went Wrong', 1);
            }
            $url = $result['url'];
            DB::commit();
            return redirect()->to($url)->with('message', $message);
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Get datatable's data for merchant's faqs page.
     *
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function datatable($merchant_id)
    {
        $result = FaqMerchantHelper::datatable($merchant_id);

        return $result;
    }
}
