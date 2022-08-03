<?php
/**
* Created by Rahees.
* User: raheesiocod
* Date: 4/10/21
*/
namespace App\Library\Repository\Interfaces;
use App\Http\Requests;
interface ISubAdminRepository
{
    public function index();
    public function rowData();
    public function store(Requests\AdminCreateSubAdminRequest $request);
    public function find($id);
    public function update(Requests\AdminUpdateSubAdminRequest $request,$id);
    public function delete($id);
}
