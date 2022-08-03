<?php

namespace App\Helpers;

use DataTables;
use App\Faq;
use Exception;
use FFM;

class FaqHelper
{
    public $user_type = 0;
    public $route = 0;
    public $link = 0;

    public function __construct()
    {
        $arr[] = '';
        $arr[] = 'investors::';
        $arr[] = 'merchants::';
        $links[] = '';
        $links[] = 'investors/';
        $links[] = 'merchants/';
        $this->user_type = 0;
        if (request()->segment(2) == 'investors') {
            $this->user_type = 1;
        } elseif (request()->segment(2) == 'merchants') {
            $this->user_type = 2;
        }
        $this->route = $arr[$this->user_type];
        $this->link = $links[$this->user_type];
    }

    /*
    Faq index page
    */
    public function index($tableBuilder)
    {
        $link = $this->link;
        $title = $page_title = 'All Faqs';
        $tableBuilder->ajax(url('/admin/'.$link.'faq/datatable'));
        $tableBuilder->parameters(['responsive' => true, 'autoWidth' => false, 'processing' => true, 'aaSorting' => [], 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns(
            [
                ['className' => 'details-control', 'orderable' => false, 'data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'defaultContent' => '', 'title' => '#', 'searchable' => false],
                ['data' => 'app', 'name' => 'app', 'title' => 'Web/App'],
                ['data' => 'title', 'name' => 'title', 'title' => 'Title'],
                ['data' => 'link', 'name' => 'link', 'title' => 'Link'],
                ['data' => 'description', 'name' => 'description', 'title' => 'Description'],
                ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]
            ]
        );
        $create = url('/admin/'.$link.'faq/create');
        
        return [
            'page_title' => $page_title,
            'tableBuilder' => $tableBuilder,
            'title' => $title,
            'create' => $create
        ];
    }

    /*
    Faq create page
    */
    public function create()
    {
        $user_type = $this->user_type;
        $link = $this->link;
        $page_title = 'Create New FAQ';
        $action = 'create';
        $user_type = $user_type;
        $url = url('/admin/'.$link.'faq/');
        $route = url('/admin/'.$link.'faq');
        
        return [
            'page_title' => $page_title,
            'action' => $action,
            'user_type' => $user_type,
            'url' => $url,
            'route' => $route
        ];
    }

    /*
    Faq store function
    */
    public function store($request)
    {
        $link = $this->link;
        $url = url('/admin/'.$link.'faq/');
        $status = false;
        try {
            $user_type = $this->user_type;
            $app = $request->app ? 1 : 0;
            $request->merge(['user_type' => $user_type, 'app' => $app]);
            $requestData = $request->all();
            if(!Faq::create($requestData)){
                throw new \Exception('Something went wrong!', 1);
            }
            $message = 'FAQ added!';
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'url' => $url,
            'status' => $status,
            'message' => $message,
        ];
    }

    /*
    Faq view function
    */
    public function show($id)
    {
        $faq = Faq::findOrFail($id);

        return [
            'faq' => $faq
        ];
    }

    /*
    Faq edit function
    */
    public function edit($id)
    {
        $page_title = 'Edit FAQ';
        $action = 'edit';
        $faq = Faq::findOrFail($id);
        $edit = url('admin/'.$this->link.'faq/'.$faq->id);
        $url = url('admin/'.$this->link.'faq/');

        return [
            'page_title' => $page_title,
            'action' => $action,
            'faq' => $faq,
            'edit' => $edit,
            'id' => $id,
            'url' => $url
        ];
    }

    /*
    Faq update function
    */
    public function update($request, $id)
    {
        $link = $this->link;
        $url = url('/admin/'.$link.'faq/');
        $status = false;
        try {
            $app = $request->app ? 1 : 0;
            $request->merge(['user_type' => $this->user_type, 'app' => $app]);
            $requestData = $request->all();
            $faq = Faq::findOrFail($id);
            if(! $faq->update($requestData)){
                throw new \Exception('FAQ not updated!', 1);
            }
            $message = 'FAQ updated!';
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return [
            'url' => $url,
            'status' => $status,
            'message' => $message,
        ];
    }

    /*
    Faq destroy function
    */
    public function destroy($request, $id)
    {
        $link = $this->link;
        $url = url('/admin/'.$link.'faq/');
        $status = false;
        try {
            if(! Faq::destroy($id)){
                throw new \Exception('FAQ not deleted!', 1);
            }
            $message = 'FAQ deleted!';
            $status = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return [
            'url' => $url,
            'status' => $status,
            'message' => $message,
        ];
    }

    /*
    Faq datatable function
    */
    public function datatable()
    {
        $faq = Faq::where('user_type', $this->user_type)->get();

        return DataTables::collection($faq)->addColumn('app', function ($data) {
            return $data->app ? 'App' : 'Web';
        })->addColumn('action', function ($data) {
            $edit = url('admin/'.$this->link.'faq/'.$data->id.'/edit');
            $delete = url('admin/'.$this->link.'faq'.'/'.$data->id);
            $return = '';
            $return .= '<a href="'.$edit.'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            $return .= '<form method="POST" action="'.$delete.'" accept-charset="UTF-8" style="display:inline">
	                                         '.method_field('DELETE').csrf_field().'<button type="submit" class="btn btn-xs btn-danger" title="Delete Faq" onclick="return confirm(&quot;Are you sure want to delete ?&quot;)"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>
											</form>';

            return $return;
        })->editColumn('created_at', function ($data) {
            return FFM::datetime($data->created_at);
        })->editColumn('updated_at', function ($data) {
            return FFM::datetime($data->updated_at);
        })->addIndexColumn()->make(true);
    }

}
