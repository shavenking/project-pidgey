<?php

namespace App\Http\Controllers;

use App\{
    Work,
    WorkItem
};
use Illuminate\Http\Request;

class WorkItemController extends Controller
{
    public function list(Work $work)
    {
        $workItems = $work->workItems()->with('unit', 'costType')->get()->map(function (WorkItem $workItem) {
            return $this->transformWorkItem($workItem);
        });

        return response()->json(['data' => $workItems]);
    }

    public function create(Request $request, Work $work, WorkItem $workItem)
    {
        if ($request->has('work_item_id') && $request->has('name', 'unit_price', 'cost_type_id')) {
            return response()->json([], 403);
        }

        if ($request->has('work_item_id')) {
            $workItem = $workItem->find($request->work_item_id);
        } else {
            $workItem = $workItem->newInstance([
                'name' => $request->name,
                'unit_id' => $request->unit_id,
                'cost_type_id' => $request->cost_type_id
            ]);
        }

        \DB::beginTransaction();

        $workItem->save();

        $work->workItems()->attach($workItem, [
            'amount' => $request->amount,
            'unit_price' => $request->unit_price
        ]);

        $work->update(['unit_price' => bcadd($work->unit_price, bcmul($request->amount, $request->unit_price, 2), 2)]);

        \DB::commit();

        return response()->json(['data' => $this->transformWorkItem($workItem)], 201);
    }

    public function delete(Work $work, WorkItem $workItem)
    {
        $work->workItems()->detach($workItem);

        return response()->json([], 204);
    }

    private function transformWorkItem(WorkItem $workItem)
    {
        $workItem->load('unit', 'costType');

        $workItem->setAttribute('work_id', $workItem->pivot_work_id);
        $workItem->setAttribute('amount', $workItem->pivot_amount);
        $workItem->setAttribute('unit_price', $workItem->pivot_unit_price);
        $workItem->setAttribute('unit_name', $workItem->unit->name);
        $workItem->setAttribute('cost_type_name', $workItem->costType->name);

        return $workItem;
    }
}
