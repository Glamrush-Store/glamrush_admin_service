<?php

namespace App\Http\Controllers\PaymentTransaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentTransaction\PaymentTransactionResource;
use App\Http\Responses\ApiResponse;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListPaymentTransactionsController extends Controller
{
    public function __invoke(Request $request)
    {
        $sortBy = in_array($request->query('sort_by', 'created_at'), ['created_at', 'amount', 'status', 'type'], true)
            ? $request->query('sort_by', 'created_at')
            : 'created_at';
        $sortDir = strtolower((string) $request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $transactions = PaymentTransaction::query()
            ->with([
                'payment:id,order_id,payment_method_id,provider,reference,provider_reference,transaction_id,amount,currency,status,paid_at,failed_at',
                'payment.order:id,order_number,status,total,currency,created_at',
                'payment.paymentMethod:id,name,code',
            ])
            ->when($request->query('search'), function (Builder $query, string $search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('id', 'like', "%{$search}%")
                        ->orWhere('event_key', 'like', "%{$search}%")
                        ->orWhere('provider_reference', 'like', "%{$search}%")
                        ->orWhereHas('payment', function (Builder $payment) use ($search): void {
                            $payment->where('reference', 'like', "%{$search}%")
                                ->orWhere('provider_reference', 'like', "%{$search}%")
                                ->orWhere('transaction_id', 'like', "%{$search}%")
                                ->orWhereHas('order', fn (Builder $order) => $order->where('order_number', 'like', "%{$search}%"));
                        });
                });
            })
            ->when($request->query('status'), fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->query('type'), fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($request->query('provider'), fn (Builder $query, string $provider) => $query->whereHas('payment', fn (Builder $payment) => $payment->where('provider', $provider)))
            ->when($request->query('date_from'), fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($request->query('date_to'), fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->orderBy($sortBy, $sortDir)
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return ApiResponse::success(PaymentTransactionResource::collection($transactions));
    }
}
