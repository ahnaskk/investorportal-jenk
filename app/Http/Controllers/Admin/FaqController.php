<?php

namespace App\Http\Controllers\Admin;

use FaqHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqRequest;
use App\Http\Requests\Admin\UpdateFaqRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder;

class FaqController extends Controller
{

    public function __construct()
    {
        
    }

    public function index(Builder $tableBuilder)
    {
        $result = FaqHelper::index($tableBuilder);

        return view('admin.faq.index', $result);
    }

    public function create()
    {
        $result = FaqHelper::create();

        return view('admin.faq.create', $result);
    }

    public function store(StoreFaqRequest $request)
    {
        try {
            DB::beginTransaction();
            $result = FaqHelper::store($request);
            if (! $result['status']) {
                throw new \Exception($result['message'], 1);
            }
            $url = $result['url'];
            $message_type = 'message';
            $message = $result['message'];
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            return redirect()->back()->withErrors($message);
        }

        return redirect()->to($url)->with($message_type, $message);
    }

    public function show($id)
    {
        $result = FaqHelper::show($id);

        return view('admin.faq.show', $result);
    }

    public function edit($id)
    {
        $result = FaqHelper::edit($id);

        return view('admin.faq.edit', $result);
    }

    public function update(UpdateFaqRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $result = FaqHelper::update($request, $id);
            if (! $result['status']) {
                throw new \Exception($result['message'], 1);
            }
            $url = $result['url'];
            $message = $result['message'];
            $message_type = 'message';
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            return redirect()->back()->withErrors($message);
        }

        return redirect()->to($url)->with($message_type, $message);
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $result = FaqHelper::destroy($request, $id);
            if (! $result['status']) {
                throw new \Exception($result['message'], 1);
            }
            $url = $result['url'];
            $message = $result['message'];
            $message_type = 'message';
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            return redirect()->back()->withErrors($message);
        }

        return redirect()->to($url)->with($message_type, $message);
    }

    public function datatable()
    {
        $result = FaqHelper::datatable();

        return $result;
    }
}
