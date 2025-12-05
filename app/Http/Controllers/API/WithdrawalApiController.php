<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Withdrawal;
use App\Models\SalesExecutive;
use Illuminate\Support\Facades\Auth;

class WithdrawalApiController extends Controller
{
    public function dashboard(Request $request)
    {
        $sales = $request->user();

        if (!$sales instanceof SalesExecutive) {
            return response()->json([
                'status' => false,
                'message' => 'Only Sales Executives can access this.'
            ], 403);
        }

        $salesExecutiveId = $sales->id;

        $totalStudents = Student::where('added_by', $salesExecutiveId)->count();
        $incomePerTarget = $sales->income_per_target ?? 0;
        $totalEarning = $incomePerTarget * $totalStudents;

        $totalWithdrawn = Withdrawal::where('sales_executive_id', $salesExecutiveId)
            ->whereIn('status', ['approved', 'completed'])
            ->sum('amount');

        $availableBalance = $totalEarning - $totalWithdrawn;

        $withdrawals = Withdrawal::where('sales_executive_id', $salesExecutiveId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal dashboard loaded successfully',
            'data' => [
                'total_earning'     => $totalEarning,
                'total_withdrawn'   => $totalWithdrawn,
                'available_balance' => $availableBalance,
                'min_withdraw'      => 50,
                'can_withdraw'      => $totalEarning >= 50,
                'withdrawals'       => $withdrawals
            ]
        ]);
    }
    public function requestWithdraw(Request $request)
    {
        $sales = $request->user();
        $salesExecutiveId = $sales->id;

        $totalStudents = Student::where('added_by', $salesExecutiveId)->count();
        $incomePerTarget = $sales->income_per_target ?? 0;
        $totalEarning = $incomePerTarget * $totalStudents;

        $totalWithdrawn = Withdrawal::where('sales_executive_id', $salesExecutiveId)
            ->whereIn('status', ['approved', 'completed'])
            ->sum('amount');

        $availableBalance = $totalEarning - $totalWithdrawn;

        if ($totalEarning < 50) {
            return response()->json([
                'status' => false,
                'message' => 'You must have at least ₹50 to request withdrawal.'
            ], 403);
        }

        $pending = Withdrawal::where('sales_executive_id', $salesExecutiveId)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return response()->json([
                'status' => false,
                'message' => 'You already have a pending withdrawal request.'
            ], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $availableBalance,
            'payment_method' => 'required|string|in:bank_transfer,upi',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validated['payment_method'] == 'bank_transfer') {
            if (
                empty($sales->bank_name) ||
                empty($sales->account_number) ||
                empty($sales->ifsc_code) ||
                empty($sales->bank_branch)
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please add your bank details before requesting a bank transfer withdrawal.'
                ], 403);
            }
        }

        if ($validated['payment_method'] == 'upi') {
            if (empty($sales->upi_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please add your UPI ID before requesting a UPI withdrawal.'
                ], 403);
            }
        }

        Withdrawal::create([
            'sales_executive_id' => $salesExecutiveId,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal request submitted successfully.',
        ], 201);
    }
}
