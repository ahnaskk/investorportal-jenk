<?php

namespace App\Http\Resources\Investor;

use App\Library\Helpers\FieldFormatter;
use FFM;
use Illuminate\Http\Resources\Json\JsonResource;

class StatementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = \Auth::user();

        return [
            'id'               => $this->id,
            'file_name'        => $this->file_name,
            'from_date'        => ($this->from_date != '0000-00-00') ? FFM::date($this->from_date) : '00-00-0000',
            'investor_portal'  => $this->investor_portal,
            'mail_status'      => $this->mail_status,
            'to_date'          => ($this->to_date != '0000-00-00') ? FFM::date($this->to_date) : '00-00-0000',
            'user_id'          => $this->user_id,
            'created_at'       => (new FieldFormatter)->datetime($this->created_at),
            'updated_at'       => (new FieldFormatter)->datetime($this->created_at),
            'download_csv_url' => url('api/investor/download/statement/'.$this->id.'?type=csv&token='.$user->getDownloadToken()),
            'download_pdf_url' => url('api/investor/download/statement/'.$this->id.'?type=pdf&token='.$user->getDownloadToken()),
        ];
    }
}
