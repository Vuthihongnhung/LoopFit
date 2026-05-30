function formatMoney(number) {
    return number.toLocaleString('vi-VN') + 'đ';
}

// ========== TOAST ==========
if (typeof Toast === 'undefined') {
    window.Toast = {
        success: (msg) => alert(msg),
        error: (msg) => alert(msg),
        warning: (msg) => alert(msg)
    };
}

// ========== TÍNH TỔNG TIỀN (CHỈ TÍNH CÁC ITEM ĐƯỢC CHỌN) ==========
function updateCartTotal() {
    let totalRent = 0;
    let totalDeposit = 0;

    document.querySelectorAll('.cart-item').forEach(item => {
        const checkbox = item.querySelector('.item-select');
        if (checkbox && checkbox.checked) {
            let price = parseFloat(item.dataset.price);
            let days = parseInt(item.dataset.days, 10);
            let depositPerItem = parseFloat(item.dataset.depositPrice);
            let cartId = item.dataset.cartId;
            const qtySpan = document.getElementById('qty-' + cartId);
            let qty = qtySpan ? parseInt(qtySpan.innerText, 10) : 1;

            if (isNaN(price)) price = 0;
            if (isNaN(days)) days = 0;
            if (isNaN(depositPerItem)) depositPerItem = 0;
            if (isNaN(qty)) qty = 1;

            totalRent += price * days * qty;
            totalDeposit += depositPerItem * qty;
        }
    });

    const grandTotal = totalRent + totalDeposit;

    const tempEl = document.getElementById('cart-total-temp');
    const depositEl = document.getElementById('cart-deposit-total');
    const totalEl = document.getElementById('cart-total-all');
    if (tempEl) tempEl.innerText = formatMoney(totalRent);
    if (depositEl) depositEl.innerText = formatMoney(totalDeposit);
    if (totalEl) totalEl.innerText = formatMoney(grandTotal);
}

// ========== CẬP NHẬT BADGE GIỎ HÀNG ==========
function updateCartCount() {
    if (typeof BASE_URL === 'undefined') return;
    fetch(BASE_URL + '/api/cart/count')
        .then(res => res.json())
        .then(data => {
            const el = document.getElementById('cart-count');
            if (el) el.innerText = data.count;
        })
        .catch(() => {});
}

// ========== MODAL XÁC NHẬN XÓA ==========
let confirmModal = null;
function initConfirmModal() {
    if (!document.getElementById('confirm-modal')) {
        const modalHtml = `
           <div id="confirm-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
                <div style="background:white; padding:24px; border-radius:12px; max-width:400px; width:90%; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
                    <i class="fas fa-question-circle" style="font-size:48px; color:#f59e0b; margin-bottom:10px;"></i>
                    <p id="confirm-message" style="font-size:16px; margin-bottom:20px; color:#333;">Bạn có chắc chắn?</p>
                    <div style="display:flex; gap:12px; justify-content:center;">
                        <button id="confirm-yes" style="background:#dc3545; color:white; border:none; padding:8px 24px; border-radius:40px; font-weight:600; cursor:pointer;">Có</button>
                        <button id="confirm-no" style="background:#6c757d; color:white; border:none; padding:8px 24px; border-radius:40px; font-weight:600; cursor:pointer;">Không</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        confirmModal = document.getElementById('confirm-modal');
        document.getElementById('confirm-no')?.addEventListener('click', () => {
            confirmModal.style.display = 'none';
        });
    } else {
        confirmModal = document.getElementById('confirm-modal');
    }
}

function showConfirm(message, onConfirm) {
    initConfirmModal();
    const msgEl = document.getElementById('confirm-message');
    if (msgEl) msgEl.innerText = message;
    const yesBtn = document.getElementById('confirm-yes');
    const noBtn = document.getElementById('confirm-no');
    const oldYes = yesBtn.cloneNode(true);
    const oldNo = noBtn.cloneNode(true);
    yesBtn.parentNode.replaceChild(oldYes, yesBtn);
    noBtn.parentNode.replaceChild(oldNo, noBtn);
    oldYes.addEventListener('click', () => {
        confirmModal.style.display = 'none';
        onConfirm();
    });
    oldNo.addEventListener('click', () => {
        confirmModal.style.display = 'none';
    });
    confirmModal.style.display = 'flex';
}

// ========== CẬP NHẬT SỐ LƯỢNG ==========
window.updateQuantity = function(cartId, change) {
    const qtySpan = document.getElementById('qty-' + cartId);
    if (!qtySpan) return;
    let currentQty = parseInt(qtySpan.innerText, 10);
    let newQty = currentQty + change;
    if (newQty < 1) return;

    const oldQty = currentQty;
    qtySpan.innerText = newQty;

    const itemDiv = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
    if (itemDiv) {
        const price = parseFloat(itemDiv.dataset.price);
        const days = parseInt(itemDiv.dataset.days, 10);
        const totalSpan = document.getElementById('total-' + cartId);
        if (totalSpan) totalSpan.innerText = formatMoney(price * newQty * days);
    }
    updateCartTotal();

    fetch(BASE_URL + '/api/cart/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ cart_id: cartId, change: change })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            // rollback
            qtySpan.innerText = oldQty;
            if (itemDiv) {
                const price = parseFloat(itemDiv.dataset.price);
                const days = parseInt(itemDiv.dataset.days, 10);
                const totalSpan = document.getElementById('total-' + cartId);
                if (totalSpan) totalSpan.innerText = formatMoney(price * oldQty * days);
            }
            updateCartTotal();
            Toast.error(data.message || 'Không thể cập nhật số lượng');
        } else {
            updateCartCount();
        }
    })
    .catch(() => {
        qtySpan.innerText = oldQty;
        if (itemDiv) {
            const price = parseFloat(itemDiv.dataset.price);
            const days = parseInt(itemDiv.dataset.days, 10);
            const totalSpan = document.getElementById('total-' + cartId);
            if (totalSpan) totalSpan.innerText = formatMoney(price * oldQty * days);
        }
        updateCartTotal();
        Toast.error('Lỗi kết nối, vui lòng thử lại');
    });
};

// ========== XÓA SẢN PHẨM ==========
window.removeFromCart = function(cartId) {
    showConfirm('Bạn có chắc chắn muốn xóa sản phẩm này?', () => {
        fetch(BASE_URL + '/api/cart/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ cart_id: cartId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const itemDiv = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
                if (itemDiv) itemDiv.remove();
                updateCartTotal();
                updateCartCount();
                Toast.success('Đã xóa sản phẩm');
                if (document.querySelectorAll('.cart-item').length === 0) {
                    location.reload();
                }
            } else {
                Toast.error(data.message || 'Xóa thất bại');
            }
        })
        .catch(() => Toast.error('Lỗi kết nối'));
    });
};

// ========== CHỈNH SỬA SIZE, MÀU, NGÀY THUÊ, NGÀY TRẢ ==========
let currentEditCartId = null;
let currentEditProductId = null;

//Lấy danh sách size/màu của sản phẩm từ server
function fetchProductOptions(productId, callback) {
    fetch(BASE_URL + '/api/product/options?id=' + productId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                callback(data.sizes, data.colors);
            } else {
                Toast.error('Không thể tải thông tin sản phẩm');
                callback([], []);
            }
        })
        .catch(() => {
            Toast.error('Lỗi kết nối khi tải dữ liệu sản phẩm');
            callback([], []);
        });
}

function showEditModal(cartId, productId, currentSize, currentColor, currentRentDate, currentReturnDate) {
    currentEditCartId = cartId;
    currentEditProductId = productId;
    let modal = document.getElementById('edit-cart-modal');
    if (!modal) {
        // Tạo modal với giao diện đẹp
        const modalHtml = `
            <div id="edit-cart-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1001; justify-content:center; align-items:center; font-family:'Montserrat',sans-serif;">
                <div style="background:#ffffff; border-radius:28px; width:90%; max-width:520px; box-shadow:0 25px 45px -12px rgba(0,0,0,0.3); overflow:hidden; animation:fadeInUp 0.3s ease;">
                    <div style="background:#718BBC; padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
                        <h4 style="margin:0; font-family:'Playfair Display',serif; font-size:22px; font-weight:700; color:white;"><i class="fas fa-pen-alt" style="margin-right:10px;"></i>Chỉnh sửa sản phẩm</h4>
                        <i id="close-modal-btn" class="fas fa-times" style="color:white; font-size:22px; cursor:pointer;"></i>
                    </div>
                    <div style="padding:28px 28px 32px;">
                        <div style="margin-bottom:24px; display:flex; gap:16px; align-items:center; border-bottom:1px solid #eee; padding-bottom:16px;">
                            <div style="width:70px; height:70px; background:#F0F0ED; border-radius:20px; display:flex; align-items:center; justify-content:center; color:#718BBC; font-size:32px;"><i class="fas fa-tshirt"></i></div>
                            <div><strong id="edit-product-name" style="font-size:16px;">Đang tải...</strong></div>
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;"><i class="fas fa-ruler-combined"></i> Size</label>
                            <select id="edit-size" style="width:100%; padding:12px 16px; border-radius:40px; border:1.5px solid #e2e8f0; font-family:'Montserrat'; background:#fefefe; outline:none;"></select>
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;"><i class="fas fa-palette"></i> Màu sắc</label>
                            <select id="edit-color" style="width:100%; padding:12px 16px; border-radius:40px; border:1.5px solid #e2e8f0; font-family:'Montserrat'; background:#fefefe; outline:none;"></select>
                        </div>
                        <div style="display:flex; gap:16px; margin-bottom:20px;">
                            <div style="flex:1;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;"><i class="fas fa-calendar-alt"></i> Ngày thuê</label>
                                <input type="date" id="edit-rent-date" style="width:100%; padding:12px 16px; border-radius:40px; border:1.5px solid #e2e8f0; font-family:'Montserrat'; background:#fefefe;">
                            </div>
                            <div style="flex:1;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;"><i class="fas fa-calendar-check"></i> Ngày trả</label>
                                <input type="date" id="edit-return-date" style="width:100%; padding:12px 16px; border-radius:40px; border:1.5px solid #e2e8f0; font-family:'Montserrat'; background:#fefefe;">
                            </div>
                        </div>
                        <div style="background:#F8FAFE; padding:14px 18px; border-radius:24px; margin-bottom:28px; font-size:13px; color:#475569; display:flex; align-items:center; gap:12px;">
                            <i class="fas fa-info-circle" style="color:#718BBC; font-size:18px;"></i>
                            <span id="edit-summary">Số ngày: -- | Cọc: --</span>
                        </div>
                        <div style="display:flex; gap:16px;">
                            <button id="edit-cancel" class="btn btn-secondary" style="flex:1; background:transparent; border:1.5px solid #cbd5e1; border-radius:40px; padding:12px; font-weight:600; font-family:'Montserrat'; cursor:pointer; color:#dc3545;">Hủy</button>
                            <button id="edit-save" class="btn btn-primary" style="flex:1; background:#718BBC; border:none; border-radius:40px; padding:12px; color:white; font-weight:600; font-family:'Montserrat'; cursor:pointer;">Lưu thay đổi</button>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                @keyframes fadeInUp {
                    from { opacity:0; transform:translateY(20px); }
                    to { opacity:1; transform:translateY(0); }
                }
                #edit-cart-modal select:focus, #edit-cart-modal input:focus {
                    border-color: #718BBC;
                    box-shadow: 0 0 0 3px rgba(113,139,188,0.2);
                    outline: none;
                }
            </style>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('edit-cart-modal');
        
        // Gắn sự kiện đóng modal
        const closeBtn = document.getElementById('close-modal-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
        document.getElementById('edit-cancel')?.addEventListener('click', () => {
            modal.style.display = 'none';
        });
        document.getElementById('edit-save')?.addEventListener('click', () => {
            const newSize = document.getElementById('edit-size').value;
            const newColor = document.getElementById('edit-color').value;
            const newRentDate = document.getElementById('edit-rent-date').value;
            const newReturnDate = document.getElementById('edit-return-date').value;
            if (newRentDate && newReturnDate) {
                const today = new Date().toISOString().slice(0,10);
                if (newRentDate < today) {
                    Toast.error('Ngày thuê không thể trong quá khứ');
                    return;
                }
                if (newReturnDate <= newRentDate) {
                    Toast.error('Ngày trả phải sau ngày thuê');
                    return;
                }
            }
            saveCartItem(currentEditCartId, newSize, newColor, newRentDate, newReturnDate);
            modal.style.display = 'none';
        });
        
        // Đóng khi click ra ngoài modal (background)
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Lấy tên sản phẩm để hiển thị
    const productNameSpan = document.getElementById('edit-product-name');
    if (productNameSpan) {
        // Tìm tên sản phẩm từ DOM cart-item
        const cartItem = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
        if (cartItem) {
            const nameEl = cartItem.querySelector('.product-name');
            if (nameEl) productNameSpan.innerText = nameEl.innerText;
            else productNameSpan.innerText = 'Sản phẩm';
        } else {
            productNameSpan.innerText = 'Sản phẩm';
        }
    }
    
    // Tải options từ server trước khi hiển thị modal
    fetchProductOptions(productId, (sizes, colors) => {
        const sizeSelect = document.getElementById('edit-size');
        const colorSelect = document.getElementById('edit-color');
        if (!sizeSelect || !colorSelect) return;
        sizeSelect.innerHTML = '';
        colorSelect.innerHTML = '';
        (sizes || []).forEach(s => {
            const opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s;
            if (s === currentSize) opt.selected = true;
            sizeSelect.appendChild(opt);
        });
        (colors || []).forEach(c => {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            if (c === currentColor) opt.selected = true;
            colorSelect.appendChild(opt);
        });
        document.getElementById('edit-rent-date').value = currentRentDate;
        document.getElementById('edit-return-date').value = currentReturnDate;
        
        // Cập nhật summary (số ngày, tiền cọc)
        updateEditSummary(currentRentDate, currentReturnDate, cartId);
        
        modal.style.display = 'flex';
    });
}

// Hàm tính số ngày và hiển thị cọc
function updateEditSummary(rentDate, returnDate, cartId) {
    if (!rentDate || !returnDate) return;

    const start = new Date(rentDate);
    const end = new Date(returnDate);
    const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

    // Lấy tiền cọc từ thuộc tính data-deposit của phần tử .cart-item
    const cartItem = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
    let deposit = 0;
    if (cartItem && cartItem.dataset.deposit) {
        deposit = parseFloat(cartItem.dataset.deposit);
        // Kiểm tra nếu deposit là số nguyên (60.000) thì giữ nguyên
    } else {
        // Fallback an toàn: dùng 30% giá sản phẩm (nếu có data-price)
        const price = cartItem ? parseFloat(cartItem.dataset.price) : 0;
        deposit = price * 0.3;
        console.warn(`Không tìm thấy data-deposit cho cart ${cartId}, dùng 30% giá sản phẩm = ${deposit}`);
    }

    const summarySpan = document.getElementById('edit-summary');
    if (summarySpan) {
        summarySpan.innerHTML = `<i class="fas fa-calendar-week"></i> Số ngày: <strong>${diffDays}</strong> &nbsp;|&nbsp; <i class="fas fa-coins"></i> Cọc: <strong>${deposit.toLocaleString('vi-VN')}đ</strong>`;
    }

    // Gắn sự kiện change cho input ngày để cập nhật lại summary
    const rentInput = document.getElementById('edit-rent-date');
    const returnInput = document.getElementById('edit-return-date');
    if (!rentInput || !returnInput) return;

    const updateSummaryFromInputs = () => {
        const newRent = rentInput.value;
        const newReturn = returnInput.value;
        if (newRent && newReturn) {
            const s = new Date(newRent);
            const e = new Date(newReturn);
            const days = Math.ceil((e - s) / (1000 * 3600 * 24)) + 1;
            if (summarySpan) {
                summarySpan.innerHTML = `<i class="fas fa-calendar-week"></i> Số ngày: <strong>${days}</strong> &nbsp;|&nbsp; <i class="fas fa-coins"></i> Cọc: <strong>${deposit.toLocaleString('vi-VN')}đ</strong>`;
            }
        }
    };

    rentInput.removeEventListener('change', updateSummaryFromInputs);
    returnInput.removeEventListener('change', updateSummaryFromInputs);
    rentInput.addEventListener('change', updateSummaryFromInputs);
    returnInput.addEventListener('change', updateSummaryFromInputs);
}

function saveCartItem(cartId, newSize, newColor, newRentDate, newReturnDate) {
    fetch(BASE_URL + '/api/cart/update-item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            cart_id: cartId,
            size: newSize,
            color: newColor,
            rent_date: newRentDate,
            return_date: newReturnDate
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Toast.success('Cập nhật thành công');
            const itemDiv = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
            if (itemDiv) {
                // Cập nhật size/màu
                const sizeColorSpan = itemDiv.querySelector('.product-size-color');
                if (sizeColorSpan) sizeColorSpan.innerText = `Size: ${newSize} | Màu: ${newColor}`;
                // Cập nhật ngày
                const rentSpan = itemDiv.querySelector('.product-rent-date');
                const returnSpan = itemDiv.querySelector('.product-return-date');
                if (rentSpan) rentSpan.innerText = `Ngày thuê: ${newRentDate}`;
                if (returnSpan) returnSpan.innerText = `Ngày trả: ${newReturnDate}`;
                // Tính lại số ngày
                const start = new Date(newRentDate);
                const end = new Date(newReturnDate);
                const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                const daysSpan = itemDiv.querySelector('.product-days');
                if (daysSpan) daysSpan.innerText = `Số ngày: ${diffDays}`;
                itemDiv.dataset.days = diffDays;
                itemDiv.dataset.rentDate = newRentDate;
                itemDiv.dataset.returnDate = newReturnDate;
                // Cập nhật tổng tiền dòng
                const price = parseFloat(itemDiv.dataset.price);
                const qtySpan = document.getElementById('qty-' + cartId);
                const qty = qtySpan ? parseInt(qtySpan.innerText, 10) : 1;
                const newTotal = price * diffDays * qty;
                const totalSpan = document.getElementById('total-' + cartId);
                if (totalSpan) totalSpan.innerText = formatMoney(newTotal);
                updateCartTotal();
            }
        } else {
            Toast.error(data.message || 'Cập nhật thất bại');
        }
    })
    .catch(() => Toast.error('Lỗi kết nối'));
}

function bindEditEvents() {
    document.querySelectorAll('.cart-name, .edit-item-btn').forEach(el => {
        el.removeEventListener('click', handleEditClick);
        el.addEventListener('click', handleEditClick);
    });
}

function handleEditClick(event) {
    const itemDiv = event.target.closest('.cart-item');
    if (!itemDiv) return;
    const cartId = itemDiv.dataset.cartId;
    const productId = itemDiv.dataset.productId;
    if (!productId) {
        Toast.error('Không xác định được sản phẩm');
        return;
    }
    // Lấy thông tin hiện tại từ các span
    const sizeColorSpan = itemDiv.querySelector('.product-size-color');
    let currentSize = 'M', currentColor = 'Trắng';
    if (sizeColorSpan) {
        const text = sizeColorSpan.innerText;
        const sizeMatch = text.match(/Size:\s*(\S+)/);
        const colorMatch = text.match(/Màu:\s*(\S+)/);
        if (sizeMatch) currentSize = sizeMatch[1];
        if (colorMatch) currentColor = colorMatch[1];
    }
    const rentSpan = itemDiv.querySelector('.product-rent-date');
    const returnSpan = itemDiv.querySelector('.product-return-date');
    const currentRentDate = rentSpan ? rentSpan.innerText.replace('Ngày thuê: ', '') : '';
    const currentReturnDate = returnSpan ? returnSpan.innerText.replace('Ngày trả: ', '') : '';
    showEditModal(cartId, productId, currentSize, currentColor, currentRentDate, currentReturnDate);
}

// ========== KHỞI TẠO ==========
document.addEventListener('DOMContentLoaded', function() {
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = '';
    }
    updateCartCount();
    updateCartTotal();
    initConfirmModal();
    bindEditEvents();

    document.querySelectorAll('.item-select').forEach(cb => {
        cb.addEventListener('change', updateCartTotal);
    });

    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.item-select').forEach(cb => cb.checked = this.checked);
            updateCartTotal();
        });
    }

    const checkoutBtn = document.getElementById('checkout-selected');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            // Lấy danh sách các sản phẩm được chọn kèm ngày thuê/trả
            const selectedItems = [];
            document.querySelectorAll('.cart-item').forEach(item => {
                const cb = item.querySelector('.item-select');
                if (cb && cb.checked) {
                    const rentDate = item.dataset.rentDate;
                    const returnDate = item.dataset.returnDate;
                    const cartId = item.dataset.cartId;
                    selectedItems.push({ cartId, rentDate, returnDate });
                }
            });
            if (selectedItems.length === 0) {
                Toast.warning('Vui lòng chọn ít nhất một sản phẩm');
                return;
            }
            // Kiểm tra đồng nhất ngày
            const firstStart = selectedItems[0].rentDate;
            const firstEnd = selectedItems[0].returnDate;
            let isValid = true;
            for (let i = 1; i < selectedItems.length; i++) {
                if (selectedItems[i].rentDate !== firstStart || selectedItems[i].returnDate !== firstEnd) {
                    isValid = false;
                    break;
                }
            }
            if (!isValid) {
                Toast.error('Các sản phẩm được chọn phải có cùng ngày thuê và ngày trả. Vui lòng chỉ chọn các sản phẩm cùng ngày.');
                return;
            }
            // Hợp lệ, chuyển sang checkout
            const cartIds = selectedItems.map(item => item.cartId);
            window.location.href = BASE_URL + '/checkout?cart_ids=' + cartIds.join(',');
        });
    }
});