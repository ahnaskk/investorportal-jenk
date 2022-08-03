<?php

namespace App\Library\Repository;

use App\Label;
use App\Library\Repository\Interfaces\ILabelRepository;
use Illuminate\Support\Facades\DB;

class LabelRepository implements ILabelRepository
{
    public function __construct()
    {
        $this->table = new Label();
    }

    public function getAll()
    {
        return $this->table->get();
    }

    // public function get_insurance_label()
    // {
    //     return $this->table->whereIn('id', [3,4,5])->get();
    // }

    public function datatable($fields = null)
    {
        DB::statement(DB::raw('set @rownum=0'));
        $fields['rowrum'] = DB::raw('@rownum  := @rownum  + 1 AS rownum');
        if ($fields != null) {
            return $this->table->select($fields);
        }

        return $this->table;
    }

    public function createRequest($arr, $request = 0)
    {
        $flag = isset($arr['flag']) ? $arr['flag'] : 0;

        $label = $this->table->create(['name'=>$arr['name'], 'flag'=>$flag]);

        return $label;
    }

    public function updateRequest($request)
    {
        $label = $this->table->find($request->id);
        $label->update($request->all());
        $label->name = $request->name;
        $label->flag = isset($request->flag) ? $request->flag : 0;
        $label->save();

        return $label;
    }

    public function delete($id)
    {
        if ($label = $this->find($id)) {
            return $label->delete();
        }

        return false;
    }

    public function find($id)
    {
        return $this->table->find($id);
    }
}
