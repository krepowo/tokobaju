@extends('layouts.site')

@section('title', 'Keranjang Belanja')
@section('active', 'keranjang')
@section('main-class', 'site-main--full')

@section('content')
<div class="cart-page">
    <div class="cart-container">
        <!-- Page Title & Active Items Count -->
        <div class="cart-header-row">
            <h1 class="cart-title">Keranjang Belanja</h1>
            <div class="cart-active-badge">
                Keranjang Aktif ({{ $items->count() }})
            </div>
        </div>

        @if (session('success'))
            <div class="alert-box alert-box--success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-box alert-box--error">
                {{ session('error') }}
            </div>
        @endif

        @if ($items->isEmpty())
            <!-- Empty Cart State -->
            <div class="cart-empty-card">
                <div class="cart-empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="56" height="56">
                        <path d="M6 8h12l-1.2 11.2a1.6 1.6 0 0 1-1.6 1.4H8.8a1.6 1.6 0 0 1-1.6-1.4L6 8Z"/>
                        <path d="M9.2 8V6.4a2.8 2.8 0 0 1 5.6 0V8"/>
                    </svg>
                </div>
                <h2 class="cart-empty-title">Keranjang Belanja Masih Kosong</h2>
                <p class="cart-empty-subtitle">
                    Belum ada pakaian atau aksesori yang Anda masukkan. Temukan koleksi favorit Anda sekarang!
                </p>
                <a href="{{ route('katalog') }}" class="btn-continue-shopping">Mulai Belanja</a>
            </div>
        @else
            <!-- Cart Table Card -->
            <div class="cart-card">
                <!-- Table Header -->
                <div class="cart-table-header">
                    <div class="col-select-all">
                        <label class="cart-checkbox-wrapper">
                            <input type="checkbox"
                                   id="selectAllCheckbox"
                                   {{ $semuaDipilih ? 'checked' : '' }}
                                   onchange="toggleSelectAll(this.checked)"
                                   class="cart-checkbox">
                            <span class="col-header-label">PILIH SEMUA</span>
                        </label>
                    </div>
                    <div class="col-header-text col-price">HARGA</div>
                    <div class="col-header-text col-qty">JUMLAH</div>
                    <div class="col-header-text col-subtotal">SUBTOTAL</div>
                </div>

                <!-- Item Rows -->
                <div class="cart-items-list">
                    @foreach ($items as $item)
                        @php
                            $prod = $item->varianUkuran?->produk;
                            $variant = $item->varianUkuran;
                            $unitPrice = (float) ($prod?->harga ?? 0);
                            $stokTersedia = (int) ($variant?->stok ?? 0);
                            $stokCukup = $stokTersedia >= $item->jumlah;
                        @endphp

                        <div class="cart-item-row" id="cartRow-{{ $item->id_keranjang }}">
                            <!-- Selection Checkbox + Product Info -->
                            <div class="cart-item-main">
                                <label class="cart-checkbox-wrapper">
                                    <input type="checkbox"
                                           class="cart-checkbox item-checkbox"
                                           data-id="{{ $item->id_keranjang }}"
                                           {{ $item->dipilih ? 'checked' : '' }}
                                           onchange="toggleItemSelect('{{ $item->id_keranjang }}', this.checked)">
                                </label>

                                <a href="{{ $prod ? route('produk.show', $prod->id_produk) : '#' }}" class="cart-item-thumb">
                                    <img src="{{ $prod?->gambar_url ?? asset('images/prod-kaos.jpg') }}" alt="{{ $prod?->nama_produk ?? 'Produk' }}">
                                </a>

                                <div class="cart-item-info">
                                    <h3 class="cart-item-name">
                                        <a href="{{ $prod ? route('produk.show', $prod->id_produk) : '#' }}">
                                            {{ $prod?->nama_produk ?? 'Produk Busana' }}
                                        </a>
                                    </h3>
                                    <div class="cart-item-meta">
                                        Warna: <strong>{{ $variant?->warna ?? '-' }}</strong> | Ukuran: <strong>{{ $variant?->ukuran ?? '-' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Unit Price -->
                            <div class="cart-col-price">
                                Rp {{ number_format($unitPrice, 0, ',', '.') }}
                            </div>

                            <!-- Quantity Stepper + Stock Warning -->
                            <div class="cart-col-qty">
                                <div class="cart-stepper">
                                    <button type="button"
                                            class="cart-stepper-btn"
                                            onclick="updateItemQty('{{ $item->id_keranjang }}', -1)"
                                            aria-label="Kurangi">&minus;</button>
                                    <span class="cart-stepper-val" id="qtyVal-{{ $item->id_keranjang }}">{{ $item->jumlah }}</span>
                                    <button type="button"
                                            class="cart-stepper-btn"
                                            onclick="updateItemQty('{{ $item->id_keranjang }}', 1)"
                                            aria-label="Tambah">&plus;</button>
                                </div>

                                <div class="cart-stock-warning"
                                     id="stockWarning-{{ $item->id_keranjang }}"
                                     style="{{ $stokCukup ? 'display: none;' : 'display: flex;' }}">
                                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Stok tidak cukup</span>
                                </div>
                            </div>

                            <!-- Subtotal + Delete Button -->
                            <div class="cart-col-subtotal">
                                <span class="cart-subtotal-text" id="subtotalVal-{{ $item->id_keranjang }}">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>

                                <button type="button"
                                        class="cart-btn-delete"
                                        onclick="confirmDeleteItem('{{ $item->id_keranjang }}', '{{ addslashes($prod?->nama_produk ?? 'Produk') }}')"
                                        title="Hapus produk"
                                        aria-label="Hapus produk">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Bottom Floating Action Bar -->
            <div class="cart-bottom-bar">
                <div class="cart-bottom-inner">
                    <!-- Left: Lanjut Belanja -->
                    <a href="{{ route('katalog') }}" class="cart-back-link">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16" aria-hidden="true">
                            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                        </svg>
                        <span>Lanjut Belanja</span>
                    </a>

                    <!-- Middle: Info Pengiriman -->
                    <div class="cart-notice-text">
                        Alamat dan jasa pengiriman diisi pada tahap checkout.
                    </div>

                    <!-- Right: Total & Checkout Button -->
                    <div class="cart-checkout-action">
                        <div class="cart-total-group">
                            <span class="cart-total-label">Total Harga</span>
                            <span class="cart-total-amount" id="totalHargaDisplay">
                                Rp {{ number_format($totalHarga, 0, ',', '.') }}
                            </span>
                        </div>

                        <button type="button"
                                class="btn-checkout"
                                id="btnCheckout"
                                {{ $totalItemDipilih === 0 ? 'disabled' : '' }}
                                onclick="proceedToCheckout()">
                            Checkout (<span id="selectedCountDisplay">{{ $totalItemDipilih }}</span>)
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal Konfirmasi Hapus Produk -->
<div id="deleteModal" class="custom-modal" style="display: none;">
    <div class="custom-modal__overlay" onclick="closeDeleteModal()"></div>
    <div class="custom-modal__dialog">
        <div class="custom-modal__header">
            <h3 class="custom-modal__title">Hapus Produk</h3>
            <button type="button" class="custom-modal__close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="custom-modal__body">
            <p>Yakin ingin menghapus produk ini?</p>
            <strong id="deleteProductName" style="color: #0f172a; display: block; margin-top: 0.5rem;"></strong>
        </div>
        <div class="custom-modal__footer">
            <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">Batal</button>
            <form id="deleteForm" method="POST" action="" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-modal-confirm">Hapus</button>
            </form>
        </div>
    </div>
</div>

<script>
    let pendingDeleteId = null;

    function confirmDeleteItem(itemId, productName) {
        pendingDeleteId = itemId;
        document.getElementById('deleteProductName').textContent = productName;
        document.getElementById('deleteForm').action = `/keranjang/${itemId}`;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        pendingDeleteId = null;
    }

    async function toggleSelectAll(isChecked) {
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.checked = isChecked;
        });

        try {
            const res = await fetch("{{ route('keranjang.toggleAll') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ dipilih: isChecked })
            });

            const data = await res.json();
            if (data.success) {
                updateTotalsUI(data.totals);
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function toggleItemSelect(itemId, isChecked) {
        try {
            const res = await fetch(`/keranjang/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ dipilih: isChecked })
            });

            const data = await res.json();
            if (data.success) {
                updateTotalsUI(data.totals);
                document.getElementById('selectAllCheckbox').checked = data.totals.all_selected;
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function updateItemQty(itemId, delta) {
        const qtyEl = document.getElementById(`qtyVal-${itemId}`);
        let currentQty = parseInt(qtyEl.textContent) || 1;
        let nextQty = currentQty + delta;
        if (nextQty < 1) return;

        try {
            const res = await fetch(`/keranjang/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ jumlah: nextQty })
            });

            const data = await res.json();
            if (data.success) {
                qtyEl.textContent = nextQty;
                document.getElementById(`subtotalVal-${itemId}`).textContent = data.subtotal;

                const warningEl = document.getElementById(`stockWarning-${itemId}`);
                if (data.stok_cukup) {
                    warningEl.style.display = 'none';
                } else {
                    warningEl.style.display = 'flex';
                }

                updateTotalsUI(data.totals);
            }
        } catch (e) {
            console.error(e);
        }
    }

    function updateTotalsUI(totals) {
        if (!totals) return;
        document.getElementById('totalHargaDisplay').textContent = totals.total_formatted;
        document.getElementById('selectedCountDisplay').textContent = totals.selected_count;

        const btnCheckout = document.getElementById('btnCheckout');
        if (totals.selected_count > 0) {
            btnCheckout.disabled = false;
        } else {
            btnCheckout.disabled = true;
        }
    }

    function proceedToCheckout() {
        window.location.href = "{{ route('checkout.index') }}";
    }
</script>
@endsection
