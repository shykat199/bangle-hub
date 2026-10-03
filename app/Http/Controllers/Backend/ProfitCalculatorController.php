<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProfitCalculation;
use App\Services\ProfitCalculatorService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfitCalculatorController extends Controller
{
    protected ProfitCalculatorService $svc;

    public function __construct(ProfitCalculatorService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * Main calculator view. ?id=N to load a saved calc.
     */
    public function index(Request $request)
    {
        $editing = null;
        if ($request->filled('id')) {
            $editing = ProfitCalculation::where('user_id', auth()->id())->find($request->id);
        }

        // Defaults: null for blank inputs (render empty placeholder), record values for edits.
        $defaults = [
            'product_name'           => $editing->product_name ?? '',
            'product_category'       => $editing->product_category ?? '',
            'note'                   => $editing->note ?? '',
            'cost_price'             => $editing ? $editing->cost_price : null,
            'selling_price'          => $editing ? $editing->selling_price : null,
            'marketing_cost'         => $editing ? $editing->marketing_cost : null,
            'shipping_cost'          => $editing ? $editing->shipping_cost : null,
            'extra_shipping_profit'  => $editing ? $editing->extra_shipping_profit : null,
            'per_return_loss'        => $editing ? $editing->per_return_loss : null,
            'quantity'               => $editing ? $editing->quantity : null,
            'return_percentage'      => $editing ? $editing->return_percentage : null,
            'call_cancel_percentage' => $editing ? $editing->call_cancel_percentage : null,
            'target_delivered_units' => $editing ? $editing->target_delivered_units : null,
        ];

        // Calculate with coerced numerics so the service doesn't choke on nulls.
        $coerce = fn($v) => ($v === null || $v === '') ? 0 : $v;
        $result = $this->svc->calculate([
            'cost_price'             => $coerce($defaults['cost_price']),
            'selling_price'          => $coerce($defaults['selling_price']),
            'marketing_cost'         => $coerce($defaults['marketing_cost']),
            'shipping_cost'          => $coerce($defaults['shipping_cost']),
            'extra_shipping_profit'  => $coerce($defaults['extra_shipping_profit']),
            'per_return_loss'        => $coerce($defaults['per_return_loss']),
            'quantity'               => $coerce($defaults['quantity']),
            'return_percentage'      => $coerce($defaults['return_percentage']),
            'call_cancel_percentage' => $coerce($defaults['call_cancel_percentage']),
            'target_delivered_units' => $coerce($defaults['target_delivered_units']),
        ]);

        return view('backend.profit_calculator.calculator', [
            'editing'  => $editing,
            'defaults' => $defaults,
            'result'   => $result,
        ]);
    }

    /**
     * AJAX live calculation — returns computed metrics for current inputs.
     */
    public function liveCalc(Request $request)
    {
        $result = $this->svc->calculate($request->all());
        return response()->json($result);
    }

    /**
     * Save / update a calculation row.
     */
    public function save(Request $request)
    {
        $isAuto = $request->boolean('auto');

        // For auto-save we accept softer rules; the client gates whether to call us.
        $data = $request->validate([
            'id'                     => 'nullable|integer',
            'auto'                   => 'nullable|boolean',
            'product_name'           => 'required|string|max:191',
            'product_category'       => 'nullable|string|max:191',
            'note'                   => 'nullable|string|max:2000',
            'cost_price'             => 'required|numeric|min:0',
            'selling_price'          => 'required|numeric|min:0',
            'marketing_cost'         => 'nullable|numeric|min:0',
            'shipping_cost'          => 'nullable|numeric|min:0',
            'extra_shipping_profit'  => 'nullable|numeric',
            'per_return_loss'        => 'nullable|numeric',
            'quantity'               => 'required|integer|min:1',
            'return_percentage'      => 'nullable|numeric|min:0|max:100',
            'call_cancel_percentage' => 'nullable|numeric|min:0|max:100',
            'target_delivered_units' => 'nullable|integer|min:0',
        ]);

        unset($data['auto']);

        // Coerce nullable numerics to 0 so DECIMAL/INT columns don't reject blanks.
        $num = fn($v) => ($v === null || $v === '') ? 0 : (float) $v;
        $int = fn($v) => ($v === null || $v === '') ? 0 : (int) $v;
        $data['marketing_cost']         = $num($data['marketing_cost']         ?? null);
        $data['shipping_cost']          = $num($data['shipping_cost']          ?? null);
        $data['extra_shipping_profit']  = $num($data['extra_shipping_profit']  ?? null);
        $data['per_return_loss']        = $num($data['per_return_loss']        ?? null);
        $data['return_percentage']      = $num($data['return_percentage']      ?? null);
        $data['call_cancel_percentage'] = $num($data['call_cancel_percentage'] ?? null);
        $data['target_delivered_units'] = $int($data['target_delivered_units'] ?? null);

        $result = $this->svc->calculate($data);

        $payload = array_merge($data, [
            'user_id'         => auth()->id(),
            'net_profit'      => $result['net_profit'],
            'gross_profit'    => $result['gross_profit'],
            'total_selling'   => $result['total_selling'],
            'total_cost'      => $result['total_cost'],
            'profit_margin'   => $result['profit_margin'],
            'roi'             => $result['roi'],
            'sold_units'      => $result['sold_units'],
            'return_units'    => $result['return_units'],
            'health_status'   => $result['health_status'],
            'formula_version' => $result['formula_version'],
        ]);

        if (!empty($data['id'])) {
            $rec = ProfitCalculation::where('user_id', auth()->id())->findOrFail($data['id']);
            $rec->update($payload);
        } else {
            $rec = ProfitCalculation::create($payload);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'msg'      => $isAuto ? 'Auto-saved' : 'Calculation saved!',
                'id'       => $rec->id,
                'edit_url' => route('admin.profit_calculator.index', ['id' => $rec->id]),
                'auto'     => $isAuto,
            ]);
        }

        return redirect()
            ->route('admin.profit_calculator.index', ['id' => $rec->id])
            ->with('success', 'Calculation saved!');
    }

    /**
     * History page.
     */
    public function history(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $search = trim((string) $request->input('search', ''));

        $query = ProfitCalculation::where('user_id', auth()->id());

        if ($search !== '') {
            $query->where('product_name', 'like', "%{$search}%");
        }

        if ($filter === 'profitable') {
            $query->where('net_profit', '>', 0);
        } elseif ($filter === 'loss') {
            $query->where('net_profit', '<=', 0);
        } elseif ($filter === 'favorites') {
            $query->where('is_favorite', true);
        }

        $items = $query->orderByDesc('created_at')->paginate(12)->appends($request->all());

        return view('backend.profit_calculator.history', compact('items', 'filter', 'search'));
    }

    /**
     * Toggle favorite (AJAX).
     */
    public function toggleFavorite(int $id)
    {
        $rec = ProfitCalculation::where('user_id', auth()->id())->findOrFail($id);
        $rec->is_favorite = !$rec->is_favorite;
        $rec->save();
        return response()->json(['success' => true, 'is_favorite' => $rec->is_favorite]);
    }

    /**
     * Delete a calculation.
     */
    public function destroy(int $id, Request $request)
    {
        $rec = ProfitCalculation::where('user_id', auth()->id())->findOrFail($id);
        $rec->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'msg' => 'Deleted!']);
        }

        return redirect()->route('admin.profit_calculator.history')->with('success', 'Deleted!');
    }

    /**
     * Compare page.
     */
    public function compare(Request $request)
    {
        $selected = (array) $request->input('ids', []);
        $selected = array_slice(array_filter(array_map('intval', $selected)), 0, 4);

        $all = ProfitCalculation::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $picked = $all->whereIn('id', $selected)->values();

        return view('backend.profit_calculator.compare', compact('all', 'picked', 'selected'));
    }

    /**
     * Print / PDF view.
     */
    public function printPdf(int $id)
    {
        $rec = ProfitCalculation::where('user_id', auth()->id())->findOrFail($id);
        $result = $this->svc->calculate($rec->inputsArray());

        return view('backend.profit_calculator.print', compact('rec', 'result'));
    }

    /**
     * Export all calcs as CSV.
     */
    public function exportCsvAll(): StreamedResponse
    {
        $rows = ProfitCalculation::where('user_id', auth()->id())
            ->orderByDesc('created_at')->get();

        return $this->streamCsv('profit-calculations-' . date('Y-m-d') . '.csv', $rows);
    }

    /**
     * Export a single calc as CSV.
     */
    public function exportCsvOne(int $id): StreamedResponse
    {
        $rec = ProfitCalculation::where('user_id', auth()->id())->findOrFail($id);
        return $this->streamCsv(
            'profit-' . preg_replace('/[^a-z0-9-]+/i', '-', $rec->product_name) . '-' . $rec->id . '.csv',
            collect([$rec])
        );
    }

    private function streamCsv(string $filename, $rows): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $cols = [
            'id','product_name','product_category','cost_price','selling_price',
            'marketing_cost','shipping_cost','extra_shipping_profit','per_return_loss',
            'quantity','call_cancel_percentage','return_percentage','target_delivered_units',
            'sold_units','return_units','total_selling','total_cost',
            'gross_profit','net_profit','profit_margin','roi',
            'health_status','formula_version','created_at',
        ];

        return response()->stream(function () use ($rows, $cols) {
            $fh = fopen('php://output', 'w');
            fwrite($fh, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($fh, $cols);
            foreach ($rows as $r) {
                $line = [];
                foreach ($cols as $c) $line[] = $r->{$c};
                fputcsv($fh, $line);
            }
            fclose($fh);
        }, 200, $headers);
    }
}
