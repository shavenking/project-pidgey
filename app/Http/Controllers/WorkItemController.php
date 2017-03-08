<?php

namespace App\Http\Controllers;

use App\Work;
use Illuminate\Http\Request;

class WorkItemController extends Controller
{
    public function list(Work $work)
    {
        $workItems = $work->workItems()->with('unit', 'costType')->get()->map(function ($workItem) {
            $workItem->setAttribute('work_id', $workItem->pivot->work_id);
            $workItem->setAttribute('amount', $workItem->pivot->amount);
            $workItem->setAttribute('unit_price', $workItem->pivot->unit_price);
            $workItem->setAttribute('unit_name', $workItem->unit->name);
            $workItem->setAttribute('cost_type_name', $workItem->costType->name);

            return $workItem;
        });

        return response()->json(['data' => $workItems]);
    }
}
