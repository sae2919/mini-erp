@extends('layouts.app')
@section('title', 'Request Stock')
@section('heading', 'Request Stock')

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
    --danger:         #ef4444;
    --shadow-sm:      0 1px 3px rgba(0,0,0,0.06);
    --radius:         12px;
    --radius-sm:      8px;
  }

  /* Remove spinners from number inputs */
  input[type="number"]::-webkit-inner-spin-button,
  input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
  input[type="number"] { -moz-appearance: textfield; }

  .sr-wrap { max-width: 700px; font-family: 'DM Sans', sans-serif; }

  .sr-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
  }
  .sr-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-soft);
    display: flex; align-items: center; gap: 10px;
    background: var(--surface2);
  }
  .sr-card-icon {
    width: 34px; height: 34px;
    background: var(--accent-light);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
  }
  .sr-card-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
  .sr-card-body  { padding: 20px; }

  .sr-alert {
    background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b;
    padding: 11px 14px; border-radius: var(--radius-sm);
    margin-bottom: 18px; font-size: 13px;
  }
  .sr-alert ul { margin-top: 5px; padding-left: 16px; }

  /* Section header */
  .sr-section-row {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 12px;
  }
  .sr-section-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }

  /* Table */
  .sr-table { width: 100%; border-collapse: collapse; }
  .sr-table thead th {
    padding: 8px 10px 10px;
    font-size: 11px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: 0.04em;
    text-align: left; border-bottom: 1.5px solid var(--border-soft);
    background: var(--surface2);
  }
  .sr-table thead th.r { text-align: right; }
  .sr-item-row td { padding: 7px 10px; vertical-align: middle; }
  .sr-item-row td.r { text-align: right; }

  .sr-input {
    padding: 8px 10px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px; color: var(--text-primary);
    background: var(--surface); outline: none; width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .sr-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(91,77,222,0.12);
  }
  .sr-input.r { text-align: right; }

  .sr-del {
    width: 27px; height: 27px; border: none;
    background: #fee2e2; color: var(--danger);
    border-radius: 6px; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700; transition: background 0.15s;
  }
  .sr-del:hover { background: #fca5a5; }

  /* Actions */
  .sr-actions {
    display: flex; gap: 10px; justify-content: flex-end;
    padding-top: 18px; border-top: 1px solid var(--border-soft); margin-top: 18px;
  }
  .sr-btn {
    padding: 10px 20px; border-radius: var(--radius-sm);
    font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.15s;
    display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none; border: none;
  }
  .sr-btn-ghost {
    background: transparent; color: var(--text-secondary);
    border: 1.5px solid var(--border);
  }
  .sr-btn-ghost:hover { background: var(--surface2); }
  .sr-btn-primary {
    background: var(--accent); color: #fff;
    box-shadow: 0 2px 10px rgba(91,77,222,0.35);
  }
  .sr-btn-primary:hover { background: var(--accent-hover); transform: translateY(-1px); }
  .sr-btn-add {
    background: var(--accent-light); color: var(--accent);
    border: 1.5px solid transparent; padding: 6px 13px; font-size: 12px;
  }
  .sr-btn-add:hover { border-color: var(--accent); }
</style>
@endpush

@section('content')
<div class="sr-wrap">
  <div class="sr-card">
    <div class="sr-card-header">
      <div class="sr-card-icon">📦</div>
      <span class="sr-card-title">Request Stock</span>
    </div>
    <div class="sr-card-body">

      @if($errors->any())
        <div class="sr-alert">
          <strong>Please fix the following:</strong>
          <ul>
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('stock-requests.store') }}" id="sr-form">
        @csrf

        <div class="sr-section-row">
          <span class="sr-section-title">Products Requested</span>
          <button type="button" id="add-row" class="sr-btn sr-btn-add">+ Add Product</button>
        </div>

        <table class="sr-table">
          <thead>
            <tr>
              <th style="width:75%">Product</th>
              <th class="r" style="width:17%">Quantity</th>
              <th style="width:8%"></th>
            </tr>
          </thead>
          <tbody id="items-body">
            {{-- Restore rows on validation failure --}}
            @if(old('items'))
              @foreach(old('items') as $i => $item)
                <tr class="sr-item-row">
                  <td>
                    <select name="items[{{ $i }}][product_id]" class="sr-input sr-sel" required>
                      <option value="">-- Search product --</option>
                      @foreach($products as $p)
                        <option value="{{ $p->id }}"
                          {{ old("items.{$i}.product_id") == $p->id ? 'selected' : '' }}>
                          {{ $p->name }} ({{ $p->stock_quantity }} available)
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td class="r">
                    <input type="number" name="items[{{ $i }}][quantity]"
                      class="sr-input r" value="{{ $item['quantity'] ?? 1 }}" min="1" required />
                  </td>
                  <td>
                    <button type="button" class="sr-del">×</button>
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>

        <div class="sr-actions">
          <a href="{{ route('dashboard') }}" class="sr-btn sr-btn-ghost">Cancel</a>
          <button type="submit" class="sr-btn sr-btn-primary">📦 Submit Request</button>
        </div>
      </form>

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
    });
}

function addRow() {
    const i    = idx++;
    const opts = PRODUCTS.map(p =>
        `<option value="${p.id}">${p.name} (${p.stock} available)</option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.className = 'sr-item-row';
    tr.innerHTML = `
        <td>
            <select name="items[${i}][product_id]" class="sr-input sr-sel" required>
                <option value="">-- Search product --</option>${opts}
            </select>
        </td>
        <td class="r">
            <input type="number" name="items[${i}][quantity]"
                class="sr-input r" min="1" value="1" required />
        </td>
        <td>
            <button type="button" class="sr-del">×</button>
        </td>
    `;

    document.getElementById('items-body').appendChild(tr);

    initTomSelect(tr.querySelector('.sr-sel'));

    tr.querySelector('.sr-del').addEventListener('click', () => tr.remove());
}

document.getElementById('add-row').addEventListener('click', addRow);

document.getElementById('sr-form').addEventListener('submit', e => {
    if (!document.querySelectorAll('.sr-item-row').length) {
        e.preventDefault();
        alert('Add at least one product.');
    }
});

// Init Tom Select on old() restored rows
document.querySelectorAll('.sr-sel').forEach(initTomSelect);

// Auto-add first row on fresh load
@if(!old('items')) addRow(); @endif
</script>
@endpush