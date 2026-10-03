<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ManualPayment;
use Illuminate\Http\Request;

class ManualPaymentController extends Controller
{
    public function index()
    {
        $payments = ManualPayment::orderBy('id', 'desc')->get();
        return view('backend.manual_payments.index', compact('payments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
        ]);

        ManualPayment::create([
            'name' => $request->name,
            'number' => $request->number,
            'type' => $request->type ?? 'Personal',
            'status' => 1
        ]);

        return back()->with('success', 'Payment Method Added Successfully');
    }

    public function toggle($id)
    {
        $payment = ManualPayment::findOrFail($id);
        $payment->status = $payment->status == 1 ? 0 : 1;
        $payment->save();

        return back()->with('success', 'Status Updated Successfully');
    }

    public function destroy($id)
    {
        ManualPayment::findOrFail($id)->delete();
        return back()->with('success', 'Payment Method Deleted');
    }
}