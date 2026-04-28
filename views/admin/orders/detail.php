<?php 
$pageTitle = 'Chi tiết đơn hàng #' . ($order['id'] ?? ''); 
$activeMenu = 'orders';
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

    <!-- Grid 2 cột -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <!-- Thông tin khách hàng -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-primary-100">
            <div class="bg-gradient-to-r from-primary-700 to-primary-500 px-3 py-2">
                <h3 class="text-white font-semibold text-xs flex items-center gap-1.5">
                    <i class="fas fa-user-circle text-xs"></i> Thông tin khách hàng
                </h3>
            </div>
            <div class="p-3">
                <table class="w-full text-xs">
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500 w-28">Khách hàng</td><td class="py-1.5 font-semibold text-gray-800"><?php echo htmlspecialchars($order['user_name'] ?? 'N/A'); ?></td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Email</td><td class="py-1.5 text-gray-700"><?php echo htmlspecialchars($order['user_email'] ?? $order['email'] ?? 'N/A'); ?></td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Điện thoại</td><td class="py-1.5 text-gray-700"><?php echo htmlspecialchars($order['phone'] ?? 'Chưa cập nhật'); ?></td></tr>
                    <tr><td class="py-1.5 font-medium text-gray-500">Địa chỉ</td><td class="py-1.5 text-gray-700"><?php echo htmlspecialchars($order['address'] ?? 'Chưa cập nhật'); ?></td></tr>
                </table>
            </div>
        </div>

        <!-- Thông tin đơn hàng -->
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
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Ngày trả thực tế</td><td class="py-1.5"><?php echo $order['actual_return_date'] ? date('d/m/Y', strtotime($order['actual_return_date'])) : '<span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700">Chưa trả</span>'; ?></td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Tổng tiền</td><td class="py-1.5 font-bold text-primary-600"><?php echo number_format($order['discount_price'] ?? $order['total_price'] ?? 0, 0, ',', '.'); ?>đ</td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 font-medium text-gray-500">Tiền cọc</td><td class="py-1.5 text-gray-700"><?php echo number_format($order['total_deposit'] ?? 0, 0, ',', '.'); ?>đ</td></tr>
                    <tr><td class="py-1.5 font-medium text-gray-500">Phí trễ</td><td class="py-1.5 text-red-600"><?php echo number_format($order['late_fee'] ?? 0, 0, ',', '.'); ?>đ</td></tr>
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
                    <option value="pending" <?php echo ($order['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                    <option value="processing" <?php echo ($order['status'] ?? '') == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="renting" <?php echo ($order['status'] ?? '') == 'renting' ? 'selected' : ''; ?>>Đang thuê</option>
                    <option value="completed" <?php echo ($order['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Hoàn trả</option>
                    <option value="cancelled" <?php echo ($order['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
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
                $deposit_status = $order['deposit_status'] ?? 'pending';
                $deposit_badge_class = match($deposit_status) {
                    'refunded_full' => 'bg-green-100 text-green-700',
                    'pending_return' => 'bg-amber-100 text-amber-700',
                    'refunded_partial' => 'bg-blue-100 text-blue-700',
                    default => 'bg-amber-100 text-amber-700'
                };
                $deposit_labels = ['pending' => 'Chưa hoàn', 'pending_return' => 'Chờ hoàn cọc', 'refunded_full' => 'Đã hoàn toàn bộ', 'refunded_partial' => 'Hoàn một phần'];
                ?>
                <span class="inline-block px-2 py-1 rounded-full text-[11px] font-medium <?php echo $deposit_badge_class; ?>">
                    <?php echo $deposit_labels[$deposit_status] ?? 'Chưa hoàn'; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Sản phẩm đã thuê -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-4 border border-primary-100">
        <div class="bg-gradient-to-r from-primary-700 to-primary-500 px-3 py-2">
            <h3 class="text-white font-semibold text-xs flex items-center gap-1.5">
                <i class="fas fa-boxes text-xs"></i> Sản phẩm đã thuê
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Sản phẩm</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">SL</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Đơn giá</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Cọc/sp</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Thành tiền</th>
                        <th class="px-2 py-1.5 text-left font-semibold text-gray-600">Tình trạng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr class="border-t border-gray-100">
                        <td class="px-2 py-1.5 font-medium text-gray-800"><?php echo htmlspecialchars($item['product_name'] ?? 'N/A'); ?></td>
                        <td class="px-2 py-1.5 text-gray-700"><?php echo $item['quantity'] ?? 0; ?></td>
                        <td class="px-2 py-1.5 text-gray-700"><?php echo number_format($item['price'] ?? 0, 0, ',', '.'); ?>đ</td>
                        <td class="px-2 py-1.5 text-gray-700"><?php echo number_format($item['deposit_price'] ?? 0, 0, ',', '.'); ?>đ</td>
                        <td class="px-2 py-1.5 font-semibold text-primary-600"><?php echo number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 0, ',', '.'); ?>đ</td>
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
    <?php if (($order['status'] ?? '') == 'completed' && ($order['deposit_status'] ?? '') == 'pending_return'): ?>
    <div class="flex justify-end mt-3">
        <button id="processDepositBtn" class="px-4 py-1.5 bg-gradient-to-r from-emerald-600 to-green-500 text-white rounded-md hover:from-emerald-700 hover:to-green-600 transition-all duration-200 font-semibold text-xs flex items-center gap-1.5 shadow-sm" data-order-id="<?php echo $order['id']; ?>">
            <i class="fas fa-hand-holding-usd text-xs"></i> Hoàn cọc
        </button>
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
        border-color: #153d7e;
    }
    .status-after-return:focus {
        outline: none;
        border-color: #0c377b;
        ring: 2px solid #1752b1;
    }
</style>