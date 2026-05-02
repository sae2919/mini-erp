@extends('layouts.app')
@section('title','POS Terminal')
@section('heading','POS Terminal — ' . $seller->name)

@section('content')
<div style="height:calc(100vh - 120px)" class="py-2">
<div class="flex gap-4 h-full">

    {{-- LEFT: Products --}}
    <div class="flex-1 flex flex-col bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-3 border-b border-gray-100">
            <input type="text" id="pos-search" placeholder="🔍 Search product..."
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>

        <div class="px-3 py-2 border-b border-gray-100 flex gap-2 overflow-x-auto flex-shrink-0">
            <button onclick="filterCat('')" data-cat=""
                    class="cat-btn flex-shrink-0 px-3 py-1.5 text-xs font-semibold rounded-full bg-indigo-600 text-white">
                All
            </button>
            @foreach($categories as $cat)
            <button onclick="filterCat('{{ $cat->id }}')" data-cat="{{ $cat->id }}"
                    class="cat-btn flex-shrink-0 px-3 py-1.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>

        <div class="flex-1 overflow-y-auto p-3">
            @if($stock->isEmpty())
            <div class="text-center py-12 text-gray-400">
                <p class="text-4xl mb-3">📦</p>
                <p class="font-medium">No stock available</p>
                <p class="text-sm mt-1">Request a dispatch from the company</p>
            </div>
            @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2" id="pos-grid">
                @foreach($stock as $s)
                <div class="pos-card cursor-pointer rounded-xl border-2 border-gray-100 bg-gray-50
                            hover:border-indigo-400 hover:bg-indigo-50 transition-all duration-100 p-3"
                     data-id="{{ $s->product_id }}"
                     data-name="{{ addslashes($s->product->name) }}"
                     data-price="{{ $s->product->mrp }}"
                     data-stock="{{ $s->quantity }}"
                     data-unit="{{ $s->product->unit }}"
                     data-cat="{{ $s->product->category_id }}"
                     onclick="addToCart(this)">
                    <p class="text-xs text-indigo-400 font-medium truncate">{{ $s->product->category?->name }}</p>
                    <p class="text-sm font-bold text-gray-800 mt-0.5 leading-tight">{{ $s->product->name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $s->product->sku }}</p>
                    <div class="flex items-end justify-between mt-2">
                        <p class="text-sm font-bold text-indigo-600">₹{{ number_format($s->product->mrp, 2) }}</p>
                        <span class="text-xs {{ $s->quantity <= 5 ? 'text-orange-500 font-semibold' : 'text-gray-400' }}">
                            {{ $s->quantity }} left
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            <p id="pos-no-results" class="hidden text-center text-gray-400 py-8">No products found.</p>
            @endif
        </div>
    </div>

    {{-- RIGHT: Cart --}}
    <div class="w-72 flex-shrink-0 flex flex-col bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <span class="font-bold text-gray-800 text-sm">🛒 Cart (<span id="pos-count">0</span>)</span>
            <button onclick="clearCart()" class="text-xs text-red-400 hover:text-red-600 font-medium">Clear</button>
        </div>

        <div class="px-3 py-2 border-b border-gray-100">
            <input type="text" id="pos-customer" placeholder="Customer name (optional)"
                   class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400">
        </div>

        <div class="flex-1 overflow-y-auto">
            <div id="pos-empty" class="text-center text-gray-400 text-sm py-10">
                <p class="text-3xl mb-2">🛒</p>
                <p>Click a product to add</p>
            </div>
            <div id="pos-cart-list"></div>
        </div>

        <div class="border-t-2 border-gray-100 p-3 bg-gray-50">
            <div class="flex justify-between font-bold text-base mb-3">
                <span class="text-gray-800">Total</span>
                <span class="text-indigo-700" id="pos-total">₹0.00</span>
            </div>
            <button onclick="completeSale()" id="pos-btn"
                    class="w-full bg-green-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-green-700 transition">
                ✅ Complete Sale
            </button>
        </div>
    </div>
</div>
</div>

{{-- Receipt Modal --}}
<div id="pos-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center">
    <div style="background:white;border-radius:16px;width:320px;overflow:hidden;box-shadow:0 25px 50px rgba(0,0,0,0.3)">
        <div style="background:#16a34a;color:white;padding:24px;text-align:center">
            <div style="font-size:40px;margin-bottom:8px">✅</div>
            <div style="font-size:18px;font-weight:700">Sale Complete!</div>
            <div id="pos-ref" style="font-size:13px;opacity:0.85;margin-top:4px;font-family:monospace"></div>
        </div>
        <div style="padding:16px 20px">
            <div id="pos-receipt-items" style="font-size:13px;color:#374151;margin-bottom:12px;max-height:160px;overflow-y:auto"></div>
            <div style="border-top:2px solid #e5e7eb;padding-top:12px;display:flex;justify-content:space-between;font-weight:700;font-size:16px">
                <span>Total</span>
                <span id="pos-receipt-total" style="color:#4f46e5"></span>
            </div>
            <div style="margin-top:8px;font-size:12px;color:#7c3aed;text-align:right">
                Commission: <span id="pos-comm"></span>
            </div>
        </div>
        <div style="padding:0 20px 20px;display:flex;gap:8px">
            <button onclick="window.print()"
                    style="flex:1;padding:10px;border:1px solid #d1d5db;border-radius:10px;font-size:13px;cursor:pointer;background:white">
                🖨️ Print
            </button>
            <button onclick="newSale()"
                    style="flex:1;padding:10px;background:#4f46e5;color:white;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer">
                + New Sale
            </button>
        </div>
    </div>
</div>

<div id="pos-toast" style="display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:10px 20px;border-radius:999px;color:white;font-size:13px;font-weight:600;z-index:9998"></div>
@endsection

@push('scripts')
<script>
const cart = {};
const SELLER_ID = {{ $seller->id }};

function addToCart(el) {
    const id = el.dataset.id, stock = parseInt(el.dataset.stock);
    if (stock <= 0) { toast('Out of stock', false); return; }
    if (cart[id]) {
        if (cart[id].qty >= stock) { toast('Max stock reached', false); return; }
        cart[id].qty++;
    } else {
        cart[id] = { id, name: el.dataset.name, price: parseFloat(el.dataset.price), stock, unit: el.dataset.unit, qty: 1 };
    }
    el.style.borderColor = '#6366f1'; el.style.background = '#eef2ff';
    setTimeout(() => { el.style.borderColor = ''; el.style.background = ''; }, 300);
    renderCart();
    toast('Added: ' + el.dataset.name, true);
}

function changeQty(id, d) {
    if (!cart[id]) return;
    cart[id].qty += d;
    if (cart[id].qty <= 0) delete cart[id];
    else if (cart[id].qty > cart[id].stock) cart[id].qty = cart[id].stock;
    renderCart();
}

function removeItem(id) { delete cart[id]; renderCart(); }

function clearCart() {
    Object.keys(cart).forEach(k => delete cart[k]);
    renderCart();
    document.getElementById('pos-customer').value = '';
}

function renderCart() {
    const keys = Object.keys(cart);
    const empty = document.getElementById('pos-empty');
    const list  = document.getElementById('pos-cart-list');
    const total = document.getElementById('pos-total');
    const count = document.getElementById('pos-count');
    if (!keys.length) {
        empty.style.display = 'block'; list.innerHTML = '';
        total.textContent = '₹0.00'; count.textContent = '0'; return;
    }
    empty.style.display = 'none';
    let grand = 0, cnt = 0, html = '';
    keys.forEach(id => {
        const item = cart[id]; const sub = item.price * item.qty;
        grand += sub; cnt += item.qty;
        html += `<div style="border-bottom:1px solid #f3f4f6;padding:10px 12px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                <div style="flex:1;min-width:0">
                    <p style="font-size:12px;font-weight:700;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.name}</p>
                    <p style="font-size:11px;color:#9ca3af">₹${item.price.toFixed(2)} each</p>
                </div>
                <button onclick="removeItem('${id}')" style="color:#fca5a5;font-size:16px;font-weight:700;background:none;border:none;cursor:pointer">×</button>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px">
                <div style="display:flex;align-items:center;gap:6px">
                    <button onclick="changeQty('${id}',-1)" style="width:24px;height:24px;border-radius:50%;background:#f3f4f6;border:none;cursor:pointer;font-size:14px;font-weight:700;color:#374151">−</button>
                    <span style="font-size:13px;font-weight:700;color:#1f2937;min-width:20px;text-align:center">${item.qty}</span>
                    <button onclick="changeQty('${id}',1)"  style="width:24px;height:24px;border-radius:50%;background:#f3f4f6;border:none;cursor:pointer;font-size:14px;font-weight:700;color:#374151">+</button>
                </div>
                <span style="font-size:13px;font-weight:700;color:#4f46e5">₹${sub.toFixed(2)}</span>
            </div>
        </div>`;
    });
    list.innerHTML = html;
    total.textContent = '₹' + grand.toFixed(2);
    count.textContent = cnt;
}

async function completeSale() {
    if (!Object.keys(cart).length) { toast('Cart is empty!', false); return; }
    const btn = document.getElementById('pos-btn');
    btn.disabled = true; btn.textContent = '⏳ Processing...';
    try {
        const res = await fetch('{{ route("seller-pos.sale") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({
                seller_id: SELLER_ID,
                customer_name: document.getElementById('pos-customer').value.trim() || null,
                items: Object.values(cart).map(i => ({ product_id: i.id, quantity: i.qty, selling_price: i.price })),
            }),
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('pos-ref').textContent = data.reference;
            document.getElementById('pos-receipt-total').textContent = '₹' + data.total;
            document.getElementById('pos-comm').textContent = '₹' + data.commission;
            document.getElementById('pos-receipt-items').innerHTML = data.items.map(i =>
                `<div style="display:flex;justify-content:space-between;padding:3px 0"><span>${i.name} × ${i.qty}</span><span style="font-weight:600">₹${i.subtotal}</span></div>`
            ).join('');
            document.getElementById('pos-modal').style.display = 'flex';
        } else {
            alert('Error: ' + Object.values(data.errors||{}).flat().join('\n'));
        }
    } catch(e) { alert('Network error. Please try again.'); }
    finally { btn.disabled = false; btn.textContent = '✅ Complete Sale'; }
}

function newSale() {
    document.getElementById('pos-modal').style.display = 'none';
    clearCart(); location.reload();
}

let activeCat = '';
document.getElementById('pos-search').addEventListener('input', function() { applyFilters(this.value.toLowerCase(), activeCat); });

function filterCat(catId) {
    activeCat = catId;
    document.querySelectorAll('.cat-btn').forEach(b => {
        const on = b.dataset.cat === catId;
        b.style.background = on ? '#4f46e5' : '#f3f4f6';
        b.style.color = on ? 'white' : '#374151';
    });
    applyFilters(document.getElementById('pos-search').value.toLowerCase(), catId);
}

function applyFilters(term, catId) {
    let v = 0;
    document.querySelectorAll('.pos-card').forEach(c => {
        const ms = !term || c.dataset.name.toLowerCase().includes(term);
        const mc = !catId || c.dataset.cat === catId;
        c.style.display = ms && mc ? '' : 'none';
        if (ms && mc) v++;
    });
    document.getElementById('pos-no-results').classList.toggle('hidden', v > 0);
}

function toast(msg, ok) {
    const el = document.getElementById('pos-toast');
    el.textContent = msg;
    el.style.background = ok ? '#16a34a' : '#dc2626';
    el.style.display = 'block';
    clearTimeout(el._t);
    el._t = setTimeout(() => el.style.display = 'none', 2000);
}
</script>
@endpush