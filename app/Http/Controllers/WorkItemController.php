<?php

namespace App\Http\Controllers;

use App\{
    Work,
    WorkItem
};
use JWTAuth;
use Illuminate\Http\Request;

class WorkItemController extends Controller
{
    private $workItemReturnKeys = ['id', 'name', 'unit_id', 'unit_name', 'cost_type_id', 'cost_type_name'];

    public function listWithoutWork()
    {
        $workItems = WorkItem::with('unit', 'costType')->get()->map(function (WorkItem $workItem) {
            return array_only(
                $workItem
                    ->setAttribute('unit_name', $workItem->unit->name)
                    ->setAttribute('cost_type_name', $workItem->costType->name)
                    ->toArray(),
                $this->workItemReturnKeys
            );
        });

        return response()->json(['data' => $workItems]);
    }

    public function createWithoutWork(Request $request)
    {
        $workItem = WorkItem::create([
            'name' => $request->name,
            'unit_id' => $request->unit_id,
            'cost_type_id' => $request->cost_type_id
        ])->load('unit', 'costType');

        $workItem->setAttribute('unit_name', $workItem->unit->name)
            ->setAttribute('cost_type_name', $workItem->costType->name);

        return response()->json(['data' => array_only($workItem->toArray(), $this->workItemReturnKeys)], 201);
    }

    public function list(Work $work)
    {
        $workItems = $work->workItems()->with('unit', 'costType')->get()->map(function (WorkItem $workItem) use ($work) {
            return $this->transformWorkItem($work, $workItem);
        });

        return response()->json(['data' => $workItems]);
    }

    public function stats(Work $work)
    {
        $workItems = $work->workItems()->with('costType')->get();

        $stats = $workItems->groupBy('cost_type_id')->map(function ($workItems) {
            return [
                'cost_type_id' => $workItems->first()->costType->id,
                'cost_type_name' => $workItems->first()->costType->name,
                'sum' => $workItems->map(function ($workItem) {
                    return bcmul($workItem->pivot->amount, $workItem->pivot->unit_price, 2);
                })->reduce(function ($carry, $unitPrice) {
                    return bcadd($carry, $unitPrice, 2);
                }, '0')
            ];
        });

        return response()->json(['data' => $stats->values()]);
    }

    public function create(Request $request, Work $work, WorkItem $workItem)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->id !== $work->user_id) {
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

        return response()->json(['data' => $this->transformWorkItem($work, $workItem)], 201);
    }

    public function delete(Work $work, WorkItem $workItem)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->id !== $work->user_id) {
            return response()->json([], 403);
        }

        $work->workItems()->detach($workItem);

        return response()->json([], 204);
    }

    private function transformWorkItem(Work $work, WorkItem $workItem)
    {
        $workItem = $work->workItems()->with('unit', 'costType')->find($workItem->id);

        $workItem->setAttribute('work_id', $workItem->pivot->work_id);
        $workItem->setAttribute('amount', $workItem->pivot->amount);
        $workItem->setAttribute('unit_price', $workItem->pivot->unit_price);
        $workItem->setAttribute('unit_name', $workItem->unit->name);
        $workItem->setAttribute('cost_type_name', $workItem->costType->name);

        return $workItem;
    }
}
