<?php

namespace App\Exports;

use App\Models\OrderDetails;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OrderReportExport implements FromView
{
   public $details = [];
   public function __construct($details = [])
   {
    	$this->details = $details; 
   }
    public function view(): View
    {
      	$details = $this->details;
        return view('backend.reports.order_report_export', compact('details'));
    }

}
