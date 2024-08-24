<?php

namespace App\Http\Controllers\Api;

use App\Events\UserRedeem;
use App\Http\Controllers\Api\Enum\RedeemStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetRedeemRequest;
use App\Http\Resources\RedeemResource;
use App\Models\Redeem;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RedeemController extends Controller
{
    /**
     * Get all the Redeem items
     *
     * Display in a list all the redeem items from the shop
     *
     * @param  GetRedeemRequest  $request
     * @return AnonymousResourceCollection
     */
    public function index(GetRedeemRequest $request)
    {
        $redeem = Redeem::query();

        return RedeemResource::collection($redeem->user($request->user)->status($request->status)->fastPaginate());
    }

    /**
     * Redeem Item from Shop
     *
     * @param  Request  $request
     * @return RedeemResource
     */
    public function store(Request $request)
    {
        $request->validate([
            'shop' => [
                'required',
                'exists:shops,id',
            ],
        ]);
        $shop = Shop::query()->findOrFail($request->shop);
        $redeem = Redeem::create([
            'shop_id' => $shop?->id,
            'user_id' => $request->user()->id,
            'status' => RedeemStatusEnum::WAITING_APPROVAL->value,
            'product_id' => $shop?->product?->id,
        ]);

        UserRedeem::dispatch($redeem, $request->user(), $shop);

        return RedeemResource::make($redeem);
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return RedeemResource
     */
    public function show($id)
    {
        $redeem = Redeem::findOrFail($id);

        return RedeemResource::make($redeem);
    }

    /**
     * Cancel the redeem item
     *
     * @param  $id
     * @return JsonResponse|Response
     */
    public function cancel($id)
    {
        $redeem = Redeem::findOrFail($id);
        if ($redeem->status != RedeemStatusEnum::WAITING_APPROVAL) {
            return response()->json([
                'message' => 'Unable to canceled redeem due to it is already in process.',
                'status' => false,
            ], HttpResponse::HTTP_FORBIDDEN);
        }

        $redeem->status = RedeemStatusEnum::CANCELED->value;
        $redeem->save();

        return response()->noContent();
    }

    /**
     * Update Redeemed Status
     *
     * @param  Request  $request
     * @param  $id
     * @return RedeemResource|JsonResponse
     */
    public function status(Request $request, $id)
    {
        $request->validate([
            'status' => [
                'required',
                'string',
                Rule::enum(RedeemStatusEnum::class),
            ],
        ]);
        $redeem = Redeem::findOrFail($id);
        if ($redeem->status == RedeemStatusEnum::REDEEMED->value) {
            return response()->json([
                'message' => 'Unable to change the status due to it was already completed',
                'status' => false,
            ], HttpResponse::HTTP_FORBIDDEN);
        }
        $redeem->status = $request->status;
        $redeem->save();

        return RedeemResource::make($redeem->refresh());
    }
}