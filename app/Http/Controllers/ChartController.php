<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    public function dailyGuestPerMonth()
    {
        $currentDate = Carbon::now();
        $daysInMonth = $currentDate->daysInMonth;

        $days = collect(range(1, $daysInMonth));
        $guests = $days
            ->map(function ($day) use ($currentDate) {
                return $this->dailyTotalDownPaymentGuests($currentDate->year, $currentDate->month, $day);
            })
            ->toArray();

        $max = (int) ceil((max($guests) + 10) / 10) * 10;

        return [
            'day' => $days->toArray(),
            'guest_count_data' => $guests,
            'max' => $max,
        ];
    }

    public function dailyGuest(Request $request)
    {
        $date = Carbon::createFromDate(
            year: $request->year,
            month: $request->month,
            day: $request->day
        );

        // Get transactions that were active on this day
        $transactions = Transaction::where('check_in', '<=', $date)
            ->where('check_out', '>=', $date)
            ->get();
            
        // Filter transactions to only include those with down payments
        $transactionsWithDownPayment = $transactions->filter(function($transaction) {
            return Payment::where('transaction_id', $transaction->id)
                ->where('status', 'Down Payment')
                ->exists();
        });

        return view('dashboard.chart_detail', [
            'transactions' => $transactionsWithDownPayment,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    private function dailyTotalGuests($year, $month, $day)
    {
        $date = Carbon::createFromDate($year, $month, $day);

        return Transaction::where('check_in', '<=', $date)
            ->where('check_out', '>=', $date)
            ->count();
    }

    private function dailyTotalDownPaymentGuests($year, $month, $day)
    {
        $date = Carbon::createFromDate($year, $month, $day);
        
        // Get transactions that were active on this day
        $transactions = Transaction::where('check_in', '<=', $date)
            ->where('check_out', '>=', $date)
            ->get();
            
        // Count transactions that have down payments
        $downPaymentCount = 0;
        foreach ($transactions as $transaction) {
            // Check if this transaction has a down payment
            $hasDownPayment = Payment::where('transaction_id', $transaction->id)
                ->where('status', 'Down Payment')
                ->exists();
                
            if ($hasDownPayment) {
                $downPaymentCount++;
            }
        }
        
        return $downPaymentCount;
    }
}
