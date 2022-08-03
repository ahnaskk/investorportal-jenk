<?php
namespace App\Library\Repository;
use Illuminate\Http\Request;
use App\Library\Repository\Interfaces\IVisitorRepository;
use App\User;
use App\Models\Visitor;
use Exception;

class VisitorRepository implements IVisitorRepository
{
	public function __construct() {
		$this->request = app('request');
	}
	public function iIndex(Request $request) {
		$onlineUsers = User::online()->pluck('id','id');
		$Visitors = New Visitor;
		$Visitors = $Visitors->whereRaw('id IN (select MAX(id) FROM visitors GROUP BY visitor_id)');
		$Visitors = $Visitors->latest();
		$Visitors = $Visitors->get();
		$return['onlineUsers'] = $onlineUsers;
		$return['Visitors']    = $Visitors;
		return $return;
	}
}
