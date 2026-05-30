<?php 
$pageTitle = 'Chi tiết đơn hàng #' . ($order['id'] ?? ''); 
$activeMenu = 'orders';

$totalDisplay = $order['final_total'] ?? 0;
if ($totalDisplay == 0) {
    $totalDisplay = ($order['total_price'] ?? 0) + ($order['deposit_amount'] ?? 0) + ($order['shipping_fee'] ?? 0) - ($order['discount_price'] ?? 0);
}
$depositAmount = $order['deposit_amount'] ?? 0;
$lateFee = (float)($order['late_fee'] ?? 0);
$refundedAmount = (float)($order['deposit_refunded_amount'] ?? 0);
$depositStatus = $order['deposit_status'] ?? 'pending';
$orderStatus = $order['status'] ?? '';
$lateFeeStatus = $order['late_fee_status'] ?? 'unpaid';
?>
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        50: '#eff6ff',
                        100: '#dbeafe',
                        200: '#bfdbfe',
                        300: '#93c5fd',
                        400: '#4478b8',
                        500: '#2f64ba',
                        600: '#2a51a3',
                        700: '#1c3fa1',
                        800: '#162c74',
                        900: '#0e1c43',
                    }
                }
            }
        }
    }
</script>
<script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>

<div class="max-w-5xl mx-auto px-4 py-3">
    <!-- Header -->
    <div class="flex justify-between items-center mb-3 flex-wrap gap-2">
        <h1 class="text-base font-bold text-primary-800">
            <i class="fas fa-shopping-cart text-primary-600 mr-2 text-sm"></i>
            Chi tiết đơn hàng #<?php echo $order['id'] ?? ''; ?>
        </h1>
        <a href="javascript:history.back()" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg hover:bg-primary-100 hover:text-primary-700 transition-all duration-200 text-xs font-medium flex items-center gap-1.5">
            <i class="fas fa-arrow-left text-xs"></i> Quay lại
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <!-- Thông tin tài khoản & địa chỉ giao hàng -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-primary-100">
            <div class="bg-gradient-to-r from-primary-700 to-primary-500 px-3 py-2">
                <h3 class="text-white font-semibold text-xs flex items-center gap-1.5">
                    <i class="fas fa-user-circle text-xs"></i> Thông tin khách hàng & địa chỉ giao hàng
                </h3>
            </div>
            <div class="p-3">
                <h4 class="text-xs font-semibold text-primary-700 mb-1">Tài khoản đặt hàng</h4>
                <table class="w-full text-xs mb-3">
                    <tr class="border-b border-gray-100"><td class="py-1 font-medium text-gray-500 w-28">Khách hàng</td><td class="py-1 font-semibold text-gray-800"><?php echo htmlspecialchars($order['user_name'] ?? 'N/A'); ?></td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1 font-medium text-gray-500">Email</td><td class="py-1 text-gray-700"><?php echo htmlspecialchars($order['user_email'] ?? $order['email'] ?? 'N/A'); ?></td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1 font-medium text-gray-500">Điện thoại</td><td class="py-1 text-gray-700"><?php echo htmlspecialchars($order['phone'] ?? 'Chưa cập nhật'); ?></td></tr>
                </table>

                <h4 class="text-xs font-semibold text-primary-700 mb-1">Người nhận hàng</h4>
                <table class="w-full text-xs">
                    <?php if (!empty($address)): ?>
                        <tr class="border-b border-gray-100"><td class="py-1 font-medium text-gray-500 w-28">Họ tên</td><td class="py-1 text-gray-700"><?php echo htmlspecialchars($address['full_name'] ?? ''); ?></td></tr>
                        <tr class="border-b border-gray-100"><td class="py-1 font-medium text-gray-500">SĐT người nhận</td><td class="py-1 text-gray-700"><?php echo htmlspecialchars($address['phone'] ?? ''); ?></td></tr>
                        <tr class="border-b border-gray-100"><td class="py-1 font-medium text-gray-500">Địa chỉ</td><td class="py-1 text-gray-700"><?php echo htmlspecialchars($address['address_line'] ?? ''); ?></td></tr>
                    <?php else: ?>
                        <tr><td colspan="2" class="py-1 text-gray-400">Chưa có thông tin địa chỉ giao hàng</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Thông tin đơn hàng chi tiết (có form cập nhật ngày trả thực tế) -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-primary-100">
            <div class="bg-gradient-to-r from-primary-700 to-primary-500 px-3 py-2">
                <h3 class="text-white font-semibold text-xs flex items-center gap-1.5">
                    <i class="fas fa-receipt text-xs"></i> Thông tin đơn hàng
                </h3>
            </div>
            <div class="p-3">
                <table class="w-full text-xs">
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500 w-32">Ngày thuê</td><td class="py-1.5 text-gray-700"><?php echo date('d/m/Y', strtotime($order['rent_date'] ?? 'now')); ?></td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Ngày trả dự kiến</td><td class="py-1.5 text-gray-700"><?php echo date('d/m/Y', strtotime($order['return_date'] ?? 'now')); ?></td></tr>
                    
                    <!-- PHẦN CẬP NHẬT NGÀY TRẢ THỰC TẾ -->
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 font-medium text-gray-500">Ngày trả thực tế</td>
                        <td class="py-1.5">
                            <?php 
                            $canUpdateActual = in_array($orderStatus, ['pending', 'confirmed', 'preparing', 'shipping', 'renting']);
                            $actualDate = $order['actual_return_date'] ?? '';
                            ?>
                            <?php if ($canUpdateActual): ?>
                                <div class="flex flex-wrap items-center gap-2">
                                    <input type="date" id="actual_return_date_input" class="px-2 py-1 text-xs border border-gray-300 rounded-md" value="<?php echo date('Y-m-d'); ?>">
                                    <button id="update_actual_return_btn" data-order-id="<?php echo $order['id']; ?>" class="px-3 py-1 bg-primary-600 text-white text-xs rounded-md hover:bg-primary-700">Cập nhật & tính phí trễ</button>
                                    <span id="update_status_msg" class="text-xs text-green-600 hidden"></span>
                                </div>
                                <div id="current_actual_return" class="mt-1 text-gray-500 text-[11px]">
                                    Hiện tại: <?php echo $actualDate ? date('d/m/Y', strtotime($actualDate)) : 'Chưa có'; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-700"><?php echo $actualDate ? date('d/m/Y', strtotime($actualDate)) : '<span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700">Chưa trả</span>'; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Số ngày trễ</td>
                        <td class="py-1.5 text-gray-700" id="late_days_display">
                            <?php
                            $lateDays = 0;
                            if (!empty($order['actual_return_date']) && !empty($order['return_date'])) {
                                $returnDate = new DateTime($order['return_date']);
                                $actualReturnDate = new DateTime($order['actual_return_date']);
                                if ($actualReturnDate > $returnDate) {
                                    $lateDays = $actualReturnDate->diff($returnDate)->days;
                                }
                            }
                            echo $lateDays . ' ngày';
                            ?>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Tiền thuê (chưa cọc)</td><td class="py-1.5 text-gray-700"><?php echo number_format($order['total_price'] ?? 0, 0, ',', '.'); ?>đ</td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Phí vận chuyển</td><td class="py-1.5 text-gray-700"><?php echo number_format($order['shipping_fee'] ?? 0, 0, ',', '.'); ?>đ</td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Tiền cọc</td><td class="py-1.5 text-gray-700"><?php echo number_format($depositAmount, 0, ',', '.'); ?>đ</td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Tiền cọc hoàn lại</td>
                        <td class="py-1.5 text-gray-700">
                            <?php
                            if (in_array($depositStatus, ['refunded_full', 'refunded_partial']) && $refundedAmount > 0) {
                                echo number_format($refundedAmount, 0, ',', '.') . 'đ';
                            } elseif ($depositStatus == 'forfeited') {
                                echo '0đ';
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Giảm giá</td><td class="py-1.5 text-red-600">- <?php echo number_format($order['discount_price'] ?? 0, 0, ',', '.'); ?>đ</td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Phí trễ</td><td class="py-1.5 text-red-600" id="late_fee_display"><?php echo number_format($lateFee, 0, ',', '.'); ?>đ</td></tr>
                    <tr class="bg-gray-50"><td class="py-1.5 font-semibold text-gray-700">Tổng thanh toán</td><td class="py-1.5 font-bold text-primary-600"><?php echo number_format($totalDisplay, 0, ',', '.'); ?>đ</td></tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-1.5 font-medium text-gray-500">Ghi chú</td>
                        <td class="py-1.5 text-gray-700"><?php echo nl2br(htmlspecialchars($order['notes'] ?? 'Không có ghi chú')); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Trạng thái đơn hàng -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-4 border border-primary-100">
        <div class="bg-gradient-to-r from-primary-800 to-primary-600 px-3 py-2">
            <h3 class="text-white font-semibold text-xs flex items-center gap-1.5">
                <i class="fas fa-chart-line text-xs"></i> Trạng thái đơn hàng
            </h3>
        </div>
        <div class="p-3 flex flex-wrap gap-4">
            <div class="flex-1 min-w-[160px]">
                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Trạng thái đơn</div>
                <select id="orderStatus" data-order-id="<?php echo $order['id']; ?>" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="pending" <?php echo ($orderStatus == 'pending') ? 'selected' : ''; ?>>Chờ xác nhận</option>
                    <option value="confirmed" <?php echo ($orderStatus == 'confirmed') ? 'selected' : ''; ?>>Đã xác nhận</option>
                    <option value="shipping" <?php echo ($orderStatus == 'shipping') ? 'selected' : ''; ?>>Đang giao</option>
                    <option value="renting" <?php echo ($orderStatus == 'renting') ? 'selected' : ''; ?>>Đang thuê</option>
                    <option value="returning" <?php echo ($orderStatus == 'returning') ? 'selected' : ''; ?>>Đang hoàn trả</option>
                    <option value="completed" <?php echo ($orderStatus == 'completed') ? 'selected' : ''; ?>>Hoàn tất</option>
                    <option value="cancelled" <?php echo ($orderStatus == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Thanh toán</div>
                <?php
                $payment_status = $order['payment_status'] ?? 'pending';
                $payment_badge_class = match($payment_status) {
                    'paid' => 'bg-green-100 text-green-700',
                    'pending_verification' => 'bg-amber-100 text-amber-700',
                    default => 'bg-gray-100 text-gray-600'
                };
                $payment_labels = ['pending' => 'Chưa thanh toán', 'pending_verification' => 'Chờ xác nhận', 'paid' => 'Đã thanh toán'];
                ?>
                <span class="inline-block px-2 py-1 rounded-full text-[11px] font-medium <?php echo $payment_badge_class; ?>">
                    <?php echo $payment_labels[$payment_status] ?? 'Chưa thanh toán'; ?>
                </span>
            </div>
            <div class="flex-1 min-w-[160px]">
                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Tiền cọc</div>
                <?php
                $deposit_status_text = match($depositStatus) {
                    'pending' => 'Chưa hoàn',
                    'pending_return' => 'Chờ hoàn cọc',
                    'refunded_full' => 'Đã hoàn toàn bộ',
                    'refunded_partial' => 'Hoàn một phần',
                    'forfeited' => 'Bị giữ (không hoàn)',
                    default => 'Không xác định'
                };
                ?>
                <span class="inline-block px-2 py-1 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                    <?php echo $deposit_status_text; ?>
                </span>
            </div>
            <?php if ($lateFee > 0): ?>
            <div class="flex-1 min-w-[160px]">
                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Phí trễ</div>
                <?php
                $lateFeeStatusText = '';
                $lateFeeStatusClass = '';
                switch ($lateFeeStatus) {
                    case 'paid':
                        $lateFeeStatusText = 'Đã thanh toán';
                        $lateFeeStatusClass = 'bg-green-100 text-green-700';
                        break;
                    case 'pending_verification':
                        $lateFeeStatusText = 'Chờ xác nhận';
                        $lateFeeStatusClass = 'bg-amber-100 text-amber-700';
                        break;
                    default:
                        $lateFeeStatusText = 'Chưa thanh toán';
                        $lateFeeStatusClass = 'bg-red-100 text-red-700';
                }
                ?>
                <span class="inline-block px-2 py-1 rounded-full text-[11px] font-medium <?= $lateFeeStatusClass ?>">
                    <?= $lateFeeStatusText ?>
                </span>
                <?php if ($lateFeeStatus == 'pending_verification'): ?>
                    <button id="markLateFeePaidBtn" data-order-id="<?= $order['id'] ?>" class="mt-1 w-full px-2 py-1 bg-blue-600 text-white text-[10px] rounded-md hover:bg-blue-700">Xác nhận đã thu phí trễ</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sản phẩm đã thuê -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-4 border border-primary-100">
        <div class="bg-gradient-to-r from-primary-700 to-primary-500 px-3 py-2">
            <h3 class="text-white font-semibold text-xs flex items-center gap-1.5">
                <i class="fas fa-boxes text-xs"></i> Sản phẩm đã thuê (giá đã áp dụng khuyến mãi)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Sản phẩm</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">SL</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Đơn giá/ngày (sau giảm)</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Cọc/sp</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Số ngày</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Thành tiền (thuê)</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Tình trạng</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item):
                    $rent = strtotime($item['rent_date'] ?? $order['rent_date']);
                    $return = strtotime($item['return_date'] ?? $order['return_date']);
                    $days = max(1, floor(($return - $rent) / 86400) + 1);
                    $pricePerDay = (float)($item['price_per_day'] ?? $item['original_price'] ?? $item['price'] ?? 0);
                    $depositPerItem = (float)($item['deposit_per_item'] ?? $item['deposit_price'] ?? 0);
                    $quantity = (int)($item['quantity'] ?? 0);
                    $subtotalRent = $pricePerDay * $quantity * $days;
                    $originalPrice = (float)($item['original_price'] ?? $item['price'] ?? 0);
                ?>
                    <tr class="border-t border-gray-100">
                        <td class="px-2 py-1.5 font-medium text-gray-800"><?php echo htmlspecialchars($item['product_name'] ?? 'N/A'); ?></td>
                        <td class="px-2 py-1.5"><?php echo $quantity; ?></td>
                        <td class="px-2 py-1.5">
                            <?php echo number_format($pricePerDay, 0, ',', '.'); ?>đ
                            <?php if ($originalPrice > 0 && $originalPrice != $pricePerDay): ?>
                                <span class="text-gray-400 line-through text-[10px] ml-1">(<?php echo number_format($originalPrice, 0, ',', '.'); ?>đ)</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-2 py-1.5"><?php echo number_format($depositPerItem, 0, ',', '.'); ?>đ</td>
                        <td class="px-2 py-1.5"><?php echo $days; ?></td>
                        <td class="px-2 py-1.5 font-semibold text-primary-600"><?php echo number_format($subtotalRent, 0, ',', '.'); ?>đ</td>
                        <td class="px-2 py-1.5">
                            <select class="status-after-return px-1.5 py-0.5 text-[11px] border border-gray-300 rounded-md focus:ring-1 focus:ring-primary-500" data-detail-id="<?php echo $item['id']; ?>">
                                <option value="">-- Chọn --</option>
                                <option value="good" <?php echo ($item['status_after_return'] ?? '') == 'good' ? 'selected' : ''; ?>>Tốt</option>
                                <option value="damaged" <?php echo ($item['status_after_return'] ?? '') == 'damaged' ? 'selected' : ''; ?>>Hỏng</option>
                                <option value="lost" <?php echo ($item['status_after_return'] ?? '') == 'lost' ? 'selected' : ''; ?>>Mất</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Nút hoàn cọc -->
    <?php 
    // Điều kiện hiển thị nút hoàn cọc:
    // - Đơn hàng đang ở trạng thái 'returning' hoặc 'completed'
    // - deposit_status = 'pending_return' (chờ hoàn cọc)
    // - Nếu có phí trễ thì phải đã thanh toán (late_fee_status = 'paid')
    $canProcessDeposit = false;
    if (in_array($orderStatus, ['returning', 'completed']) && $depositStatus == 'pending_return') {
        if ($lateFee <= 0 || $lateFeeStatus == 'paid') {
            $canProcessDeposit = true;
        }
    }
    ?>
    <?php if ($canProcessDeposit): ?>
    <div class="flex justify-end mt-3">
        <button id="processDepositBtn" class="px-4 py-1.5 bg-gradient-to-r from-emerald-600 to-green-500 text-white rounded-md hover:from-emerald-700 hover:to-green-600 transition-all duration-200 font-semibold text-xs flex items-center gap-1.5 shadow-sm" data-order-id="<?php echo $order['id']; ?>">
            <i class="fas fa-hand-holding-usd text-xs"></i> Xử lý hoàn cọc
        </button>
    </div>
    <?php elseif ($orderStatus == 'returning' && $depositStatus == 'pending_return' && $lateFee > 0 && $lateFeeStatus != 'paid'): ?>
    <div class="flex justify-end mt-3">
        <div class="text-red-600 text-xs font-medium">⚠️ Khách chưa thanh toán phí trễ, chưa thể hoàn cọc.</div>
    </div>
    <?php endif; ?>
</div>

<style>
    .status-after-return {
        background-color: white;
        cursor: pointer;
        transition: all 0.2s;
    }
    .status-after-return:hover {
        border-color: #2a51a3;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const BASE_URL = '<?php echo BASE_URL; ?>';
        console.log('BASE_URL:', BASE_URL);

        async function safeFetchJSON(url, options) {
            try {
                const response = await fetch(url, options);
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Lỗi parse JSON từ', url, text);
                    alert('Lỗi server: ' + text.substring(0, 200));
                    throw new Error('Invalid JSON');
                }
            } catch (err) {
                console.error('Fetch error:', err);
                alert('Không thể kết nối đến máy chủ');
                throw err;
            }
        }

        // 1. Cập nhật trạng thái đơn hàng
        const statusSelect = document.getElementById('orderStatus');
        if (statusSelect) {
            console.log('Tìm thấy orderStatus, gắn event change');
            statusSelect.addEventListener('change', async function(e) {
                const orderId = this.dataset.orderId;
                const newStatus = this.value;
                const url = BASE_URL + '/admin/orders/updateStatus';
                try {
                    const data = await safeFetchJSON(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${orderId}&status=${newStatus}`
                    });
                    if (data.success) {
                        alert('Cập nhật thành công');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.error);
                    }
                } catch (err) {
                    console.error('Lỗi khi gọi updateStatus:', err);
                }
            });
        }

        // 2. Cập nhật ngày trả thực tế
        const updateBtn = document.getElementById('update_actual_return_btn');
        if (updateBtn) {
            updateBtn.addEventListener('click', async function() {
                const orderId = this.dataset.orderId;
                const actualDate = document.getElementById('actual_return_date_input').value;
                if (!actualDate) {
                    alert('Vui lòng chọn ngày trả thực tế.');
                    return;
                }
                const url = BASE_URL + '/admin/orders/updateActualReturn';
                const data = await safeFetchJSON(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `order_id=${orderId}&actual_return_date=${actualDate}`
                });
                if (data.success) {
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.error);
                }
            });
        }

        // 3. Xử lý tình trạng sản phẩm (good/damaged/lost)
        document.querySelectorAll('.status-after-return').forEach(select => {
            const originalValue = select.value;
            select.addEventListener('change', async function() {
                const detailId = this.dataset.detailId;
                const condition = this.value;
                if (!condition) return;
                let confirmMsg = 'Xác nhận tình trạng sản phẩm?';
                if (condition === 'good') confirmMsg = 'Xác nhận sản phẩm trả tốt? Hệ thống sẽ cộng lại số lượng vào kho.';
                if (condition === 'damaged') confirmMsg = 'Xác nhận sản phẩm bị hỏng? Tiền cọc sẽ bị khấu trừ.';
                if (condition === 'lost') confirmMsg = 'Xác nhận sản phẩm bị mất? Toàn bộ tiền cọc sẽ bị giữ.';
                if (!confirm(confirmMsg)) {
                    this.value = originalValue;
                    return;
                }
                const url = BASE_URL + '/admin/orders/processReturn';
                const data = await safeFetchJSON(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `detail_id=${detailId}&condition=${condition}&late_days=0`
                });
                if (data.success) {
                    alert('Cập nhật thành công');
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.error);
                    this.value = originalValue;
                }
            });
        });

        // 4. Xác nhận đã thu phí trễ
        const markLateFeeBtn = document.getElementById('markLateFeePaidBtn');
        if (markLateFeeBtn) {
            markLateFeeBtn.addEventListener('click', async function() {
                if (!confirm('Xác nhận đã thu phí trễ?')) return;
                const orderId = this.dataset.orderId;
                const url = BASE_URL + '/admin/orders/markLateFeePaid';
                const data = await safeFetchJSON(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `order_id=${orderId}`
                });
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.error);
                }
            });
        }

        // 5. Xử lý hoàn cọc
        const depositBtn = document.getElementById('processDepositBtn');
        if (depositBtn) {
            depositBtn.addEventListener('click', async function() {
                if (!confirm('Xác nhận hoàn tiền cọc?')) return;
                const orderId = this.dataset.orderId;
                const url = BASE_URL + '/admin/orders/processDeposit';
                const data = await safeFetchJSON(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `order_id=${orderId}`
                });
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.error);
                }
            });
        }
    });
</script>