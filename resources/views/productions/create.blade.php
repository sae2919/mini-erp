@extends('layouts.app')
@section('title', 'New Production')
@section('heading', 'Record Production Batch')

@section('header-actions')
    <a href="{{ route('productions.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet"/>
<style>
  :root {
    --surface:        #ffffff;
    --surface2:       #f8f9fb;
    --border:         #e3e6eb;
    --border-soft:    #edf0f4;
    --text-primary:   #1a1d23;
    --text-secondary: #6b7280;
    --text-muted:     #9ca3af;
    --accent:         #5b4dde;
    --accent-light:   #ede9ff;
    --accent-hover:   #4a3cc7;
    --blue:           #3b82f6;
    --green:          #10b981;
    --danger:         #ef4444;
    --shadow-sm:      0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --radius:         12px;
    --radius-sm:      8px;
  }

  /* ── REMOVE SPINNER ARROWS FROM NUMBER INPUTS ── */
  input[type="number"]::-webkit-inner-spin-button,
  input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  input[type="number"] {
    -moz-appearance: textfield;
  }

  /* ── TWO-COLUMN LAYOUT ── */
  .merge-layout {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: 20px;
    align-items: start;
    font-family: 'DM Sans', sans-serif;
  }
  @media (max-width: 1100px) {
    .merge-layout { grid-template-columns: 1fr; }
  }

  /* ── SHARED CARD ── */
  .m-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
  }
  .m-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-soft);
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--surface2);
  }
  .m-card-header-icon {
    width: 34px; height: 34px;
    background: var(--accent-light);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
  }
  .m-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
  }
  .m-card-body { padding: 20px; }

  /* ── ALERT ── */
  .m-alert {
    background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b;
    padding: 11px 14px; border-radius: var(--radius-sm);
    margin-bottom: 18px; font-size: 13px;
  }
  .m-alert ul { margin-top: 5px; padding-left: 16px; }

  /* ── FORM GRID ── */
  .m-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 20px;
  }
  .m-field { display: flex; flex-direction: column; gap: 5px; }
  .m-label {
    font-size: 11px; font-weight: 700; color: var(--text-secondary);
    text-transform: uppercase; letter-spacing: 0.05em;
  }
  .m-required { color: var(--danger); }
  .m-input {
    padding: 9px 12px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px; color: var(--text-primary);
    background: var(--surface); outline: none; width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .m-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(91,77,222,0.12);
  }
  .m-field-error { font-size: 11px; color: var(--danger); margin-top: 2px; }

  /* ── DIVIDER ── */
  .m-divider { border: none; border-top: 1px solid var(--border-soft); margin: 0 0 18px; }

  /* ── SECTION ROW ── */
  .m-section-row {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 12px;
  }
  .m-section-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }

  /* ── ITEMS TABLE (form) ── */
  .m-items-table { width: 100%; border-collapse: collapse; }
  .m-items-table thead th {
    padding: 8px 10px 10px;
    font-size: 11px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: 0.04em;
    text-align: left; border-bottom: 1.5px solid var(--border-soft);
    background: var(--surface2);
  }
  .m-items-table thead th.r { text-align: right; }
  .m-item-row td { padding: 7px 10px; vertical-align: middle; }
  .m-item-row td.r { text-align: right; }

  .m-row-input {
    padding: 8px 10px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px; color: var(--text-primary);
    background: var(--surface); outline: none; width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .m-row-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(91,77,222,0.12);
  }
  .m-row-input.r { text-align: right; }

  .m-subtotal {
    font-size: 13px; font-weight: 600;
    color: var(--blue); font-family: 'DM Mono', monospace;
    white-space: nowrap;
  }

  .m-del {
    width: 27px; height: 27px; border: none;
    background: #fee2e2; color: var(--danger);
    border-radius: 6px; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700; transition: background 0.15s;
  }
  .m-del:hover { background: #fca5a5; }

  /* ── TOTAL ROW ── */
  .m-total-row {
    display: flex; justify-content: flex-end; align-items: center;
    gap: 12px; padding: 13px 10px 0;
    border-top: 1.5px solid var(--border-soft); margin-top: 6px;
  }
  .m-total-label { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
  .m-total-value {
    font-size: 17px; font-weight: 700; color: var(--text-primary);
    font-family: 'DM Mono', monospace; min-width: 80px; text-align: right;
  }

  /* ── FORM ACTIONS ── */
  .m-actions {
    display: flex; gap: 10px; justify-content: flex-end;
    padding-top: 18px; border-top: 1px solid var(--border-soft); margin-top: 18px;
  }
  .m-btn {
    padding: 10px 20px; border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.15s;
    display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none; border: none;
  }
  .m-btn-ghost {
    background: transparent; color: var(--text-secondary);
    border: 1.5px solid var(--border);
  }
  .m-btn-ghost:hover { background: var(--surface2); }
  .m-btn-primary {
    background: var(--accent); color: #fff;
    box-shadow: 0 2px 10px rgba(91,77,222,0.35);
  }
  .m-btn-primary:hover { background: var(--accent-hover); transform: translateY(-1px); }
  .m-btn-add {
    background: var(--accent-light); color: var(--accent);
    border: 1.5px solid transparent; padding: 6px 13px; font-size: 12px;
  }
  .m-btn-add:hover { border-color: var(--accent); }

  /* ── REPORT FILTERS ── */
  .r-filters {
    padding: 13px 20px; background: var(--surface2);
    border-bottom: 1px solid var(--border-soft);
    display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;
  }
  .r-filter-group { display: flex; flex-direction: column; gap: 5px; }
  .r-filter-group label {
    font-size: 11px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: 0.04em;
  }
  .r-filter-group input,
  .r-filter-group select {
    padding: 7px 11px; min-width: 126px;
    border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif; font-size: 13px;
    color: var(--text-primary); background: var(--surface); outline: none;
    transition: border-color 0.15s;
  }
  .r-filter-group input:focus,
  .r-filter-group select:focus { border-color: var(--accent); }
  .r-btn-generate {
    padding: 8px 18px; background: var(--accent); color: #fff;
    border: none; border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600;
    cursor: pointer; align-self: flex-end;
    box-shadow: 0 2px 8px rgba(91,77,222,0.28); transition: background 0.15s;
  }
  .r-btn-generate:hover { background: var(--accent-hover); }

  /* ── REPORT TABLE ── */
  .r-table-wrap { overflow-x: auto; }
  .r-table { width: 100%; border-collapse: collapse; font-family: 'DM Sans', sans-serif; }
  .r-table thead th {
    padding: 10px 14px; text-align: left;
    font-size: 11px; font-weight: 700; color: var(--text-secondary);
    text-transform: uppercase; letter-spacing: 0.04em;
    white-space: nowrap; background: var(--surface2);
    border-bottom: 1.5px solid var(--border);
  }
  .r-table thead th.num   { text-align: right; }
  .r-table thead th.col-p { background: #eff6ff; }
  .r-table thead th.col-d { background: #eff6ff; }
  .r-table thead th.col-s { background: #ecfdf5; }

  .r-table tbody tr { border-bottom: 1px solid var(--border-soft); transition: background 0.1s; }
  .r-table tbody tr:last-child { border-bottom: none; }
  .r-table tbody tr:hover { background: #fafbfc; }
  .r-table td { padding: 10px 14px; vertical-align: middle; font-size: 13px; }
  .r-table td.num { text-align: right; font-family: 'DM Mono', monospace; }

  .r-name { font-weight: 600; color: var(--text-primary); font-size: 13px; }
  .r-sku  { font-size: 11px; color: var(--text-muted); font-family: 'DM Mono', monospace; margin-top: 1px; }

  .r-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 500; }
  .r-badge-Electronics { background: #eff6ff; color: #1d4ed8; }
  .r-badge-Clothing    { background: #fdf4ff; color: #7e22ce; }
  .r-badge-Stationery  { background: #fff7ed; color: #c2410c; }
  .r-badge-Food        { background: #f0fdf4; color: #166534; }

  .r-dispatched { color: var(--blue);  font-weight: 600; }
  .r-sold       { color: var(--green); font-weight: 600; }
  .r-total      { font-weight: 700;    color: var(--text-primary); }

  .r-table tfoot td {
    padding: 11px 14px; font-weight: 700;
    background: var(--surface2); border-top: 1.5px solid var(--border);
    font-family: 'DM Mono', monospace; font-size: 13px;
  }
  .r-table tfoot td:first-child,
  .r-table tfoot td:nth-child(2) { font-family: 'DM Sans', sans-serif; }
</style>
@endpush

@section('content')
<div class="merge-layout">

  {{-- ══════════════════════════════════════
       LEFT — RECORD PRODUCTION BATCH FORM
  ══════════════════════════════════════ --}}
  <div class="m-card">
    <div class="m-card-header">
      <div class="m-card-header-icon">🏭</div>
      <span class="m-card-title">Record Production Batch</span>
    </div>
    <div class="m-card-body">

      @if($errors->any())
        <div class="m-alert">
          <strong>Please fix the following:</strong>
          <ul>
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('productions.store') }}" id="prod-form">
        @csrf

        <div class="m-form-grid">
          <div class="m-field">
            <label class="m-label">Production Date <span class="m-required">*</span></label>
            <input type="date" name="production_date" class="m-input"
              value="{{ old('production_date', date('Y-m-d')) }}"
              max="{{ date('Y-m-d') }}" required />
            @error('production_date')
              <span class="m-field-error">{{ $message }}</span>
            @enderror
          </div>
          <div class="m-field">
            <label class="m-label">Notes</label>
            <input type="text" name="notes" class="m-input"
              value="{{ old('notes') }}" placeholder="Batch notes..." />
            @error('notes')
              <span class="m-field-error">{{ $message }}</span>
            @enderror
          </div>
        </div>

        <hr class="m-divider" />

        <div class="m-section-row">
          <span class="m-section-title">Products Manufactured</span>
          <button type="button" id="add-row" class="m-btn m-btn-add">+ Add Product</button>
        </div>

        <table class="m-items-table">
          <thead>
            <tr>
              <th style="width:38%">Product</th>
<th class="r" style="width:16%">Qty</th>
<th class="r" style="width:20%">Unit Cost (₹)</th>
<th class="r" style="width:18%">Subtotal</th>
<th style="width:8%"></th>
            </tr>
          </thead>
          <tbody id="items-body">
            @if(old('items'))
              @foreach(old('items') as $i => $item)
                <tr class="m-item-row">
                  <td>
                    <select name="items[{{ $i }}][product_id]" class="m-row-input m-sel" required>
                      <option value="">-- Search product --</option>
                      @foreach($products as $p)
                        <option value="{{ $p->id }}" data-cost="{{ $p->production_cost }}"
                          {{ old("items.{$i}.product_id") == $p->id ? 'selected' : '' }}>
                          {{ $p->name }} ({{ $p->sku }})
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td class="r">
                    <input type="number" name="items[{{ $i }}][quantity]"
                      class="m-row-input r" value="{{ $item['quantity'] ?? 1 }}" min="1" required />
                  </td>
                  <td class="r">
                    <input type="number" name="items[{{ $i }}][unit_cost]"
                      class="m-row-input r" value="{{ $item['unit_cost'] ?? '' }}" step="0.01" min="0" required />
                  </td>
                  <td class="r"><span class="m-subtotal">₹0.00</span></td>
                  <td><button type="button" class="m-del">×</button></td>
                </tr>
              @endforeach
            @endif
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3">
                <div class="m-total-row">
                  <span class="m-total-label">Total Cost:</span>
                  <span class="m-total-value" id="grand-total">₹0.00</span>
                </div>
              </td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>

        <div class="m-actions">
          <a href="{{ route('productions.index') }}" class="m-btn m-btn-ghost">Cancel</a>
          <button type="submit" class="m-btn m-btn-primary">🏭 Record Production</button>
        </div>
      </form>

    </div>
  </div>

  {{-- ══════════════════════════════════════
       RIGHT — STOCK MOVEMENT REPORT
  ══════════════════════════════════════ --}}
  <div class="m-card">
    <div class="m-card-header">
      <div class="m-card-header-icon">📈</div>
      <span class="m-card-title">Stock Movement Report</span>
    </div>

    <div class="r-filters">
      <div class="r-filter-group">
        <label>From</label>
        <input type="date" id="fromDate" value="{{ now()->startOfMonth()->format('Y-m-d') }}" />
      </div>
      <div class="r-filter-group">
        <label>To</label>
        <input type="date" id="toDate" value="{{ now()->format('Y-m-d') }}" />
      </div>
      <div class="r-filter-group">
        <label>Category</label>
        <select id="catFilter" onchange="filterReport()">
          <option value="all">All Categories</option>
          @foreach($report->pluck('category_name')->unique()->filter()->sort() as $cat)
            <option value="{{ $cat }}">{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <button class="r-btn-generate" onclick="filterReport()">Generate</button>
    </div>

    <div class="r-table-wrap">
      <table class="r-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th class="num col-p">📊 Produced</th>
            <th class="num col-d">🚚 Dispatched</th>
            <th class="num col-s">🔥 Sold</th>
            <th class="num">🏭 Warehouse</th>
            <th class="num">📦 With Sellers</th>
            <th class="num">Total Stock</th>
          </tr>
        </thead>
        <tbody id="reportBody">
          @forelse($report as $row)
            @php $slug = preg_replace('/[^A-Za-z]/', '', $row->category_name ?? ''); @endphp
            <tr data-category="{{ $row->category_name }}">
              <td>
                <div class="r-name">{{ $row->product_name }}</div>
                @if(!empty($row->product_sku))
                  <div class="r-sku">{{ $row->product_sku }}</div>
                @endif
              </td>
              <td>
                <span class="r-badge r-badge-{{ $slug }}">{{ $row->category_name }}</span>
              </td>
              <td class="num">{{ number_format($row->produced) }}</td>
              <td class="num r-dispatched">{{ number_format($row->dispatched) }}</td>
              <td class="num r-sold">{{ number_format($row->sold) }}</td>
              <td class="num">{{ number_format($row->warehouse) }}</td>
              <td class="num">{{ number_format($row->with_sellers) }}</td>
              <td class="num r-total">{{ number_format($row->total_stock) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px">
                No stock data available
              </td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr>
            <td>Total</td><td></td>
            <td class="num">{{ number_format($report->sum('produced')) }}</td>
            <td class="num r-dispatched">{{ number_format($report->sum('dispatched')) }}</td>
            <td class="num r-sold">{{ number_format($report->sum('sold')) }}</td>
            <td class="num">{{ number_format($report->sum('warehouse')) }}</td>
            <td class="num">{{ number_format($report->sum('with_sellers')) }}</td>
            <td class="num r-total">{{ number_format($report->sum('total_stock')) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
const PRODUCTS = @json($productsJs);

let idx = {{ old('items') ? count(old('items')) : 0 }};

function initTomSelect(selectEl) {
    new TomSelect(selectEl, {
        placeholder: '-- Search product --',
        searchField: ['text'],
        maxOptions:  200,
        create:      false,
        onChange(val) {
            const row  = selectEl.closest('.m-item-row');
            const cost = row.querySelector('input[name*="unit_cost"]');
            const opt  = selectEl.options[selectEl.selectedIndex];
            if (cost && !cost.value && opt && opt.dataset.cost) {
                cost.value = opt.dataset.cost;
            }
            calcRowEl(row);
        }
    });
}

function calcRowEl(row) {
    const qty  = row.querySelector('input[name*="quantity"]');
    const cost = row.querySelector('input[name*="unit_cost"]');
    const sub  = row.querySelector('.m-subtotal');
    if (!qty || !cost || !sub) return;
    const v = (parseFloat(qty.value) || 0) * (parseFloat(cost.value) || 0);
    sub.textContent = '₹' + v.toFixed(2);
    calcTotal();
}

function addRow() {
    const i    = idx++;
    const opts = PRODUCTS.map(p =>
        `<option value="${p.id}" data-cost="${p.cost}">${p.name}</option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.className = 'm-item-row';
    tr.innerHTML = `
        <td>
            <select name="items[${i}][product_id]" class="m-row-input m-sel" required>
                <option value="">-- Search product --</option>${opts}
            </select>
        </td>
        <td class="r">
            <input type="number" name="items[${i}][quantity]"
                class="m-row-input r" min="1" value="1" required />
        </td>
        <td class="r">
            <input type="number" name="items[${i}][unit_cost]"
                class="m-row-input r" step="0.01" min="0" placeholder="0.00" required />
        </td>
        <td class="r"><span class="m-subtotal">₹0.00</span></td>
        <td><button type="button" class="m-del">×</button></td>
    `;

    document.getElementById('items-body').appendChild(tr);

    const sel  = tr.querySelector('.m-sel');
    const qty  = tr.querySelector('input[name*="quantity"]');
    const cost = tr.querySelector('input[name*="unit_cost"]');

    initTomSelect(sel);

    qty.addEventListener('input',  () => calcRowEl(tr));
    cost.addEventListener('input', () => calcRowEl(tr));
    tr.querySelector('.m-del').addEventListener('click', () => {
        tr.remove(); calcTotal();
    });
}

function calcTotal() {
    let t = 0;
    document.querySelectorAll('.m-subtotal').forEach(s => {
        t += parseFloat(s.textContent.replace('₹', '')) || 0;
    });
    document.getElementById('grand-total').textContent = '₹' + t.toFixed(2);
}

function filterReport() {
    const cat = document.getElementById('catFilter').value;
    document.querySelectorAll('#reportBody tr').forEach(tr => {
        tr.style.display = (cat === 'all' || tr.dataset.category === cat) ? '' : 'none';
    });
}

document.getElementById('add-row').addEventListener('click', addRow);

document.getElementById('prod-form').addEventListener('submit', e => {
    if (!document.querySelectorAll('.m-item-row').length) {
        e.preventDefault();
        alert('Add at least one product.');
    }
});

document.querySelectorAll('.m-sel').forEach(initTomSelect);

@if(!old('items')) addRow(); @else calcTotal(); @endif
</script>
@endpush