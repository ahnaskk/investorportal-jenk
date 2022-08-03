<?php

namespace App\Helpers;

use App\Merchant;
use App\MerchantFaq;
use Exception;

class FaqMerchantHelper
{
    /**
     * Individual merchant's faqs function.
     *
     * @param  $tableBuilder
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function index($tableBuilder, $merchant_id)
    {
        $title = $page_title = 'All Faqs';
        $tableBuilder->ajax(route('admin::merchants::faq.datatable', $merchant_id));
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false], ['data' => 'title', 'name' => 'title', 'title' => 'Title'], ['data' => 'description', 'name' => 'description', 'title' => 'Description'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
        $create = route('admin::merchants::merchantFaq.faq.create', $merchant_id);
        $merchant = Merchant::find($merchant_id);

        return [
            'page_title' => $page_title,
            'tableBuilder' => $tableBuilder,
            'title' => $title,
            'create' => $create,
            'merchant' => $merchant
        ];
    }

    /**
     * Create faq for individual merchant function.
     *
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function create($merchant_id)
    {
        $page_title = 'Create New FAQ';
        $action = 'create';
        $merchant = Merchant::find($merchant_id);
        $url = url('/admin/merchants/' . $merchant_id . '/faq/');

        return [
            'page_title' => $page_title,
            'action' => $action,
            'url' => $url,
            'merchant_id' => $merchant_id,
            'merchant' => $merchant
        ];
    }

    /**
     * Show faq for individual merchant function.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $faq = MerchantFaq::findOrFail($id);

        return [
            'faq' => $faq
        ];
    }

    /**
     * Edit faq for individual merchant function.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($merchant_id, $id)
    {
        $page_title = 'Edit FAQ';
        $action = 'edit';
        $faq = MerchantFaq::findOrFail($id);
        $edit = url('/admin/merchants/' . $merchant_id . '/faq/' . $faq->id);
        $url = url('/admin/merchants/' . $merchant_id . '/faq/');
        $merchant = Merchant::find($merchant_id);

        return [
            'page_title' => $page_title,
            'action' => $action,
            'faq' => $faq,
            'edit' => $edit,
            'url' => $url,
            'merchant_id' => $merchant_id,
            'id' => $id,
            'merchant' => $merchant
        ];
    }

    /**
     * Get datatable's data for merchant's faqs function.
     *
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function datatable($merchant_id)
    {
        $faq = MerchantFaq::where('merchant_id', $merchant_id)->get();

        return \DataTables::collection($faq)->addColumn('action', function ($data) {
            $edit = url('admin/merchants/' . $data->merchant_id . '/faq/' . $data->id . '/edit');
            $delete = url('/admin/merchants/' . $data->merchant_id . '/faq' . '/' . $data->id);
            $return = '';
            $return .= '<a href="' . $edit . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            $return .= '<form method="POST" action="' . $delete . '" accept-charset="UTF-8" style="display:inline">
	                                         ' . method_field('DELETE') . csrf_field() . '<button type="submit" class="btn btn-xs btn-danger" title="Delete Faq" onclick="return confirm(&quot;Confirm delete?&quot;)"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>
											</form>';

            return $return;
        })->editColumn('created_at', function ($data) {
            return \FFM::datetime($data->created_at);
        })->editColumn('updated_at', function ($data) {
            return \FFM::datetime($data->updated_at);
        })->addIndexColumn()->make(true);
    }

    /**
     * Store faq for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $merchant_id
     * @return \Illuminate\Http\Response
     */
    public function store($request, $merchant_id)
    {
        $status = false;
        $message = $url = '';
        try {
            $request->merge(['merchant_id' => $merchant_id]);
            $requestData = $request->all();
            if (MerchantFaq::create($requestData)) {
                $status = true;
                $message = 'FAQ Added';
            } else {
                throw new Exception('Cannot create FAQ.', 1);
            }
            $url = url("/admin/merchants/$merchant_id/faq/");
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message,
            'url' => $url
        ];
    }

    /**
     * Update faq for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $merchant_id
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function update($request, $merchant_id, $id)
    {
        $status = false;
        $message = $url = '';
        try {
            $request->merge(['merchant_id' => $merchant_id]);
            $requestData = $request->all();
            $faq = MerchantFaq::findOrFail($id);
            $url = url("/admin/merchants/$merchant_id/faq/");
            if ($faq->update($requestData)) {
                $status = true;
                $message = 'FAQ updated';
            } else {
                throw new Exception('Cannot update FAQ.', 1);
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message,
            'url' => $url
        ];
    }

    /**
     * Delete faq for merchant function.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $merchant_id
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($merchant_id, $id)
    {
        $status = false;
        $message = $url = '';
        try {
            if (MerchantFaq::destroy($id)) {
                $url = url("/admin/merchants/$merchant_id/faq/");
                $status = true;
                $message = 'FAQ deleted';
            } else {
                throw new Exception('Cannot update FAQ.', 1);
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message,
            'url' => $url
        ];
    }
}
