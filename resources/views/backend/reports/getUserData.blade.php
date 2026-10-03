@php
    // Status icon + color map
    $statusMeta = [
        'pending'           => ['icon' => 'uil-clock-eight',       'color' => 'warning'],
        'incomplete'        => ['icon' => 'uil-exclamation-circle','color' => 'info'],
        'on hold'           => ['icon' => 'uil-pause',             'color' => 'purple'],
        'scheduled'         => ['icon' => 'uil-calendar-alt',      'color' => 'pink'],
        'confirmed'         => ['icon' => 'uil-check',             'color' => 'info'],
        'processing'        => ['icon' => 'uil-setting',           'color' => 'warning'],
        'courier complete'  => ['icon' => 'uil-truck-loading',     'color' => 'info'],
        'shipped'           => ['icon' => 'uil-truck',             'color' => 'primary'],
        'delivered'         => ['icon' => 'uil-check-circle',      'color' => 'success'],
        'cancelled'         => ['icon' => 'uil-times-circle',      'color' => 'danger'],
        'returning'         => ['icon' => 'uil-corner-up-left',    'color' => 'danger'],
        'return received'   => ['icon' => 'uil-archive',           'color' => 'danger'],
        'return missing'    => ['icon' => 'uil-question-circle',   'color' => 'dark'],
        'complete'          => ['icon' => 'uil-check-circle',      'color' => 'success'],
        'completed'         => ['icon' => 'uil-check-circle',      'color' => 'success'],
        'courier'           => ['icon' => 'uil-truck-loading',     'color' => 'info'],
    ];
@endphp

<style>
    /* ============ STAFF PERFORMANCE GRID ============ */
    .perf-grid {
        display: grid;
        grid-template-columns: 1fr; /* এক রো-তে একটাই কার্ড, full width */
        gap: 18px;
    }

    .staff-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
    }
    .staff-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }

    /* TOP PERFORMER */
    .top-card {
        border: 2px solid #f59e0b;
        background: linear-gradient(180deg, #fffbeb 0%, #ffffff 60%);
    }
    .top-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, #f59e0b, #ef4444);
    }
    .badge-top {
        background: linear-gradient(135deg, #f59e0b, #ef4444);
        color: #fff !important;
        font-size: 10.5px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 10px;
        box-shadow: 0 4px 8px rgba(245, 158, 11, 0.25);
        letter-spacing: 0.3px;
    }

    /* STAFF HEADER */
    .staff-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 1px dashed #e2e8f0;
        padding-bottom: 12px;
        margin-bottom: 14px;
    }
    .staff-name {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        letter-spacing: -0.2px;
    }
    .staff-name small {
        color: #64748b;
        font-size: 11.5px;
        font-weight: 500;
        display: block;
        margin-top: 2px;
    }
    .staff-total {
        font-size: 12.5px;
        font-weight: 800;
        color: #2563eb;
        background: #eff6ff;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #bfdbfe;
        white-space: nowrap;
    }

    /* KPI ROW */
    .kpi-row {
        display: flex;
        justify-content: space-between;
        background: #f8fafc;
        padding: 10px 14px;
        border-radius: 10px;
        margin-bottom: 14px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #f1f5f9;
    }
    .kpi-row i { font-size: 14px; vertical-align: -1px; }

    /* ============ STATUS GRID (7 × 2) ============ */
    .status-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }
    @media (max-width: 768px) { .status-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 480px) { .status-grid { grid-template-columns: repeat(3, 1fr); } }

    .status-cell {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 6px;
        text-align: center;
        transition: all 0.2s;
        position: relative;
        cursor: default;
    }
    .status-cell:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
    }
    .top-card .status-cell { background: #fffdf5; border-color: #fde68a; }
    .status-cell .s-icon { font-size: 16px; display: block; margin-bottom: 4px; line-height: 1; }
    .status-cell .s-label {
        font-size: 10px; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: 0.3px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        line-height: 1.1; margin-bottom: 3px;
    }
    .status-cell .s-count {
        font-size: 16px; font-weight: 800; color: #0f172a; line-height: 1;
    }
    .status-cell.is-empty .s-count { color: #cbd5e1; font-weight: 600; }
    .status-cell.is-empty .s-icon,
    .status-cell.is-empty .s-label { opacity: 0.5; }

    /* Status color modifiers (icon) */
    .sc-primary .s-icon { color: #3b82f6; }
    .sc-success .s-icon { color: #10b981; }
    .sc-warning .s-icon { color: #f59e0b; }
    .sc-danger  .s-icon { color: #ef4444; }
    .sc-info    .s-icon { color: #0ea5e9; }
    .sc-purple  .s-icon { color: #8b5cf6; }
    .sc-pink    .s-icon { color: #ec4899; }
    .sc-dark    .s-icon { color: #0f172a; }
    .sc-secondary .s-icon { color: #64748b; }

    /* Active accent (when count > 0) */
    .status-cell.has-count.sc-primary { border-color: rgba(59,130,246,0.35); background: rgba(59,130,246,0.06); }
    .status-cell.has-count.sc-success { border-color: rgba(16,185,129,0.35); background: rgba(16,185,129,0.06); }
    .status-cell.has-count.sc-warning { border-color: rgba(245,158,11,0.35); background: rgba(245,158,11,0.06); }
    .status-cell.has-count.sc-danger  { border-color: rgba(239,68,68,0.35);  background: rgba(239,68,68,0.06); }
    .status-cell.has-count.sc-info    { border-color: rgba(14,165,233,0.35); background: rgba(14,165,233,0.06); }
    .status-cell.has-count.sc-purple  { border-color: rgba(139,92,246,0.35); background: rgba(139,92,246,0.06); }
    .status-cell.has-count.sc-pink    { border-color: rgba(236,72,153,0.35); background: rgba(236,72,153,0.06); }
    .status-cell.has-count.sc-dark    { border-color: rgba(15,23,42,0.25);   background: rgba(15,23,42,0.05); }

    /* EMPTY STATE */
    .perf-empty {
        text-align: center; padding: 60px 20px;
        background: #fff; border-radius: 14px;
        border: 1px dashed #cbd5e1; color: #94a3b8;
    }
    .perf-empty i { font-size: 56px; color: #cbd5e1; }
    .perf-empty p { margin-top: 12px; font-size: 1rem; font-weight: 500; }
</style>

@if($items->count() > 0)
    <div class="perf-grid">
        @foreach($items as $index => $item)
            @php
                $isTopPerformer = ($index === 0 && $items->currentPage() == 1 && $item->total_orders > 0);
                $total       = $item->total_orders ?: 1;
                $successRate = round((($item->kpi_delivered ?? 0) / $total) * 100, 1);
                $failedRate  = round((($item->kpi_failed   ?? 0) / $total) * 100, 1);
            @endphp

            <div class="staff-card {{ $isTopPerformer ? 'top-card' : '' }}">

                @if($isTopPerformer)
                    <div class="badge-top"><i class="mdi mdi-trophy"></i> Top Performer</div>
                @endif

                <div class="staff-header">
                    <div class="staff-name">
                        {{ $item->assign_user_name ?? 'Unassigned / Admin' }}
                        @if(!empty($item->last_name))
                            <small>{{ $item->last_name }}</small>
                        @endif
                    </div>
                    <div class="staff-total">
                        Total: {{ $item->total_orders }}
                    </div>
                </div>

                <div class="kpi-row">
                    <span class="text-success"><i class="mdi mdi-check-circle"></i> Success: {{ $successRate }}%</span>
                    <span class="text-danger"><i class="mdi mdi-close-circle"></i> Failed: {{ $failedRate }}%</span>
                </div>

                {{-- STATUS GRID (7 × 2) --}}
                <div class="status-grid">
                    @foreach(getOrderStatus() as $key => $label)
                        @php
                            $colName  = 'status_' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($key));
                            $count    = $item->{$colName} ?? 0;
                            $lowerKey = strtolower($key);
                            $meta     = $statusMeta[$lowerKey] ?? ['icon' => 'uil-circle', 'color' => 'secondary'];

                            $shortLabel = trim(str_ireplace('order', '', $label));
                            $shortLabel = str_ireplace(['Received', 'Missing', 'Complete'], ['Rec.', 'Miss.', 'C.'], $shortLabel);
                        @endphp

                        <div class="status-cell sc-{{ $meta['color'] }} {{ $count > 0 ? 'has-count' : 'is-empty' }}"
                             title="{{ $label }}: {{ $count }}">
                            <i class="uil {{ $meta['icon'] }} s-icon"></i>
                            <div class="s-label">{{ $shortLabel }}</div>
                            <div class="s-count">{{ $count > 0 ? $count : '–' }}</div>
                        </div>
                    @endforeach
                </div>

            </div>
        @endforeach
    </div>
@else
    <div class="perf-empty">
        <i class="mdi mdi-inbox-outline"></i>
        <p>No data found</p>
    </div>
@endif

{{-- Pagination --}}
@if($items->hasPages())
    <div class="mt-4 d-flex justify-content-end">
        {!! $items->appends(Request::all())->links() !!}
    </div>
@endif