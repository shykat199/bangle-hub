<?php

namespace App\Services;

class ProfitCalculatorService
{
    public const FORMULA_VERSION = 'v1.1';

    /**
     * BIZ CARE Profit Calculator v1.1
     * funnel → costs → revenue → metrics → planning → health → insights
     */
    public function calculate(array $input): array
    {
        $i = $this->normalize($input);

        // --- 2. Funnel ---
        $confirmed = $i['quantity'] * (1 - $i['call_cancel_percentage'] / 100);
        $cancelled = $i['quantity'] - $confirmed;
        $returns   = $confirmed * ($i['return_percentage'] / 100);
        $sold      = $confirmed - $returns;

        // --- 3. Costs ---
        $totMkt   = $i['marketing_cost'] * $i['quantity'];
        $totCp    = $i['cost_price']     * $confirmed;
        $totShip  = $i['shipping_cost']  * $confirmed;
        $totCost  = $totCp + $totMkt + $totShip;
        $cancelledMktLoss = $cancelled * $i['marketing_cost'];

        // --- 4. Revenue & Profit ---
        $totSell  = $i['selling_price'] * $confirmed;
        $gross    = $totSell - $totCost;
        $retDed   = $returns * $i['selling_price'];
        $extra    = $sold    * $i['extra_shipping_profit'];
        $retLoss  = $returns * $i['per_return_loss'];
        $net      = $gross + $extra - $retDed - $retLoss;

        // --- 5. Derived Metrics ---
        $margin = $this->safeDiv($net, $totSell) * 100;
        $roi    = $this->safeDiv($net, $totCost) * 100;
        $avgPerSold       = $this->safeDiv($net, $sold);
        $costPerOrder     = $this->safeDiv($totCost, $i['quantity']);
        $returnImpact     = $retDed + $retLoss;
        $mktPctRevenue    = $this->safeDiv($totMkt, $totSell) * 100;

        // --- 6. Planning Metric ---
        $confirmRate = 1 - $i['call_cancel_percentage'] / 100;
        $deliverRate = 1 - $i['return_percentage'] / 100;
        $effRate     = $confirmRate * $deliverRate;
        $reqOrders   = ($i['target_delivered_units'] > 0 && $effRate > 0)
            ? (int) ceil($i['target_delivered_units'] / $effRate)
            : 0;

        // --- 7. Health badge ---
        $health = $this->healthStatus($net, $margin, $roi);

        // --- 8. Insights ---
        $insights = $this->insights($net, $i['return_percentage'], $i['call_cancel_percentage'],
            $mktPctRevenue, $margin, $gross, $returnImpact);

        // --- Extra cash-flow fields (for legacy report layout) ---
        $cashLockedInCourier = $sold * $i['selling_price'];
        $requiredAdBudget    = $reqOrders > 0 ? $reqOrders * $i['marketing_cost'] : 0;
        $availableAdBudget   = 0;
        $budgetGap           = $requiredAdBudget - $availableAdBudget;
        $reinvestmentDays    = 0;

        return [
            'inputs' => $i,
            'confirmed_orders'        => $this->cleanNum($confirmed),
            'call_cancelled_units'    => $this->cleanNum($cancelled),
            'return_units'            => $this->cleanNum($returns),
            'sold_units'              => $this->cleanNum($sold),
            'total_marketing_cost'    => $this->cleanNum($totMkt),
            'total_cost_price'        => $this->cleanNum($totCp),
            'total_shipping_cost'     => $this->cleanNum($totShip),
            'total_cost'              => $this->cleanNum($totCost),
            'cancelled_marketing_loss'=> $this->cleanNum($cancelledMktLoss),
            'total_selling'           => $this->cleanNum($totSell),
            'gross_profit'            => $this->cleanNum($gross),
            'return_deduction'        => $this->cleanNum($retDed),
            'extra_shipping_profit_total' => $this->cleanNum($extra),
            'per_return_loss_total'   => $this->cleanNum($retLoss),
            'net_profit'              => $this->cleanNum($net),
            'profit_margin'           => $this->cleanNum($margin),
            'roi'                     => $this->cleanNum($roi),
            'average_profit_per_sold_unit' => $this->cleanNum($avgPerSold),
            'cost_per_order'          => $this->cleanNum($costPerOrder),
            'return_impact_amount'    => $this->cleanNum($returnImpact),
            'marketing_cost_percent_of_revenue' => $this->cleanNum($mktPctRevenue),
            'required_orders'         => $reqOrders,
            'confirm_rate'            => $this->cleanNum($confirmRate * 100),
            'deliver_rate'            => $this->cleanNum($deliverRate * 100),
            'effective_rate'          => $this->cleanNum($effRate * 100),
            'per_product_profit'        => $this->cleanNum($this->safeDiv($net, $sold)),
            'per_product_marketing'     => $this->cleanNum($this->safeDiv($totMkt, $sold)),
            'per_product_purchase'      => $this->cleanNum($i['cost_price']),
            'success_delivery_profit'   => $this->cleanNum($extra),
            'return_courier_loss'       => $this->cleanNum($retLoss),
            'cash_locked_in_courier'  => $this->cleanNum($cashLockedInCourier),
            'required_ad_budget'      => $this->cleanNum($requiredAdBudget),
            'available_ad_budget'     => $this->cleanNum($availableAdBudget),
            'budget_gap'              => $this->cleanNum($budgetGap),
            'reinvestment_capacity_days' => $this->cleanNum($reinvestmentDays),
            'health_status'           => $health,
            'insights'                => $insights,
            'formula_version'         => self::FORMULA_VERSION,
        ];
    }

    private function normalize(array $i): array
    {
        return [
            'cost_price'             => (float) ($i['cost_price'] ?? 0),
            'selling_price'          => (float) ($i['selling_price'] ?? 0),
            'marketing_cost'         => (float) ($i['marketing_cost'] ?? 0),
            'shipping_cost'          => (float) ($i['shipping_cost'] ?? 0),
            'quantity'               => (int)   ($i['quantity'] ?? 0),
            'return_percentage'      => $this->clampPct($i['return_percentage'] ?? 0),
            'extra_shipping_profit'  => (float) ($i['extra_shipping_profit'] ?? 0),
            'per_return_loss'        => (float) ($i['per_return_loss'] ?? 0),
            'call_cancel_percentage' => $this->clampPct($i['call_cancel_percentage'] ?? 0),
            'target_delivered_units' => (int)   ($i['target_delivered_units'] ?? 0),
        ];
    }

    private function clampPct($v): float
    {
        $v = (float) $v;
        if ($v < 0) return 0;
        if ($v > 100) return 100;
        return $v;
    }

    private function safeDiv($a, $b): float
    {
        return $b == 0 ? 0 : ($a / $b);
    }

    private function cleanNum($v): float
    {
        if (!is_finite($v) || is_nan($v)) return 0.0;
        return (float) $v;
    }

    private function healthStatus(float $net, float $margin, float $roi): string
    {
        if ($net <= 0) return 'loss';
        if ($margin >= 25 && $roi >= 40) return 'excellent';
        if ($margin >= 12) return 'good';
        return 'risky';
    }

    private function insights(float $net, float $retPct, float $ccPct,
                              float $mktPct, float $margin, float $gross, float $returnImpact): array
    {
        $out = [];
        if ($net <= 0) $out[] = 'This product is making a net loss. Reconsider pricing or cost structure.';
        if ($retPct >= 15) $out[] = 'High return percentage is eating into your profit.';
        if ($ccPct >= 15) $out[] = 'Call-cancel rate is high — improve confirmation script or ad targeting.';
        if ($mktPct >= 30) $out[] = 'Reducing marketing cost will directly improve net profit.';
        if ($margin > 0 && $margin < 10) $out[] = 'Profit margin is low — optimize costs before scaling up.';
        if ($net > 0 && $margin >= 20) $out[] = 'This product is profitable and ready to scale gradually.';
        if ($gross > 0 && $returnImpact > $gross * 0.3) $out[] = 'Return loss is significant — review product quality and delivery process.';
        if (empty($out)) $out[] = 'Your calculation looks balanced.';
        return $out;
    }

    public static function money($v): string
    {
        $v = (is_finite((float)$v) && !is_nan((float)$v)) ? (float)$v : 0;
        return '৳ ' . self::indianNumber($v, 2);
    }

    public static function moneyTk($v): string
    {
        $v = (is_finite((float)$v) && !is_nan((float)$v)) ? (float)$v : 0;
        return 'Tk ' . self::indianNumber($v, 2);
    }

    public static function percent($v): string
    {
        $v = (is_finite((float)$v) && !is_nan((float)$v)) ? (float)$v : 0;
        return self::indianNumber($v, 2) . '%';
    }

    public static function number($v, int $decimals = 2): string
    {
        $v = (is_finite((float)$v) && !is_nan((float)$v)) ? (float)$v : 0;
        return self::indianNumber($v, $decimals);
    }

    private static function indianNumber(float $v, int $decimals = 2): string
    {
        $negative = $v < 0;
        $v = abs($v);
        $parts = explode('.', number_format($v, $decimals, '.', ''));
        $intPart = $parts[0];
        $decPart = $parts[1] ?? null;

        $len = strlen($intPart);
        if ($len <= 3) {
            $formatted = $intPart;
        } else {
            $last3 = substr($intPart, -3);
            $rest  = substr($intPart, 0, $len - 3);
            $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $formatted = $rest . ',' . $last3;
        }
        if ($decPart !== null) $formatted .= '.' . $decPart;
        return $negative ? '-' . $formatted : $formatted;
    }

    public static function healthLabel(string $status): array
    {
        return match ($status) {
            'excellent' => ['label' => 'Excellent', 'class' => 'success', 'bg' => '#10b981', 'text' => '#fff'],
            'good'      => ['label' => 'Good Zone', 'class' => 'primary', 'bg' => '#22c55e', 'text' => '#fff'],
            'risky'     => ['label' => 'Risky Zone', 'class' => 'warning', 'bg' => '#f59e0b', 'text' => '#fff'],
            default     => ['label' => 'Loss Zone', 'class' => 'danger', 'bg' => '#ef4444', 'text' => '#fff'],
        };
    }
}
