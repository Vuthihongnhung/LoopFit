-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th5 31, 2026 lúc 04:28 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `loopfit`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `full_name`, `phone`, `address_line`, `is_default`, `created_at`) VALUES
(7, 15, 'Nguyễn A', '0926452387', 'mỹ lợi', 0, '2026-04-26 13:37:20'),
(17, 1, 'Vũ Thị Hồng Nhung', '0926452666', 'Linh Xuân', 0, '2026-05-05 21:16:07'),
(25, 22, 'Trần Thị Thanh Tuyền', '0967951028', 'Phường Linh Xuân, Thủ Đức', 1, '2026-05-23 17:41:48'),
(27, 15, 'Vũ Thị Hồng Nhung', '0982077481', 'Dĩ An, Bình Dương', 1, '2026-05-31 21:26:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bulk_promotions`
--

CREATE TABLE `bulk_promotions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Tên chương trình',
  `promotion_type` enum('percentage','fixed_price') DEFAULT 'percentage' COMMENT 'Loại khuyến mãi: percentage (%) hoặc fixed_price (đồng giá)',
  `discount_value` int(11) DEFAULT 0 COMMENT 'Giá trị giảm theo % (nếu percentage)',
  `fixed_price` decimal(10,2) DEFAULT 0.00 COMMENT 'Giá cố định (nếu fixed_price)',
  `scope_type` enum('all_products','category','specific_products') DEFAULT 'all_products' COMMENT 'Phạm vi áp dụng',
  `category_id` int(11) DEFAULT NULL COMMENT 'ID danh mục (nếu scope_type = category)',
  `start_date` datetime NOT NULL COMMENT 'Ngày bắt đầu',
  `end_date` datetime NOT NULL COMMENT 'Ngày kết thúc',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Trạng thái kích hoạt',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Quản lý chương trình khuyến mãi hàng loạt';

--
-- Đang đổ dữ liệu cho bảng `bulk_promotions`
--

INSERT INTO `bulk_promotions` (`id`, `name`, `promotion_type`, `discount_value`, `fixed_price`, `scope_type`, `category_id`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(13, 'HÈ', 'fixed_price', 10, 12000.00, 'all_products', NULL, '2026-05-29 20:52:00', '2026-06-05 20:52:00', 1, '2026-05-31 20:52:52', '2026-05-31 21:22:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bulk_promotion_categories`
--

CREATE TABLE `bulk_promotion_categories` (
  `id` int(11) NOT NULL,
  `bulk_promotion_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bulk_promotion_categories`
--

INSERT INTO `bulk_promotion_categories` (`id`, `bulk_promotion_id`, `category_id`) VALUES
(72, 9, 12),
(73, 12, 9),
(75, 13, 15);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bulk_promotion_items`
--

CREATE TABLE `bulk_promotion_items` (
  `id` int(11) NOT NULL,
  `bulk_promotion_id` int(11) NOT NULL COMMENT 'FK: Chương trình khuyến mãi',
  `product_id` int(11) DEFAULT NULL COMMENT 'FK: Sản phẩm (nếu scope_type = specific_products)',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Chi tiết sản phẩm áp dụng chương trình khuyến mãi';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `rent_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `original_price` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `parent_id` int(11) DEFAULT 0,
  `icon` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `icon`, `display_order`, `is_active`, `created_at`, `description`) VALUES
(1, 'Phong Cách', 'phong-cach', 0, NULL, 1, 1, '2026-04-14 07:16:32', NULL),
(2, 'Theo Dịp', 'theo-dip', 0, NULL, 2, 1, '2026-04-14 07:16:32', NULL),
(5, 'Đường Phố', 'duong-pho', 1, NULL, 1, 1, '2026-04-14 07:16:32', NULL),
(6, 'Tối Giản', 'toi-gian', 1, NULL, 2, 1, '2026-04-14 07:16:32', NULL),
(7, 'Hàn Quốc', 'han-quoc', 1, NULL, 3, 1, '2026-04-14 07:16:32', NULL),
(8, 'Năng Động', 'nang-dong', 1, NULL, 4, 1, '2026-04-14 07:16:32', NULL),
(9, 'Thanh Lịch', 'thanh-lich', 1, NULL, 5, 1, '2026-04-14 07:16:32', NULL),
(10, 'Cá Tính', 'ca-tinh', 1, NULL, 6, 1, '2026-04-14 07:16:32', NULL),
(11, 'Gợi Cảm', 'goi-cam', 1, NULL, 7, 1, '2026-04-14 07:16:32', NULL),
(12, 'Dễ Thương', 'de-thuong', 1, NULL, 8, 1, '2026-04-14 07:16:32', NULL),
(13, 'Đi Chơi', 'di-choi', 2, NULL, 1, 1, '2026-04-14 07:16:32', NULL),
(14, 'Hẹn Hò', 'hen-ho', 2, NULL, 2, 1, '2026-04-14 07:16:32', NULL),
(15, 'Dự Tiệc', 'du-tiec', 2, NULL, 3, 1, '2026-04-14 07:16:32', NULL),
(16, 'Du Lịch', 'du-lich', 2, NULL, 4, 1, '2026-04-14 07:16:32', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `session_id`, `sender_type`, `message`, `is_read`, `created_at`) VALUES
(1, 22, 'user_22', 'user', 'Chính sách trả trễ như thế nào?', 1, '2026-05-27 11:02:08'),
(2, 22, 'user_22', 'admin', 'Phí trả trễ là 10.000đ/ngày cho mỗi sản phẩm. Bạn nên trả đúng hẹn để tránh phát sinh phí. Liên hệ 1900 1234 nếu cần hỗ trợ.', 1, '2026-05-27 11:02:09'),
(3, 22, 'user_22', 'user', 'Làm thế nào để đặt hàng?', 1, '2026-05-27 11:02:30'),
(4, 22, 'user_22', 'admin', 'Bạn chọn sản phẩm, nhấn \"Thuê ngay\" hoặc \"Thêm vào giỏ\", sau đó vào giỏ hàng và thanh toán. Bạn sẽ nhận hàng trong 2-5 ngày.', 1, '2026-05-27 11:02:31'),
(5, 22, 'user_22', 'user', 'Chính sách trả trễ như thế nào?', 1, '2026-05-27 11:02:33'),
(6, 22, 'user_22', 'admin', 'Phí trả trễ là 10.000đ/ngày cho mỗi sản phẩm. Bạn nên trả đúng hẹn để tránh phát sinh phí. Liên hệ 1900 1234 nếu cần hỗ trợ.', 1, '2026-05-27 11:02:34'),
(7, 22, 'user_22', 'user', 'shop uiii', 1, '2026-05-27 11:02:39'),
(13, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'user', '❤️', 1, '2026-05-27 14:25:54'),
(14, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'admin', '😊', 1, '2026-05-27 14:57:51'),
(15, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'admin', '🎉', 1, '2026-05-27 14:57:54'),
(16, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'user', 'Làm sao để liên hệ hỗ trợ?', 1, '2026-05-27 14:58:02'),
(17, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'admin', 'Bạn có thể chat trực tiếp qua icon góc phải màn hình, gọi hotline 1900 1234 (8h-22h hàng ngày), hoặc email contact@loopfit.vn.', 1, '2026-05-27 14:58:03'),
(18, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'user', 'Có được thuê nhiều sản phẩm cùng lúc không?', 1, '2026-05-27 14:58:11'),
(19, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'admin', 'Có, bạn có thể thuê tối đa 5 sản phẩm cùng lúc. Với khách VIP (thuê trên 10 lần) có thể thuê tới 10 sản phẩm.', 1, '2026-05-27 14:58:12'),
(20, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'user', 'Chính sách đổi trả thế nào?', 1, '2026-05-27 14:58:16'),
(21, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'admin', 'Đổi trả trong vòng 7 ngày nếu sản phẩm lỗi hoặc sai size. Sản phẩm phải còn nguyên tem mác, không có dấu hiệu sử dụng.', 1, '2026-05-27 14:58:17'),
(22, NULL, 'guest_gd90jirvg4fes9cm2sv0fo3u5r', 'user', '👍', 1, '2026-05-27 14:58:31'),
(45, NULL, 'guest_g371sqp86vv2u9bopgsroafqmo', 'user', 'Thời gian giao hàng bao lâu?', 1, '2026-05-29 16:32:24'),
(46, NULL, 'guest_g371sqp86vv2u9bopgsroafqmo', 'admin', 'Giao hàng từ 2-5 ngày tùy khu vực. Nội thành Hà Nội/TP.HCM giao trong 1-2 ngày, các tỉnh khác 3-5 ngày.', 1, '2026-05-29 16:32:25'),
(66, 15, 'user_15', 'user', 'Làm thế nào để đổi size sản phẩm?', 1, '2026-05-31 13:32:21'),
(67, 15, 'user_15', 'admin', 'Bạn có thể đổi size trong vòng 24h kể từ khi nhận hàng. Liên hệ hotline 1900 1234 để được hỗ trợ đổi size miễn phí.', 1, '2026-05-31 13:32:22'),
(68, 15, 'user_15', 'admin', 'Xin chào', 1, '2026-05-31 13:32:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `keywords` text NOT NULL,
  `views` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`, `keywords`, `views`, `is_active`, `created_at`) VALUES
(1, 'Làm thế nào để đổi size sản phẩm?', 'Bạn có thể đổi size trong vòng 24h kể từ khi nhận hàng. Liên hệ hotline 1900 1234 để được hỗ trợ đổi size miễn phí.', 'đổi size,thay size,đổi kích cỡ', 0, 1, '2026-05-26 15:39:52'),
(2, 'Chính sách trả trễ như thế nào?', 'Phí trả trễ là 10.000đ/ngày cho mỗi sản phẩm. Bạn nên trả đúng hẹn để tránh phát sinh phí. Liên hệ 1900 1234 nếu cần hỗ trợ.', 'trả trễ,trả hàng trễ,phí trả trễ', 0, 1, '2026-05-26 15:39:52'),
(3, 'Các bước thuê sản phẩm?', 'Bước 1: Chọn sản phẩm. Bước 2: Thêm vào giỏ. Bước 3: Thanh toán. Bước 4: Nhận hàng. Bước 5: Trả hàng đúng hẹn. Mọi thắc mắc liên hệ contact@loopfit.vn', 'cách thuê,thuê như thế nào,hướng dẫn thuê', 0, 1, '2026-05-26 15:39:52'),
(4, 'Làm thế nào để đặt hàng?', 'Bạn chọn sản phẩm, nhấn \"Thuê ngay\" hoặc \"Thêm vào giỏ\", sau đó vào giỏ hàng và thanh toán. Bạn sẽ nhận hàng trong 2-5 ngày.', 'đặt hàng,mua hàng,order', 0, 1, '2026-05-27 07:55:27'),
(5, 'Thời gian giao hàng bao lâu?', 'Giao hàng từ 2-5 ngày tùy khu vực. Nội thành Hà Nội/TP.HCM giao trong 1-2 ngày, các tỉnh khác 3-5 ngày.', 'giao hàng,ship,thời gian giao', 0, 1, '2026-05-27 07:55:27'),
(6, 'Chính sách đổi trả thế nào?', 'Đổi trả trong vòng 7 ngày nếu sản phẩm lỗi hoặc sai size. Sản phẩm phải còn nguyên tem mác, không có dấu hiệu sử dụng.', 'đổi trả,chính sách đổi trả', 0, 1, '2026-05-27 07:55:27'),
(7, 'Thanh toán bằng những phương thức nào?', 'Chấp nhận thanh toán qua VNPay (thẻ ATM, Visa, Master), chuyển khoản ngân hàng, hoặc tiền mặt khi nhận hàng (COD).', 'thanh toán,phương thức thanh toán', 0, 1, '2026-05-27 07:55:27'),
(8, 'Làm sao để hủy đơn hàng?', 'Liên hệ ngay hotline 1900 1234 trong vòng 1 giờ sau khi đặt để hủy. Sau 1 giờ, đơn hàng đã được xử lý không thể hủy.', 'hủy đơn,hủy order', 0, 1, '2026-05-27 07:55:27'),
(9, 'Có được thuê nhiều sản phẩm cùng lúc không?', 'Có, bạn có thể thuê tối đa 5 sản phẩm cùng lúc. Với khách VIP (thuê trên 10 lần) có thể thuê tới 10 sản phẩm.', 'thuê nhiều,nhiều sản phẩm', 0, 1, '2026-05-27 07:55:27'),
(10, 'Làm sao để liên hệ hỗ trợ?', 'Bạn có thể chat trực tiếp qua icon góc phải màn hình, gọi hotline 1900 1234 (8h-22h hàng ngày), hoặc email contact@loopfit.vn.', 'hỗ trợ,liên hệ', 0, 1, '2026-05-27 07:55:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID người nhận (NULL nếu gửi cho nhiều người)',
  `user_type` enum('admin','customer','all_admins','all_customers','specific') NOT NULL DEFAULT 'specific',
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề thông báo',
  `message` text NOT NULL COMMENT 'Nội dung thông báo',
  `type` varchar(50) NOT NULL COMMENT 'Loại: new_order, new_message, low_stock, overdue_return, order_confirmed, order_completed, support_reply, return_reminder, order_created',
  `reference_id` int(11) DEFAULT NULL COMMENT 'ID tham chiếu (order_id, product_id, message_id)',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'order, product, message',
  `link_url` varchar(500) DEFAULT NULL COMMENT 'Đường dẫn chuyển hướng khi click',
  `icon` varchar(50) DEFAULT NULL COMMENT 'Icon thông báo',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: chưa đọc, 1: đã đọc',
  `read_at` datetime DEFAULT NULL COMMENT 'Thời gian đọc',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL COMMENT 'Thời gian hết hạn (30 ngày sau khi tạo)',
  `priority` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: thường, 1: quan trọng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `user_type`, `title`, `message`, `type`, `reference_id`, `reference_type`, `link_url`, `icon`, `is_read`, `read_at`, `created_at`, `expires_at`, `priority`) VALUES
(58, 0, 'all_admins', '📦 Đơn hàng mới vừa tạo', 'Đơn hàng #148 đã được tạo và đang chờ xử lý.', 'new_order', 148, 'order', 'http://localhost:8080/LoopFit/public/index.php?url=admin/orders/detail&id=148', 'shopping-cart', 1, '2026-05-31 20:38:47', '2026-05-31 20:24:14', '2026-06-30 15:24:14', 1),
(59, 15, 'customer', '✅ Đơn thuê đã được tạo', 'Đơn hàng #148 đã được tạo thành công. Vui lòng kiểm tra chi tiết đơn hàng.', 'order_created', 148, 'order', 'http://localhost:8080/LoopFit/public/order/148', 'shopping-cart', 1, '2026-05-31 20:24:33', '2026-05-31 20:24:14', '2026-06-30 15:24:14', 1),
(60, 15, 'customer', '⚠️ Phí trễ phát sinh', 'Đơn hàng #148 phát sinh phí trễ 50.000đ. Vui lòng thanh toán để tiếp tục xử lý đơn.', 'late_fee_incurred', 148, 'order', 'http://localhost:8080/LoopFit/public/order/148', 'exclamation-circle', 1, '2026-05-31 20:27:13', '2026-05-31 20:26:56', '2026-06-30 15:26:56', 1),
(61, 15, 'customer', '🎉 Đơn thuê đã hoàn tất', 'Đơn hàng #148 đã hoàn tất. Cảm ơn bạn đã sử dụng dịch vụ.', 'order_completed', 148, 'order', 'http://localhost:8080/LoopFit/public/order/148', 'gift', 1, '2026-05-31 20:28:22', '2026-05-31 20:27:40', '2026-06-30 15:27:40', 1),
(62, 15, 'customer', '💰 Hoàn cọc toàn bộ', 'Đơn hàng #148 đã được xử lý hoàn cọc. Số tiền hoàn: 60.000đ.', 'deposit_refund', 148, 'order', 'http://localhost:8080/LoopFit/public/order/148', 'wallet', 1, '2026-05-31 20:28:22', '2026-05-31 20:27:54', '2026-06-30 15:27:54', 1),
(63, 0, 'all_admins', '📦 Đơn hàng mới vừa tạo', 'Đơn hàng #149 đã được tạo và đang chờ xử lý.', 'new_order', 149, 'order', 'http://localhost:8080/LoopFit/public/index.php?url=admin/orders/detail&id=149', 'shopping-cart', 1, '2026-05-31 20:38:47', '2026-05-31 20:29:52', '2026-06-30 15:29:52', 1),
(64, 15, 'customer', '✅ Đơn thuê đã được tạo', 'Đơn hàng #149 đã được tạo thành công. Vui lòng kiểm tra chi tiết đơn hàng.', 'order_created', 149, 'order', 'http://localhost:8080/LoopFit/public/order/149', 'shopping-cart', 1, '2026-05-31 20:32:11', '2026-05-31 20:29:52', '2026-06-30 15:29:52', 1),
(65, 15, 'customer', '💳 Thanh toán chuyển khoản xác nhận', 'Yêu cầu thanh toán của bạn cho đơn #149 đã được admin xác nhận thành công.', 'payment_confirmed', 149, 'order', 'http://localhost:8080/LoopFit/public/order/149', 'credit-card', 1, '2026-05-31 20:32:11', '2026-05-31 20:30:49', '2026-06-30 15:30:49', 1),
(66, 15, 'customer', '🎉 Đơn thuê đã hoàn tất', 'Đơn hàng #149 đã hoàn tất. Cảm ơn bạn đã sử dụng dịch vụ.', 'order_completed', 149, 'order', 'http://localhost:8080/LoopFit/public/order/149', 'gift', 1, '2026-05-31 20:32:11', '2026-05-31 20:31:00', '2026-06-30 15:31:00', 1),
(67, 15, 'customer', '💰 Hoàn cọc một phần', 'Đơn hàng #149 đã được xử lý hoàn cọc. Số tiền hoàn: 42.500đ.', 'deposit_refund', 149, 'order', 'http://localhost:8080/LoopFit/public/order/149', 'wallet', 1, '2026-05-31 20:32:11', '2026-05-31 20:31:14', '2026-06-30 15:31:14', 1),
(68, 0, 'all_admins', '🛎️ Hỗ trợ mới từ khách hàng', 'Một khách hàng đã gửi tin nhắn hỗ trợ trong phiên user_15. Vui lòng kiểm tra.', 'support_message', NULL, NULL, 'http://localhost:8080/LoopFit/public/index.php?url=admin/chat&session_id=user_15', 'comments', 1, '2026-05-31 20:38:47', '2026-05-31 20:32:21', '2026-06-30 15:32:21', 1),
(69, 15, 'specific', '📩 Admin đã trả lời bạn', 'Admin đã gửi phản hồi trong cuộc trò chuyện của bạn. Vui lòng kiểm tra tin nhắn.', 'support_reply', NULL, NULL, 'http://localhost:8080/LoopFit/public/chat', 'reply', 1, '2026-05-31 20:32:43', '2026-05-31 20:32:35', '2026-06-30 15:32:35', 1),
(70, 0, 'all_admins', '📦 Đơn hàng mới vừa tạo', 'Đơn hàng #150 đã được tạo và đang chờ xử lý.', 'new_order', 150, 'order', 'http://localhost:8080/LoopFit/public/index.php?url=admin/orders/detail&id=150', 'shopping-cart', 0, NULL, '2026-05-31 20:55:03', '2026-06-30 15:55:03', 1),
(71, 15, 'customer', '✅ Đơn thuê đã được tạo', 'Đơn hàng #150 đã được tạo thành công. Vui lòng kiểm tra chi tiết đơn hàng.', 'order_created', 150, 'order', 'http://localhost:8080/LoopFit/public/order/150', 'shopping-cart', 0, NULL, '2026-05-31 20:55:03', '2026-06-30 15:55:03', 1),
(72, 0, 'all_admins', '📦 Đơn hàng mới vừa tạo', 'Đơn hàng #151 đã được tạo và đang chờ xử lý.', 'new_order', 151, 'order', 'http://localhost:8080/LoopFit/public/index.php?url=admin/orders/detail&id=151', 'shopping-cart', 0, NULL, '2026-05-31 21:21:12', '2026-06-30 16:21:12', 1),
(73, 15, 'customer', '✅ Đơn thuê đã được tạo', 'Đơn hàng #151 đã được tạo thành công. Vui lòng kiểm tra chi tiết đơn hàng.', 'order_created', 151, 'order', 'http://localhost:8080/LoopFit/public/order/151', 'shopping-cart', 0, NULL, '2026-05-31 21:21:12', '2026-06-30 16:21:12', 1),
(74, 0, 'all_admins', '📦 Đơn hàng mới vừa tạo', 'Đơn hàng #152 đã được tạo và đang chờ xử lý.', 'new_order', 152, 'order', 'http://localhost:8080/LoopFit/public/index.php?url=admin/orders/detail&id=152', 'shopping-cart', 0, NULL, '2026-05-31 21:21:36', '2026-06-30 16:21:36', 1),
(75, 15, 'customer', '✅ Đơn thuê đã được tạo', 'Đơn hàng #152 đã được tạo thành công. Vui lòng kiểm tra chi tiết đơn hàng.', 'order_created', 152, 'order', 'http://localhost:8080/LoopFit/public/order/152', 'shopping-cart', 0, NULL, '2026-05-31 21:21:36', '2026-06-30 16:21:36', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rent_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `discount_price` decimal(10,2) DEFAULT 0.00,
  `promotion_id` int(11) DEFAULT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `deposit_amount` decimal(10,2) DEFAULT 0.00,
  `deposit_status` enum('pending','refunded','forfeited','pending_return','refunded_full','refunded_partial','none') DEFAULT 'pending',
  `payment_method` enum('cash','transfer') DEFAULT 'cash',
  `payment_status` enum('unpaid','paid','pending','pending_verification','failed') DEFAULT 'unpaid',
  `payment_expiry` datetime DEFAULT NULL,
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `late_fee_status` enum('unpaid','pending_verification','paid') DEFAULT 'unpaid',
  `late_fee_paid_at` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `deposit_refunded_amount` decimal(10,2) DEFAULT 0.00,
  `deposit_refunded_at` datetime DEFAULT NULL,
  `payment_verified_at` datetime DEFAULT NULL,
  `final_total` decimal(10,2) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `reminded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `rent_date`, `return_date`, `actual_return_date`, `total_price`, `discount_price`, `promotion_id`, `shipping_fee`, `deposit_amount`, `deposit_status`, `payment_method`, `payment_status`, `payment_expiry`, `late_fee`, `late_fee_status`, `late_fee_paid_at`, `status`, `created_at`, `deposit_refunded_amount`, `deposit_refunded_at`, `payment_verified_at`, `final_total`, `address_id`, `notes`, `reminded_at`) VALUES
(1, 15, '2026-05-29', '2026-05-30', '2026-05-30', 226100.00, 0.00, NULL, 30000.00, 60000.00, 'refunded_partial', 'cash', 'paid', NULL, 0.00, 'unpaid', NULL, 'completed', '2023-02-03 10:02:55', 30000.00, '2026-05-30 10:03:55', '2026-05-30 10:03:03', 316100.00, 7, '', NULL),
(2, 15, '2026-05-27', '2026-05-29', '2026-05-30', 339150.00, 0.00, NULL, 30000.00, 60000.00, 'refunded_full', 'cash', 'paid', NULL, 50000.00, 'paid', '2026-05-30 20:09:55', 'completed', '2025-02-23 10:04:56', 60000.00, '2026-05-30 20:10:02', '2026-05-30 10:05:01', 429150.00, 7, '', NULL),
(3, 15, '2026-05-27', '2026-05-29', '2026-05-30', 297000.00, 0.00, NULL, 30000.00, 50000.00, 'pending_return', 'cash', 'paid', NULL, 0.00, 'unpaid', NULL, 'completed', '2025-03-24 11:50:15', 0.00, NULL, '2026-05-30 13:19:34', 377000.00, 7, '', NULL),
(4, 15, '2026-05-27', '2026-05-28', '2026-05-30', 218000.00, 0.00, NULL, 30000.00, 60000.00, 'pending_return', 'cash', 'paid', NULL, 0.00, 'unpaid', NULL, 'completed', '2025-03-30 11:53:30', 0.00, NULL, '2026-05-30 13:19:36', 308000.00, 7, '', NULL),
(5, 15, '2026-05-28', '2026-05-29', '2026-05-30', 150100.00, 0.00, NULL, 30000.00, 40000.00, 'forfeited', 'cash', 'paid', NULL, 50000.00, 'paid', '2026-05-30 12:18:01', 'completed', '2025-03-30 11:54:48', 0.00, '2026-05-30 12:18:09', '2026-05-30 13:19:48', 220100.00, 7, '', NULL),
(6, 15, '2026-05-27', '2026-05-28', '2026-05-30', 238000.00, 0.00, NULL, 30000.00, 60000.00, 'pending_return', 'cash', 'paid', NULL, 0.00, 'unpaid', NULL, 'completed', '2026-03-12 11:55:29', 0.00, NULL, '2026-05-30 13:19:46', 328000.00, 7, '', NULL),
(7, 15, '2026-05-28', '2026-05-29', '2026-05-30', 150100.00, 0.00, NULL, 30000.00, 40000.00, 'refunded_full', 'cash', 'paid', NULL, 50000.00, 'paid', '2026-05-30 12:13:48', 'completed', '2026-04-14 12:11:41', 40000.00, '2026-05-30 12:15:26', '2026-05-30 13:19:42', 220100.00, 7, '', NULL),
(8, 15, '2026-05-28', '2026-05-29', '2026-05-30', 264100.00, 0.00, NULL, 30000.00, 70000.00, 'pending_return', 'transfer', 'paid', '2026-05-30 07:44:57', 50000.00, 'unpaid', NULL, 'completed', '2026-04-17 12:29:57', 0.00, NULL, '2026-05-30 12:30:20', 364100.00, 7, '', NULL),
(9, 15, '2026-05-26', '2026-05-28', '2026-05-30', 357000.00, 0.00, NULL, 30000.00, 60000.00, 'pending_return', 'cash', 'paid', NULL, 50000.00, 'unpaid', '2026-05-30 12:36:59', 'completed', '2026-04-20 12:32:33', 0.00, NULL, '2026-05-30 13:19:40', 447000.00, 7, '', NULL),
(10, 15, '2026-05-28', '2026-05-29', '2026-05-30', 169100.00, 0.00, NULL, 30000.00, 50000.00, 'refunded_partial', 'cash', 'paid', NULL, 50000.00, 'paid', '2026-05-30 12:41:56', 'completed', '2026-05-03 12:39:06', 25000.00, '2026-05-30 12:42:04', '2026-05-30 12:39:23', 249100.00, 7, '', NULL),
(11, 15, '2026-05-26', '2026-05-28', '2026-05-30', 225150.00, 0.00, NULL, 30000.00, 40000.00, 'pending_return', 'cash', 'paid', NULL, 50000.00, 'unpaid', '2026-05-30 12:43:05', 'completed', '2026-05-03 12:42:24', 0.00, NULL, '2026-05-30 13:19:44', 295150.00, 7, '', NULL),
(12, 15, '2026-05-26', '2026-05-27', '2026-05-29', 218000.00, 15000.00, 14, 30000.00, 60000.00, 'pending_return', 'transfer', 'paid', '2026-05-30 08:05:07', 100000.00, 'unpaid', '2026-05-30 12:50:59', 'completed', '2026-05-05 12:50:07', 0.00, NULL, '2026-05-30 12:50:16', 293000.00, 7, '', NULL),
(13, 15, '2026-05-26', '2026-05-28', '2026-05-29', 297000.00, 0.00, NULL, 30000.00, 50000.00, 'pending_return', 'cash', 'paid', NULL, 50000.00, 'unpaid', '2026-05-30 12:58:20', 'completed', '2026-05-05 12:57:40', 0.00, NULL, '2026-05-30 13:19:37', 377000.00, 7, '', NULL),
(14, 15, '2026-05-25', '2026-05-26', '2026-05-29', 238000.00, 0.00, NULL, 30000.00, 60000.00, 'pending_return', 'cash', 'paid', NULL, 150000.00, 'unpaid', '2026-05-30 13:03:54', 'completed', '2026-05-12 13:03:14', 0.00, NULL, '2026-05-30 13:19:32', 328000.00, 7, '', NULL),
(15, 15, '2026-05-25', '2026-05-27', '2026-05-29', 253650.00, 0.00, NULL, 30000.00, 50000.00, 'pending_return', 'cash', 'paid', NULL, 100000.00, 'paid', '2026-05-30 13:08:47', 'completed', '2026-05-13 13:07:52', 25000.00, '2026-05-30 13:09:03', '2026-05-30 13:19:30', 333650.00, 7, '', NULL),
(16, 15, '2026-05-26', '2026-05-27', '2026-05-29', 232200.00, 0.00, NULL, 30000.00, 65000.00, 'refunded_partial', 'cash', 'paid', NULL, 100000.00, 'paid', '2026-05-30 13:14:02', 'completed', '2026-05-13 13:12:55', 32500.00, '2026-05-30 13:14:20', '2026-05-30 13:15:45', 327200.00, 7, '', NULL),
(138, 15, '2026-05-27', '2026-05-28', NULL, 169100.00, 0.00, NULL, 30000.00, 50000.00, 'pending', 'cash', 'paid', NULL, 0.00, 'unpaid', NULL, 'confirmed', '2026-05-30 13:40:08', 0.00, NULL, '2026-05-30 15:03:25', 249100.00, 7, '', NULL),
(139, 15, '2026-05-26', '2026-05-28', '2026-05-29', 297000.00, 0.00, NULL, 30000.00, 50000.00, 'refunded_partial', 'cash', 'paid', NULL, 50000.00, 'paid', '2026-05-30 14:40:01', 'completed', '2026-05-30 14:39:16', 25000.00, '2026-05-30 14:40:21', '2026-05-30 14:48:44', 377000.00, 7, '', NULL),
(140, 15, '2026-05-25', '2026-05-27', NULL, 240300.00, 15000.00, 14, 30000.00, 50000.00, 'pending', 'cash', 'unpaid', NULL, 0.00, 'unpaid', NULL, 'pending', '2026-05-30 15:58:33', 0.00, NULL, NULL, 305300.00, 7, '', NULL),
(141, 15, '2026-05-27', '2026-05-29', NULL, 180000.00, 0.00, NULL, 30000.00, 50000.00, 'pending', 'cash', 'unpaid', NULL, 0.00, 'unpaid', NULL, 'confirmed', '2026-05-30 16:18:08', 0.00, NULL, NULL, 260000.00, 7, '', NULL),
(142, 15, '2026-05-27', '2026-05-28', '2026-05-30', 120000.00, 0.00, NULL, 30000.00, 40000.00, 'pending', 'cash', 'unpaid', NULL, 100000.00, 'unpaid', NULL, 'returning', '2026-05-30 19:50:10', 0.00, NULL, NULL, 190000.00, 7, '', NULL),
(143, 15, '2026-05-26', '2026-05-28', NULL, 180000.00, 0.00, NULL, 30000.00, 55000.00, 'pending', 'cash', 'unpaid', NULL, 0.00, 'unpaid', NULL, 'pending', '2026-05-30 20:07:19', 0.00, NULL, NULL, 265000.00, 7, '', NULL),
(144, 15, '2026-05-27', '2026-05-28', NULL, 120000.00, 15000.00, 14, 30000.00, 65000.00, 'pending', 'transfer', 'paid', '2026-05-30 15:23:05', 0.00, 'unpaid', NULL, 'cancelled', '2026-05-30 20:08:05', 0.00, NULL, '2026-05-30 20:08:22', 200000.00, 7, '', NULL),
(145, 15, '2026-05-28', '2026-05-29', '2026-05-29', 496000.00, 0.00, NULL, 0.00, 125000.00, 'refunded_partial', 'cash', 'paid', NULL, 0.00, 'paid', NULL, 'completed', '2026-05-30 20:45:58', 92500.00, '2026-05-30 20:47:10', '2026-05-30 20:46:17', 621000.00, 7, '', NULL),
(146, 15, '2026-05-28', '2026-05-29', '2026-05-29', 238000.00, 0.00, NULL, 30000.00, 60000.00, 'refunded_partial', 'cash', 'unpaid', NULL, 0.00, 'paid', NULL, 'completed', '2026-05-30 20:52:38', 30000.00, '2026-05-30 20:53:34', NULL, 328000.00, 7, '', NULL),
(147, 15, '2026-05-28', '2026-05-29', '2026-05-29', 238000.00, 0.00, NULL, 30000.00, 60000.00, 'refunded_partial', 'cash', 'unpaid', NULL, 0.00, 'paid', NULL, 'completed', '2026-05-30 21:04:36', 30000.00, '2026-05-30 21:05:21', NULL, 328000.00, 7, '', NULL),
(148, 15, '2026-05-28', '2026-05-29', '2026-05-30', 238000.00, 15000.00, 14, 30000.00, 60000.00, 'refunded_full', 'transfer', 'paid', '2026-05-31 15:39:14', 50000.00, 'paid', '2026-05-31 20:27:30', 'completed', '2026-05-31 20:24:14', 60000.00, '2026-05-31 20:27:54', '2026-05-31 20:26:01', 313000.00, 7, 'Giao nhanh', NULL),
(149, 15, '2026-05-29', '2026-05-31', '2026-05-31', 507000.00, 0.00, NULL, 0.00, 85000.00, 'refunded_partial', 'cash', 'paid', NULL, 0.00, 'paid', NULL, 'completed', '2026-05-31 20:29:52', 42500.00, '2026-05-31 20:31:14', '2026-05-31 20:30:49', 592000.00, 7, '', NULL),
(150, 15, '2026-05-29', '2026-06-01', NULL, 48000.00, 0.00, NULL, 30000.00, 75000.00, 'pending', 'cash', 'unpaid', NULL, 0.00, 'unpaid', NULL, 'pending', '2026-05-31 20:55:03', 0.00, NULL, NULL, 153000.00, 7, '', NULL),
(151, 15, '2026-06-01', '2026-06-02', NULL, 169100.00, 15000.00, 14, 30000.00, 50000.00, 'pending', 'cash', 'unpaid', NULL, 0.00, 'unpaid', NULL, 'pending', '2026-05-31 21:21:12', 0.00, NULL, NULL, 234100.00, 7, '', NULL),
(152, 15, '2026-06-01', '2026-06-03', NULL, 36000.00, 0.00, NULL, 30000.00, 93000.00, 'pending', 'cash', 'unpaid', NULL, 0.00, 'unpaid', NULL, 'pending', '2026-05-31 21:21:36', 0.00, NULL, NULL, 159000.00, 7, '', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `deposit_per_item` decimal(10,2) DEFAULT 0.00,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `rental_start` date DEFAULT NULL,
  `rental_end` date DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL,
  `status_after_return` enum('good','damaged','lost') DEFAULT 'good'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`, `deposit_per_item`, `color`, `size`, `rental_start`, `rental_end`, `subtotal`, `status_after_return`) VALUES
(1, 1, 159, 1, 113050.00, 60000.00, 'Xanh', 'S', '2026-05-29', '2026-05-30', 226100, 'damaged'),
(2, 2, 159, 1, 113050.00, 60000.00, 'Xanh', 'M', '2026-05-27', '2026-05-29', 339150, 'good'),
(3, 3, 112, 1, 99000.00, 50000.00, 'Vàng', 'S', '2026-05-27', '2026-05-29', 297000, 'good'),
(4, 4, 106, 1, 109000.00, 60000.00, 'Nâu', 'S', '2026-05-27', '2026-05-28', 218000, 'good'),
(5, 5, 117, 1, 75050.00, 40000.00, 'Đen', 'L', '2026-05-28', '2026-05-29', 150100, 'lost'),
(6, 6, 116, 1, 119000.00, 60000.00, 'Đỏ', 'L', '2026-05-27', '2026-05-28', 238000, 'good'),
(7, 7, 117, 1, 75050.00, 40000.00, 'Đen', 'L', '2026-05-28', '2026-05-29', 150100, 'good'),
(8, 8, 115, 1, 132050.00, 70000.00, 'Nâu', 'XL', '2026-05-28', '2026-05-29', 264100, 'good'),
(9, 9, 116, 1, 119000.00, 60000.00, 'Đỏ', 'L', '2026-05-26', '2026-05-28', 357000, 'damaged'),
(10, 10, 122, 1, 84550.00, 50000.00, 'Xanh', 'L', '2026-05-28', '2026-05-29', 169100, 'damaged'),
(11, 11, 117, 1, 75050.00, 40000.00, 'Đen', 'L', '2026-05-26', '2026-05-28', 225150, 'damaged'),
(12, 12, 106, 1, 109000.00, 60000.00, 'Hồng', 'S', '2026-05-26', '2026-05-27', 218000, 'damaged'),
(13, 13, 142, 1, 99000.00, 50000.00, 'Trắng', 'M', '2026-05-26', '2026-05-28', 297000, 'damaged'),
(14, 14, 116, 1, 119000.00, 60000.00, 'Đỏ', 'L', '2026-05-25', '2026-05-26', 238000, 'damaged'),
(15, 15, 122, 1, 84550.00, 50000.00, 'Xanh', 'L', '2026-05-25', '2026-05-27', 253650, 'damaged'),
(16, 16, 98, 1, 116100.00, 65000.00, 'Trắng', 'M', '2026-05-26', '2026-05-27', 232200, 'damaged'),
(123, 138, 122, 1, 84550.00, 50000.00, 'Đen', 'S', '2026-05-27', '2026-05-28', 169100, 'good'),
(124, 139, 112, 1, 99000.00, 50000.00, 'Vàng', 'L', '2026-05-26', '2026-05-28', 297000, 'damaged'),
(125, 140, 119, 1, 80100.00, 50000.00, 'Xanh', 'M', '2026-05-25', '2026-05-27', 240300, 'good'),
(126, 141, 124, 1, 60000.00, 50000.00, 'Đen', 'M', '2026-05-27', '2026-05-29', 180000, 'good'),
(127, 142, 117, 1, 60000.00, 40000.00, 'Đen', 'L', '2026-05-27', '2026-05-28', 120000, 'good'),
(128, 143, 120, 1, 60000.00, 55000.00, 'Nâu', 'L', '2026-05-26', '2026-05-28', 180000, 'good'),
(129, 144, 98, 1, 60000.00, 65000.00, 'Trắng', 'M', '2026-05-27', '2026-05-28', 120000, 'good'),
(130, 145, 159, 1, 119000.00, 60000.00, 'Xanh', 'S', '2026-05-28', '2026-05-29', 238000, 'good'),
(131, 145, 158, 1, 129000.00, 65000.00, 'Hồng', 'S', '2026-05-28', '2026-05-29', 258000, 'damaged'),
(132, 146, 159, 1, 119000.00, 60000.00, 'Xanh', 'S', '2026-05-28', '2026-05-29', 238000, 'damaged'),
(133, 147, 159, 1, 119000.00, 60000.00, 'Xanh', 'S', '2026-05-28', '2026-05-29', 238000, 'damaged'),
(134, 148, 159, 1, 119000.00, 60000.00, 'Xanh', 'S', '2026-05-28', '2026-05-29', 238000, 'good'),
(135, 149, 105, 1, 169000.00, 85000.00, 'Trắng', 'M', '2026-05-29', '2026-05-31', 507000, 'damaged'),
(136, 150, 153, 1, 12000.00, 75000.00, 'Xanh', 'S', '2026-05-29', '2026-06-01', 48000, 'good'),
(137, 151, 122, 1, 84550.00, 50000.00, 'Xanh', 'L', '2026-06-01', '2026-06-02', 169100, 'good'),
(138, 152, 152, 1, 12000.00, 93000.00, 'Vàng', 'S', '2026-06-01', '2026-06-03', 36000, 'good');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `amount`, `method`, `status`) VALUES
(1, 1, 316100.00, 'cash', 'completed'),
(2, 2, 429150.00, 'cash', 'completed'),
(3, 8, 364100.00, 'transfer', 'completed'),
(4, 10, 249100.00, 'cash', 'completed'),
(5, 12, 293000.00, 'transfer', 'completed'),
(6, 16, 327200.00, 'cash', 'completed'),
(7, 15, 333650.00, 'cash', 'completed'),
(8, 14, 328000.00, 'cash', 'completed'),
(9, 3, 377000.00, 'cash', 'completed'),
(10, 4, 308000.00, 'cash', 'completed'),
(11, 13, 377000.00, 'cash', 'completed'),
(12, 9, 447000.00, 'cash', 'completed'),
(13, 7, 220100.00, 'cash', 'completed'),
(14, 11, 295150.00, 'cash', 'completed'),
(15, 6, 328000.00, 'cash', 'completed'),
(16, 5, 220100.00, 'cash', 'completed'),
(17, 139, 377000.00, 'cash', 'completed'),
(18, 138, 249100.00, 'cash', 'completed'),
(19, 144, 200000.00, 'transfer', 'completed'),
(20, 145, 621000.00, 'cash', 'completed'),
(21, 148, 313000.00, 'transfer', 'completed'),
(22, 149, 592000.00, 'cash', 'completed');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `discount_percent` int(11) DEFAULT 0,
  `deposit_price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(2000) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `rented_count` int(11) DEFAULT 0,
  `rented_quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `gender` enum('male','female','unisex') NOT NULL DEFAULT 'unisex'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `discount_percent`, `deposit_price`, `description`, `image`, `stock`, `category_id`, `category`, `rented_count`, `rented_quantity`, `created_at`, `gender`) VALUES
(96, 'Set Áo Ruffled & Quần Jean – Đỏ', 139000.00, 8, 69500.00, '- Set đồ gồm:\r\n\r\nÁo voan nhún không tay, phối thắt lưng nâu tạo điểm nhấn vòng eo.\r\n\r\nQuần jeans ống rộng xanh đậm, mang lại sự phóng khoáng và thoải mái.\r\n\r\nPhụ kiện: túi cói nhỏ xinh, dễ phối trong nhiều hoàn cảnh.\r\n\r\n- Phong cách:\r\n\r\nĐỏ: Cá tính street style, nổi bật và mạnh mẽ.\r\n\r\nXanh rêu: Trẻ trung dạo phố, hiện đại và năng động.\r\n\r\n- Màu sắc chủ đạo:\r\n\r\nĐỏ rực rỡ, thu hút ánh nhìn.\r\n\r\nXanh rêu dịu mắt, mang hơi hướng thiên nhiên.\r\n\r\n- Hoàn cảnh sử dụng:\r\n\r\nDạo phố, đi chơi cùng bạn bè.\r\n\r\nTham dự sự kiện ngoài trời, picnic hoặc du lịch.\r\n\r\nOutfit lý tưởng cho những buổi chụp ảnh thời trang, tạo phong cách riêng biệt.', '1778045487_69fad22f0c33a_set-ao-ruffled-quan-jean-do-4.jpg,1778045487_69fad22f0c5a5_set-ao-ruffled-quan-jean-do-3.jpg,1778045487_69fad22f10fd0_set-ao-ruffled-quan-jean-do-2.jpg,1778045487_69fad22f1117f_set-ao-ruffled-quan-jean-do-1.jpg', 16, 5, 'Phong Cách', 24, 0, '2026-05-06 12:31:33', 'female'),
(97, 'Set Áo Peplum Da Báo & Quần Jeans Ống Loe – Blue Leopard Streetwear Style', 129000.00, 0, 65000.00, 'Set đồ là sự giao thoa giữa nét quyến rũ cổ điển và tinh thần đường phố hiện đại. Với điểm nhấn là họa tiết da báo được cách tân trên nền tông xanh, kết hợp cùng form dáng quần ống loe tôn dáng, bộ trang phục mang đến diện mạo thời thượng, cá tính và đầy tự tin.\r\n\r\nChất liệu: Áo thun co giãn cao cấp với bề mặt vải mịn, in họa tiết sắc nét; quần denim wash mềm, có độ đứng form lý tưởng để giữ dáng ống loe; phụ kiện túi da và mũ kaki bền bỉ.\r\n\r\nMàu sắc: Gồm 2 phiên bản màu độc đáo:\r\n\r\nXanh Đậm (Deep Blue): Họa tiết da báo phối xanh thẫm, tạo cảm giác bí ẩn, sắc sảo và cực kỳ \"cool\" khi xuống phố ban đêm.\r\n\r\nXanh Nhạt (Light Blue): Họa tiết da báo phối xanh pastel/mint, mang lại vẻ ngoài tươi mới, trẻ trung và bắt sáng tốt khi chụp ảnh ngoài trời.\r\n\r\nPhong cách: Streetwear năng động, cá tính nhưng vẫn giữ được nét thanh thoát nhờ thiết kế peplum.\r\n\r\nPhù hợp: Dạo phố, đi cà phê, tham gia các buổi hangout cùng bạn bè hoặc chụp ảnh thời trang Urban.\r\n\r\nĐiểm nhấn: Thiết kế áo peplum không tay giúp tôn vòng eo, quần jeans ống loe cạp cao tạo hiệu ứng kéo dài chân tối đa, kết hợp mũ lưỡi trai thêu nổi tạo nên một tổng thể streetwear hoàn chỉnh mà không cần quá cầu kỳ.', '1778046015_69fad43f1dc1b_set-ao-peplum-da-bao-quan-jeans-ong-loe-blue-leopard-streetwear-style-3.jpg,1778046015_69fad43f1dd7b_set-ao-peplum-da-bao-quan-jeans-ong-loe-blue-leopard-streetwear-style-2.jpg,1778046015_69fad43f1de30_set-ao-peplum-da-bao-quan-jeans-ong-loe-blue-leopard-streetwear-style-1.jpg', 7, 5, 'Phong Cách', 28, 0, '2026-05-06 12:40:21', 'female'),
(98, 'Áo Trễ Vai & Quần Jeans Rách Trắng/Đen phong cách đường phố', 129000.00, 10, 65000.00, 'Set đồ mang tinh thần streetwear đường phố cá tính, kết hợp giữa sự phóng khoáng và năng động hiện đại.\r\n\r\nÁo trễ vai streetwear: điểm nhấn táo bạo, thể hiện sự tự tin.\r\n\r\nQuần jeans rách ống rộng: tạo hiệu ứng bụi bặm, thời thượng.\r\n\r\nMũ lưỡi trai và choker đinh tán: hoàn thiện phong cách đường phố.\r\n\r\nTúi đeo chéo đen đinh bạc: thêm nét mạnh mẽ và sang trọng.\r\n\r\nHai phiên bản màu:\r\n\r\nTrắng – Phong cách đường phố: tươi sáng, năng động, phù hợp phong cách trẻ trung, tự do.\r\n\r\nĐen – Phong cách đường phố: mạnh mẽ, cá tính, tôn vẻ cool và thời thượng.\r\n\r\nPhù hợp: dạo phố, chụp ảnh lookbook, du lịch hoặc tham dự sự kiện casual.\r\n\r\nChất liệu: cotton co giãn, denim cao cấp, da giả mềm và kim loại sáng bóng.', '1778046430_69fad5de5307a_ao-tre-vai-quan-jeans-rach-den-streetwear-2.jpg,1778046430_69fad5de533cf_ao-tre-vai-quan-jeans-rach-den-streetwear-3.jpg,1778046430_69fad5de56db1_ao-tre-vai-quan-jeans-rach-trang-streetwear-1.jpg', 14, 5, 'Phong Cách', 35, 0, '2026-04-06 12:47:16', 'female'),
(99, 'Set Áo Hai Dây Peplum & Chân Váy Dập Ly – Xanh Than Tối giản', 139000.00, 0, 70000.00, 'SET ÁO PEPLUM LỆCH VAI & CHÂN VÁY MIDI DẬP LY CAO CẤP\r\n\r\nHướng đến vẻ đẹp tối giản nhưng đầy tinh tế, set đồ là sự kết hợp hoàn hảo giữa nét hiện đại và sự mềm mại, giúp tôn vinh vóc dáng người phụ nữ một cách khéo léo nhất.\r\n\r\nSet đồ bao gồm:\r\n\r\nÁo kiểu Peplum: Thiết kế hai dây thanh mảnh (có thể điều chỉnh/tháo rời), điểm nhấn là phần vạt lệch (asymmetrical hem) tạo độ bồng bềnh nhẹ nhàng, giúp che khuyết điểm vòng 2 cực tốt.\r\n\r\nChân váy Midi: Dáng dài dập ly tỉ mỉ (precision-pleated), lưng thun co giãn thoải mái tạo độ rũ thướt tha trong mỗi bước di chuyển.\r\n\r\nPhong cách: Minimalist (Tối giản) kết hợp cùng vẻ đẹp nữ tính, sang trọng. Sự hài hòa giữa cấu trúc áo Peplum sắc nét và chân váy dập ly mềm mại tạo nên tổng thể vô cùng thời thượng.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nXanh Than (Navy): Tone màu kinh điển, tôn da và mang lại cảm giác chuyên nghiệp, quý phái.\r\n\r\nĐỏ Rượu (Burgundy): Tone màu nồng nàn, quyến rũ, cực kỳ nổi bật cho những buổi tiệc tối hoặc sự kiện quan trọng.\r\n\r\nChất liệu: Vải cao cấp có độ bóng nhẹ, giữ form tốt cho phần peplum và tạo độ bay bổng cho phần dập ly.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nDạo phố/Hẹn hò: Kết hợp cùng túi xách nhỏ và giày cao gót quai mảnh để có một vẻ ngoài thơ mộng.\r\n\r\nDự tiệc/Sự kiện: Thêm phụ kiện trang sức tối giản để trở nên nổi bật và sang trọng.\r\n\r\nCông sở: Có thể khoác thêm một chiếc Blazer mỏng bên ngoài để tăng vẻ thanh lịch, chỉn chu.', '1778046982_69fad80656f58_set-ao-peplum-chan-vay-dap-ly-xanh-than-toi-gian-1.jpg,1778046982_69fad80656c91_set-ao-peplum-chan-vay-dap-ly-xanh-than-toi-gian-2.jpg,1778046982_69fad8065711f_set-ao-peplum-chan-vay-dap-ly-do-ruou-toi-gian-3.jpg', 15, 6, 'Phong Cách', 19, 0, '2026-04-06 12:53:46', 'female'),
(100, 'Áo Thun Dài Tay & Quần Suông – Phiên Bản Đen/Trắng Tối Giản', 119000.00, 5, 60000.00, 'Set đồ gồm áo thun dài tay form ôm nhẹ, quần suông cạp cao dáng rộng, kết hợp cùng mũ lưỡi trai và túi tote canvas chữ in nổi bật.\r\n\r\nChất liệu: cotton gân mềm cho áo, vải denim/linen nhẹ cho quần, canvas dày dặn cho túi tote.\r\n\r\nMàu sắc: có 2 phiên bản riêng biệt – đen mang lại vẻ cá tính, mạnh mẽ; trắng tạo cảm giác thanh lịch, trẻ trung.\r\n\r\nPhong cách: tối giản, năng động, hợp xu hướng streetwear hiện đại.\r\n\r\nPhù hợp: dạo phố, đi học, chụp ảnh lookbook, hoặc tham dự sự kiện casual.\r\n\r\nChi tiết điểm nhấn: áo xếp ly nhẹ ở vai, quần suông tôn dáng, túi tote chữ in nổi bật giúp tổng thể vừa tinh tế vừa cá tính.', '1778047077_69fad86537eb7_ao-thun-dai-tay-trang-quan-suong-trang-toi-gian-1.jpg,1778047077_69fad8653753e_ao-thun-dai-tay-trang-quan-suong-trang-toi-gian-3.jpg,1778047077_69fad86537aba_ao-thun-dai-tay-trang-quan-suong-trang-toi-gian-2.jpg', 7, 6, 'Phong Cách', 8, 0, '2026-05-06 12:58:03', 'female'),
(101, 'Set Áo Cardigan & Áo Bèo – Phiên Bản Xanh/Hồng Pastel Tối Giản', 149000.00, 0, 75000.00, 'Set đồ gồm áo cardigan dáng ngắn, áo thun bèo trắng, quần suông denim sáng, mũ lưỡi trai và túi tote hoa nhí. Tổng thể mang phong cách tối giản, nhẹ nhàng và thanh lịch, phù hợp cho những ngày dạo phố, đi học hoặc chụp ảnh lookbook.\r\n\r\nChất liệu: cardigan len mềm nhẹ, áo thun cotton ren thoáng mát, quần denim sáng tôn dáng, túi tote canvas dày dặn.\r\n\r\nMàu sắc: có 2 phiên bản riêng biệt – xanh pastel mang lại cảm giác tươi mát, trẻ trung; hồng pastel thể hiện nét nữ tính, dịu dàng.\r\n\r\nPhong cách: tối giản, hiện đại, dễ phối với phụ kiện trắng hoặc bạc.\r\n\r\nPhù hợp: dạo phố, cà phê, du lịch hoặc chụp ảnh lookbook ngoài trời.\r\n\r\nĐiểm nhấn: áo bèo trắng tạo độ mềm mại, cardigan pastel làm nổi bật làn da, túi tote hoa nhí giúp tổng thể thêm phần tinh tế.', '1778047180_69fad8cc63386_set-cardigan-ao-beo-xanh-pastel-toi-gian-3.jpg,1778047180_69fad8cc63601_set-cardigan-ao-beo-xanh-pastel-toi-gian-2.jpg,1778047180_69fad8cc63eb2_set-cardigan-ao-beo-xanh-pastel-toi-gian-1.jpg,1778047180_69fad8cc643e6_set-cardigan-ao-beo-hong-pastel-toi-gian-4.jpg', 15, 6, 'Phong Cách', 27, 0, '2026-04-06 12:59:47', 'female'),
(102, 'Set Áo Hoa Vàng & Váy Tầng – Trắng, Vàng, Đen Năng Động', 119000.00, 8, 60000.00, 'Set đồ gồm:\r\n\r\nÁo thun ngắn tay trang trí họa tiết hoa vàng nổi bật.\r\n\r\nVáy tầng dáng xòe nhẹ, tạo sự năng động và thoải mái.\r\n\r\nPhụ kiện gợi ý: túi cói hoặc túi vải để hoàn thiện outfit.\r\n\r\nPhong cách: Năng động – trẻ trung, hiện đại, phù hợp xu hướng thời trang dạo phố.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nTrắng: tinh khôi, dễ phối, mang lại cảm giác thanh lịch.\r\n\r\nVàng: nổi bật, rực rỡ, tạo điểm nhấn cá tính.\r\n\r\nĐen: sang trọng, mạnh mẽ, dễ phối với nhiều phụ kiện.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nDạo phố, đi học, đi chơi cùng bạn bè.\r\n\r\nThích hợp cho picnic, du lịch hoặc các buổi hẹn cà phê.\r\n\r\nOutfit lý tưởng để chụp ảnh phong cách năng động, vừa đơn giản vừa nổi bật.', '1778047337_69fad96918bf2_set-ao-hoa-vang-vay-tang-den-nang-dong-1.jpg,1778047337_69fad969167af_set-ao-hoa-vang-vay-tang-den-nang-dong-2.jpg,1778047337_69fad96916531_set-ao-hoa-vang-vay-tang-den-nang-dong-3.jpg,1778047337_69fad96916315_set-ao-hoa-vang-vay-tang-den-nang-dong-4.jpg,1778047337_69fad96916046_set-ao-hoa-vang-vay-tang-den-nang-dong-5.jpg', 21, 8, 'Phong Cách', 4, 0, '2026-05-06 13:02:23', 'female'),
(103, 'Set Áo Khoác Caro & Quần Short Jean – Năng Động Trẻ Trung', 159000.00, 0, 80000.00, 'Set đồ gồm: áo khoác caro form rộng phối cùng áo thun trắng cơ bản, quần short jean sáng màu sờn gấu và túi mini trắng đeo chéo. Tổng thể mang phong cách năng động, trẻ trung, dễ phối đồ và phù hợp với nhiều hoàn cảnh sử dụng.\r\n\r\nChất liệu: cotton mềm thoáng mát cho áo khoác, denim wash bền màu cho quần short, da tổng hợp cao cấp cho túi mini.\r\n\r\nMàu sắc:\r\n\r\nXanh lá: tươi mát, hiện đại, mang lại cảm giác trẻ trung và năng lượng.\r\n\r\nHồng: ngọt ngào, nữ tính, tạo vẻ dễ thương và nhẹ nhàng.\r\n\r\nVàng: tươi sáng, rạng rỡ, thể hiện phong cách tự tin và nổi bật.\r\n\r\nPhong cách: năng động, hiện đại, pha chút retro, dễ phối cùng giày thể thao hoặc phụ kiện đơn giản.\r\n\r\nPhù hợp: dạo phố, đi học, đi cà phê, du lịch hoặc chụp ảnh lookbook casual.', '1778047608_69fada78648e4_set-ao-khoac-caro-xanh-la-quan-short-jean-2.jpg,1778047608_69fada7864f42_set-ao-khoac-caro-xanh-la-quan-short-jean-1.jpg,1778047608_69fada786528f_set-ao-khoac-caro-vang-quan-short-jean-4.jpg,1778047608_69fada7865566_set-ao-khoac-caro-hong-quan-short-jean-3.jpg', 16, 8, 'Phong Cách', 7, 0, '2026-05-06 13:06:54', 'female'),
(104, 'Set Áo Hai Lớp Váy Denim Đen Xám Cá Tính', 149000.00, 0, 75000.00, 'Set đồ gồm: yếm quần denim dáng suông phối cùng áo thun trắng dài tay form ôm nhẹ, tạo tổng thể năng động, hiện đại và dễ phối đồ.\r\n\r\nChất liệu: denim wash cao cấp bền màu, áo thun cotton gân mềm mịn, thoáng mát.\r\n\r\nMàu sắc: xanh denim cổ điển kết hợp trắng tinh khôi, mang lại cảm giác trẻ trung và phóng khoáng.\r\n\r\nPhong cách: năng động, cá tính, pha chút retro, phù hợp với xu hướng streetwear hiện nay.\r\n\r\nPhù hợp: dạo phố, đi học, chụp ảnh lookbook, hoặc tham dự sự kiện thời trang casual.\r\n\r\nChi tiết điểm nhấn: dây yếm kim loại sáng, nhãn “HEART CLUB” thêu đỏ nổi bật, và túi trước tiện dụng giúp tổng thể vừa tinh tế vừa năng động.', '1778047708_69fadadc56c58_set-ao-hai-lop-vay-denim-den-xam-ca-tinh-1.jpg,1778047708_69fadadc56613_set-ao-hai-lop-vay-denim-den-xam-ca-tinh-2.jpg,1778047708_69fadadc55f98_set-ao-hai-lop-vay-denim-den-xam-ca-tinh-3.jpg', 7, 8, 'Phong Cách', 6, 0, '2026-05-06 13:08:34', 'female'),
(105, 'Set Áo Thun Graphic & Chân Váy Plaid – Be Nhạt, Trắng, Xanh Mint Hàn Quốc', 169000.00, 10, 85000.00, 'Set đồ gồm:\r\n\r\nÁo thun graphic trẻ trung, chất liệu cotton thoáng mát, in họa tiết hiện đại.\r\n\r\nChân váy plaid (kẻ caro) dáng chữ A, tôn dáng, dễ phối cùng nhiều loại giày.\r\n\r\nPhong cách: Hàn Quốc – năng động, trẻ trung, phù hợp xu hướng thời trang đường phố.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nBe nhạt: tinh tế, thanh lịch, dễ phối cùng phụ kiện pastel hoặc trung tính.\r\n\r\nTrắng: tinh khôi, đơn giản nhưng nổi bật, tạo cảm giác sạch sẽ và hiện đại.\r\n\r\nXanh mint: dịu mắt, tươi mới, mang lại cảm giác trẻ trung và ngọt ngào.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nDạo phố, đi học, đi chơi cùng bạn bè.\r\n\r\nThích hợp cho các buổi hẹn cà phê, picnic hoặc du lịch.\r\n\r\nOutfit lý tưởng để chụp ảnh phong cách Hàn Quốc, vừa đơn giản vừa nổi bật.', '1778047835_69fadb5bcdc02_set-ao-thun-graphic-chan-vay-plaid-xanh-mint-han-quoc-1.jpg,1778047835_69fadb5bcd97c_set-ao-thun-graphic-chan-vay-plaid-xanh-mint-han-quoc-2.jpg,1778047835_69fadb5bcd466_set-ao-thun-graphic-chan-vay-plaid-xanh-mint-han-quoc-3.jpg,1778047835_69fadb5bce0be_set-ao-thun-graphic-chan-vay-plaid-be-nhat-han-quoc-4.jpg,1778047835_69fadb5bcde66_set-ao-thun-graphic-chan-vay-plaid-trang-han-quoc-5.jpg', 19, 7, 'Phong Cách', 32, 0, '2026-05-06 13:10:42', 'female'),
(106, 'Set Áo Voan Thêu Hoa Kem & Chân Váy Ngắn Hồng', 109000.00, 0, 60000.00, 'Set đồ gồm:\r\n\r\nÁo voan màu kem, thêu hoa tinh xảo, tay bồng nhẹ tạo sự nữ tính và thanh lịch.\r\n\r\nChân váy ngắn màu hồng pastel, dáng trẻ trung, dễ phối cùng nhiều loại giày.\r\n\r\nPhụ kiện gợi ý: túi cói nhỏ hoặc túi đeo chéo pastel để hoàn thiện outfit.\r\n\r\nPhong cách: Hàn Quốc – ngọt ngào, lãng mạn, phù hợp xu hướng thời trang dạo phố và picnic.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nKem: tinh khôi, thanh nhã.\r\n\r\nHồng pastel: nhẹ nhàng, nữ tính.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nDạo phố, đi chơi cùng bạn bè.\r\n\r\nThích hợp cho các buổi hẹn cà phê, picnic hoặc du lịch.\r\n\r\nOutfit lý tưởng để chụp ảnh phong cách Hàn Quốc, vừa đơn giản vừa nổi bật.', '1778049717_69fae2b51d853_set-ao-voan-theu-hoa-kem-chan-vay-ngan-hong-1.jpg,1778049717_69fae2b51dac7_set-ao-voan-theu-hoa-kem-chan-vay-ngan-nau-4.jpg,1778049717_69fae2b51dc6a_set-ao-voan-theu-hoa-kem-chan-vay-ngan-xanh-bo-3.jpg,1778049717_69fae2b51ddd1_set-ao-voan-theu-hoa-kem-chan-vay-ngan-hong-2.jpg', 23, 7, 'Phong Cách', 55, 0, '2026-05-06 13:12:51', 'female'),
(107, 'Đầm Ren Bất Đối Xứng – Trắng Hàn Quốc', 119000.00, 0, 60000.00, 'Set đồ gồm:\r\n\r\nĐầm ren bất đối xứng với chi tiết ren tinh xảo, tay bồng nhẹ tạo sự nữ tính.\r\n\r\nThiết kế dáng xếp tầng mềm mại, dễ phối cùng túi cói hoặc giày trắng.\r\n\r\nPhong cách: Hàn Quốc – ngọt ngào, trẻ trung, lãng mạn và hiện đại.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nTrắng: tinh khôi, thanh nhã.\r\n\r\nXanh mint: dịu dàng, tươi mới.\r\n\r\nXanh navy: sang trọng, cuốn hút.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nDạo biển, picnic, du lịch mùa hè.\r\n\r\nTham dự tiệc ngoài trời, sự kiện nhẹ nhàng.\r\n\r\nOutfit lý tưởng cho chụp ảnh phong cách Hàn Quốc, tạo vẻ đẹp thanh lịch và nổi bật.', '1778048059_69fadc3bb4fa6_dam-ren-bat-doi-xung-xanh-navy-han-quoc-3.jpg,1778048059_69fadc3bb5582_dam-ren-bat-doi-xung-xanh-mint-han-quoc-4.jpg,1778048059_69fadc3bb5a96_dam-ren-bat-doi-xung-trang-han-quoc-2.jpg,1778048059_69fadc3bb5f00_dam-ren-bat-doi-xung-trang-han-quoc-1.jpg', 17, 7, 'Phong Cách', 3, 0, '2026-05-06 13:14:26', 'female'),
(108, 'Set Full Outfit Trắng Đen Cá Tính', 139000.00, 0, 70000.00, 'Set gồm 4 món:\r\n\r\nÁo thun graphic oversized: cotton thoáng mát, in chữ nổi bật, phối sọc tay tạo điểm nhấn.\r\n\r\nQuần cargo dáng rộng: nhiều túi hộp, dây đai và khoen kim loại, phong cách utilitarian mạnh mẽ.\r\n\r\nNón Adidas: form cổ điển, logo thêu trắng, dễ phối với mọi outfit.\r\n\r\nTúi cá tính: thiết kế hộp, chất liệu vải dày dặn, chi tiết kim loại nổi bật.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nTrắng: tinh khôi, trẻ trung, tạo sự cân bằng cho outfit.\r\n\r\nĐen: cool ngầu, thời thượng, dễ phối phụ kiện bạc.\r\n\r\nPhong cách: Cá tính – năng động, hiện đại, phù hợp xu hướng thời trang thành thị.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nDạo phố, đi chơi, chụp ảnh lookbook.\r\n\r\nPhù hợp cho cả nam và nữ yêu thích phong cách urban cá tính.', '1778048139_69fadc8b376d0_set-full-outfit-trang-den-ca-tinh-1.jpg,1778048139_69fadc8b3736b_set-full-outfit-trang-den-ca-tinh-2.jpg,1778048139_69fadc8b37028_set-full-outfit-trang-den-ca-tinh-3.jpg,1778048139_69fadc8b36cd3_set-full-outfit-trang-den-ca-tinh-4.jpg', 16, 10, 'Phong Cách', 20, 0, '2026-05-06 13:15:45', 'female'),
(109, 'Set Áo Khoác Da & Quần Jeans Rách – Đen Cá Tính', 169000.00, 7, 85000.00, 'Set gồm 2 món:\r\n\r\nÁo khoác da lửng: chất liệu da mềm, form biker cổ điển, chi tiết khóa kéo và đai bạc tạo điểm nhấn mạnh mẽ.\r\n\r\nQuần jeans rách dáng rộng: denim cao cấp, rách gối và đùi tự nhiên, phối thắt lưng đinh tán bạc giúp tổng thể thêm cá tính.\r\n\r\nPhong cách: Đen cá tính – hiện đại, mạnh mẽ, thể hiện gu thời trang tự tin và độc lập.\r\n\r\nChi tiết nổi bật:\r\n\r\nPhối áo khoác da với quần jeans rách tạo hiệu ứng tương phản chất liệu.\r\n\r\nPhụ kiện bạc (dây chuyền, thắt lưng, khuy áo) làm nổi bật phong cách grunge hiện đại.\r\n\r\nDễ phối cùng giày sneaker hoặc boots cổ thấp để hoàn thiện outfit.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nDạo phố, chụp ảnh lookbook, đi chơi hoặc tham dự sự kiện thời trang.\r\n\r\nPhù hợp cho người yêu thích phong cách urban, cool và cá tính.', '1778048368_69fadd702cd84_set-ao-khoac-da-quan-jeans-rach-den-ca-tinh-1.jpg,1778048368_69fadd702c896_set-ao-khoac-da-quan-jeans-rach-den-ca-tinh-2.jpg,1778048368_69fadd702c576_set-ao-khoac-da-quan-jeans-rach-den-ca-tinh-3.jpg', 14, 10, 'Phong Cách', 29, 0, '2026-05-06 13:19:34', 'female'),
(110, 'Set Áo Hai Lớp & Váy Denim – Đen Xám Cá Tính', 119000.00, 0, 60000.00, 'Set gồm 3 món:\r\n\r\nÁo hai lớp: áo trong màu xám trắng, chất liệu thun gân co giãn; áo khoác ngoài đen dáng bomber, form rộng, tạo hiệu ứng layering thời thượng.\r\n\r\nChân váy denim: màu xanh nhạt, dáng ngắn, viền ren và rách nhẹ, mang nét nữ tính pha chút bụi bặm.\r\n\r\nPhụ kiện gợi ý: mũ lưỡi trai nâu hoặc túi nhỏ để hoàn thiện phong cách.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nĐen – Xám – Trắng: trung tính, dễ phối, tạo cảm giác hiện đại và cá tính.\r\n\r\nPhong cách: Cá tính – năng động, trẻ trung, phù hợp xu hướng thời trang đường phố.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nDạo phố, đi chơi, chụp ảnh lookbook.\r\n\r\nPhù hợp cho những ai yêu thích phong cách urban chic pha chút nữ tính.', '1778048459_69faddcb3de3a_set-ao-hai-lop-vay-denim-den-xam-ca-tinh-1.jpg,1778048459_69faddcb3da31_set-ao-hai-lop-vay-denim-den-xam-ca-tinh-2.jpg,1778048459_69faddcb3d64a_set-ao-hai-lop-vay-denim-den-xam-ca-tinh-3.jpg', 8, 10, 'Phong Cách', 20, 0, '2026-05-06 13:21:05', 'female'),
(111, 'Set Áo Drap & Váy Ren – 3 Màu', 119000.00, 0, 60000.00, 'Set đồ gồm:\r\n\r\nÁo drap cổ đổ mềm mại, ngắn tay, mang lại vẻ thanh lịch và nữ tính.\r\n\r\nVáy ren tầng dáng xòe, phối cùng thắt lưng bản to có khoen tròn, kèm tua rua trang trí.\r\n\r\nPhong cách: Hàn Quốc – thanh lịch, hiện đại, phù hợp xu hướng thời trang dạo phố và sự kiện.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nTrắng kem: tinh khôi, thanh nhã, dễ phối cùng phụ kiện pastel.\r\n\r\nXanh pastel: nhẹ nhàng, tươi mới, mang hơi hướng mùa xuân hè.\r\n\r\nXanh navy: sang trọng, cuốn hút, phù hợp tiệc tối hoặc sự kiện.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nDạo phố, đi chơi, hẹn hò.\r\n\r\nTham dự tiệc ngoài trời hoặc sự kiện nhẹ nhàng.\r\n\r\nOutfit lý tưởng để chụp ảnh thời trang, vừa đơn giản vừa nổi bật.', '1778048554_69fade2aa4fc5_set-ao-drap-vay-ren-xanh-pastel-4.jpg,1778048554_69fade2aa53b1_set-ao-drap-vay-ren-xanh-navy-5.jpg,1778048554_69fade2aa5626_set-ao-drap-vay-ren-trang-kem-6.jpg,1778048554_69fade2aa5dbf_set-ao-drap-vay-ren-3-mau-7.jpg,1778048554_69fade2aa5fff_set-ao-drap-vay-ren-3-mau-3.jpg,1778048554_69fade2aa620c_set-ao-drap-vay-ren-3-mau-2.jpg,1778048554_69fade2aa62e0_set-ao-drap-vay-ren-3-mau-1.jpg', 21, 9, 'Phong Cách', 11, 0, '2026-05-06 13:22:41', 'female'),
(112, 'Set Áo Peplum Lệch Vai & Chân Váy Trắng Thanh Lịch – Xanh, Vàng', 99000.00, 0, 50000.00, 'Set đồ gồm:\r\n\r\nÁo peplum lệch vai thiết kế hiện đại, tạo điểm nhấn quyến rũ và thanh lịch.\r\n\r\nChân váy trắng dáng dài xếp ly mềm mại, mang lại sự tinh tế và sang trọng.\r\n\r\nPhụ kiện gợi ý: túi xách nhỏ\r\n\r\nPhong cách: Thanh lịch – hiện đại, phù hợp xu hướng thời trang công sở, sự kiện và dạo phố.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nTrắng: tinh khôi, thanh nhã, dễ phối với nhiều phụ kiện.\r\n\r\nXanh: dịu mát, trẻ trung, mang hơi hướng mùa hè.\r\n\r\nVàng: rực rỡ, nổi bật, tạo điểm nhấn cá tính.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nĐi làm, dự tiệc, sự kiện ngoài trời.\r\n\r\nDạo phố, hẹn hò, du lịch.\r\n\r\nOutfit lý tưởng để chụp ảnh thời trang, vừa thanh lịch vừa nổi bật.', '1778048678_69fadea62fa18_set-ao-peplum-lech-vai-va-chan-vay-xếp-ly-vang-pastel-3.jpg,1778048678_69fadea62fef9_set-ao-peplum-lech-vai-chan-vay-trang-thanh-lich-2.jpg,1778048678_69fadea63043b_set-ao-peplum-lech-vai-chan-vay-trang-thanh-lich-1.jpg', 17, 9, 'Phong Cách', 6, 0, '2026-05-06 13:24:44', 'female'),
(113, 'Đầm Ren Trễ Vai – Trắng Thanh Lịch', 99000.00, 0, 50000.00, 'Set đồ gồm:\r\nĐầm ren trễ vai bất đối xứng với chi tiết hoa cắt laser tinh xảo.\r\nPhần nơ lớn phía sau tạo điểm nhấn nữ tính, sang trọng.\r\nPhụ kiện gợi ý: túi cói.\r\nPhong cách:\r\nTrắng kem: Thanh lịch Garden style, mang hơi hướng cổ điển và sang trọng.\r\nHồng pastel: Romantic style, ngọt ngào, trẻ trung, phù hợp không gian ngoài trời.\r\nMàu sắc chủ đạo:\r\nTrắng: tinh khôi, thanh nhã.\r\nHồng pastel: nhẹ nhàng, lãng mạn.\r\nHoàn cảnh sử dụng:\r\nDạo vườn, picnic, chụp ảnh ngoại cảnh.\r\nTham dự tiệc cưới ngoài trời hoặc sự kiện nhẹ nhàng.\r\nOutfit lý tưởng cho những buổi hẹn hò lãng mạn, du lịch nghỉ dưỡng.\r\n', '1778048817_69fadf3165112_dam-ren-tre-vai-trang-thanh-lich-2.jpg,1778048817_69fadf316538b_dam-ren-tre-vai-trang-thanh-lich-1.jpg,1778048817_69fadf3165561_dam-ren-tre-vai-hong-thanh-lich-4.jpg', 13, 9, 'Phong Cách', 0, 0, '2026-05-06 13:26:16', 'female'),
(114, 'Set Áo Khoác Workwear & Áo Len – Quần Tây Xanh Emerald Trẻ Trung Dạo Phố', 99000.00, 5, 50000.00, 'Set đồ gồm: Một áo khoác Blazer chất liệu len dệt cao cấp, áo sơ mi màu tương phản mặc trong, quần tây ống rộng xếp ly, đi kèm các phụ kiện như mũ nồi (beret), thắt lưng da và túi đeo chéo.\r\n\r\nPhong cách: Sự kết hợp tinh tế giữa phong cách Classic Gentleman và Streetstyle hiện đại. Bộ trang phục mang vẻ đẹp tri thức, cổ điển nhưng vẫn rất thời thượng nhờ form dáng rộng rãi.\r\n\r\nMàu sắc chủ đạo:\r\n\r\nPhiên bản Đỏ Rượu: Áo khoác đỏ rượu kết hợp sơ mi tím nhạt và quần xám đậm, tạo điểm nhấn với mũ nồi xanh lá.\r\n\r\nPhiên bản Nâu Tây: Áo khoác và quần màu nâu đồng điệu phối cùng sơ mi vàng rực rỡ, mang lại cảm giác ấm áp và sang trọng.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho các dịp đi làm trong môi trường sáng tạo, dự sự kiện thời trang, dạo phố mùa thu đông, hoặc tham gia các buổi triển lãm, hẹn hò cần sự chỉn chu và gu thẩm mỹ riêng.', '1778049152_69fae0802ef15_set-ao-khoac-workwear-ao-len-quan-tay-nau-emerald-tre-trung-dao-pho-3.jpg,1778049152_69fae0802f5e6_set-ao-khoac-workwear-ao-len-quan-tay-xanh-emerald-tre-trung-dao-pho-1.jpg,1778049152_69fae0802fac8_set-ao-khoac-workwear-ao-len-quan-tay-xanh-emerald-tre-trung-dao-pho-2.jpg', 13, 5, 'Phong Cách', 9, 0, '2026-05-06 13:32:38', 'male'),
(115, 'Set Sơ Mi Cargo & Quần Jean Ống Rộng – Xanh Rêu Streetwear Cá Tính', 139000.00, 5, 70000.00, 'Set đồ gồm: Một chiếc áo sơ mi khoác ngoài dáng rộng kiểu Cargo (túi hộp) màu xanh rêu bụi bặm, kết hợp cùng quần Jean suông màu xám đen có thiết kế các đường gân nổi đầy cá tính. (Lưu ý: Set đồ có thể bao gồm hoặc không bao gồm áo tank-top mặc trong tùy gói sản phẩm).\r\n\r\nPhong cách: Mang đậm dấu ấn thời trang đường phố (Streetwear) hiện đại pha chút hoài cổ. Thiết kế tập trung vào sự thoải mái, phóng khoáng nhưng cực kỳ cuốn hút và khẳng định cái tôi riêng.\r\n\r\nMàu sắc chủ đạo: Sự kết hợp hoàn hảo giữa tông màu Xanh Rêu (Olive) trầm tính của áo và màu Xám Đen (Charcoal) bụi bặm của quần, tạo nên tổng thể vừa nam tính, vừa thời thượng.\r\n\r\nHoàn cảnh sử dụng: Rất phù hợp để diện đi chơi, dạo phố, đi cà phê cùng bạn bè, đi du lịch hoặc tham gia các sự kiện, hoạt động ngoại cảnh đề cao phong cách cá nhân độc đáo và sự thoải mái khi vận động.', '1778049367_69fae1573410e_set-so-mi-cargo-quan-jean-ong-rong-xanh-reu-duong-pho-2.jpg,1778049367_69fae1573495d_set-so-mi-cargo-quan-jean-ong-rong-xanh-reu-duong-pho-1.jpg', 7, 5, 'Phong Cách', 34, 0, '2026-04-06 13:36:13', 'male'),
(116, 'Set Áo Khoác Gió & Quần Short – Đỏ Burgundy Đường Phố', 119000.00, 0, 60000.00, 'Set đồ gồm: Một áo khoác gió (Windbreaker) dáng rộng có mũ phối chi tiết kẻ sọc trắng, đi kèm quần short vải dù đồng bộ.\r\n\r\nPhong cách: Streetwear (Đường phố) mang hơi hướng Sporty. Thiết kế chú trọng vào sự thoải mái, phóng khoáng với form dáng oversize, giúp người mặc dễ dàng tạo ra những bản phối độc đáo theo phong cách layering.\r\n\r\nMàu sắc chủ đạo: Gồm hai tông màu nổi bật là Đỏ Burgundy (Đỏ rượu) ấm áp và Xanh lá trẻ trung, bắt mắt.\r\n\r\nHoàn cảnh sử dụng: Phù hợp cho các hoạt động dạo phố, đi chơi cùng bạn bè, đi du lịch hoặc tham gia các sự kiện văn hóa đường phố, hip-hop.', '1778061999_69fb12af5d6f4_set-ao-khoac-gio-quan-short-do-duong-pho-1.jpg,1778061999_69fb12af5cdf4_set-ao-khoac-gio-quan-short-do-ruou-streetstyle-2.jpg', 13, 5, 'Phong Cách', 39, 0, '2026-05-06 17:06:46', 'male'),
(117, 'Set Polo Trắng & Quần Cargo – Đen Xám Tối Giản / Xanh Olive Tối Giản', 79000.00, 5, 40000.00, 'Set đồ gồm áo polo trắng cổ điển phối cùng quần cargo dáng rộng, mang phong cách tối giản, hiện đại.\r\n\r\nChất liệu: cotton mềm mịn, thoáng khí; quần vải cargo chắc chắn, bền màu.\r\n\r\nMàu sắc:\r\n\r\nĐen xám: trung tính, dễ phối, tạo cảm giác thanh lịch và mạnh mẽ.\r\n\r\nXanh olive: trẻ trung, cá tính, mang hơi hướng streetwear tự nhiên.\r\n\r\nPhong cách: minimal streetwear, phóng khoáng nhưng tinh tế.\r\n\r\nPhù hợp: dạo phố, đi cà phê, chụp ảnh lifestyle hoặc tham dự sự kiện casual.\r\nChi tiết nổi bật gồm túi hộp tiện dụng, form quần rộng thoải mái, và áo polo trắng tinh giản, tạo tổng thể vừa thanh lịch vừa năng động.', '1778062134_69fb13369014a_set-polo-trang-cargo-olive-toi-gian-4.jpg,1778062134_69fb13369072d_set-polo-trang-cargo-den-xam-toi-gian-3.jpg,1778062134_69fb133690ad4_set-polo-trang-cargo-den-xam-toi-gian-2.jpg,1778062134_69fb133690e66_set-polo-trang-cargo-den-xam-toi-gian-1.jpg', 9, 6, 'Phong Cách', 38, 0, '2026-05-06 17:09:02', 'male'),
(118, 'Set Polo & Quần Ống Rộng – Navy Tối Giản / Đỏ Urban', 99000.00, 0, 50000.00, 'Set đồ gồm áo polo cổ điển và quần ống rộng, mang phong cách tối giản, hiện đại.\r\n\r\nChất liệu: cotton mềm mịn, thoáng khí; quần vải nhẹ, form suông tự nhiên.\r\n\r\nMàu sắc:\r\n\r\nNavy: thanh lịch, dễ phối, phù hợp phong cách tối giản.\r\n\r\nĐỏ Urban: trẻ trung, nổi bật, mang hơi hướng streetwear hiện đại.\r\n\r\nPhù hợp: đi làm, dạo phố, cà phê hoặc chụp ảnh lifestyle.\r\nTổng thể tạo cảm giác thoải mái, tinh tế và thời thượng, phù hợp với xu hướng minimal streetwear.', '1778062262_69fb13b6a565b_set-polo-quan-ong-rong-navy-toi-gian-1.jpg,1778062262_69fb13b6a53be_set-polo-quan-ong-rong-navy-toi-gian-2.jpg,1778062262_69fb13b6a4f88_set-polo-quan-ong-rong-navy-toi-gian-3.jpg,1778062262_69fb13b6a58d6_set-polo-quan-ong-rong-do-urban-xanh-reu-4.jpg', 11, 6, 'Phong Cách', 7, 0, '2026-05-30 17:11:09', 'male'),
(119, 'Set Sơ Mi Quần Jean Trắng Minimalis', 89000.00, 0, 50000.00, 'Set đồ bao gồm:\r\n\r\nÁo khoác ngoài: Áo sơ mi kẻ sọc form rộng (oversize), thiết kế tối giản tập trung vào đường may và form dáng.\r\n\r\nÁo trong: Áo thun basic trắng trơn, chất liệu cotton cao cấp, mềm mại và thoáng mát.\r\n\r\nQuần: Quần short jean ống rộng, độ dài ngang gối, tạo sự thoải mái tối đa cho người mặc.\r\n\r\nPhụ kiện: Túi tote vải canvas tối giản và nón lưỡi trai đồng điệu.\r\n\r\nPhong cách: Minimalism (Tối giản) – Đề cao sự đơn giản trong màu sắc và tinh tế trong cách phối lớp (layering).\r\n\r\nMàu sắc chủ đạo:\r\n\r\nTrắng: Màu sắc cơ bản nhất của phong cách tối giản, mang lại vẻ ngoài sạch sẽ, sáng sủa và dễ dàng phối cùng mọi phụ kiện.\r\n\r\nXanh Lá (Sage/Forest Green): Một làn gió mới cho tủ đồ tối giản, mang lại cảm giác dịu mát, gần gũi với thiên nhiên nhưng vẫn rất hiện đại.\r\n\r\nChất liệu:\r\n\r\nSơ mi vải linen/thô cao cấp, giữ độ nhăn tự nhiên đặc trưng của phong cách tối giản.\r\n\r\nQuần denim mềm, bền màu và có độ đứng form tốt.\r\n\r\nHoàn cảnh sử dụng:\r\n\r\nPhù hợp cho các buổi gặp mặt bạn bè, đi làm trong môi trường sáng tạo hoặc các chuyến dã ngoại cuối tuần.\r\n\r\nThiết kế linh hoạt giúp bạn dễ dàng thay đổi phong cách chỉ bằng cách cài hoặc mở cúc áo sơ mi bên ngoài.', '1778062366_69fb141e867c0_set-so-mi-quan-jean-xanh-la-minimalist-4.jpg,1778062366_69fb141e870c5_set-so-mi-quan-jean-trang-minimalist-3.jpg,1778062366_69fb141e87b3c_set-so-mi-quan-jean-trang-minimalist-2.jpg,1778062366_69fb141e8836b_set-so-mi-quan-jean-trang-minimalist-1.jpg', 12, 6, 'Phong Cách', 19, 0, '2026-05-30 17:12:53', 'male'),
(120, 'Set Sơ Mi Kaki & Jeans – Xám Năng Động', 109000.00, 0, 55000.00, 'Set đồ gồm:\r\nÁo sơ mi kaki ngắn tay, chất liệu cotton thoáng mát, màu kaki sáng tạo cảm giác phóng khoáng.\r\nÁo tanktop màu olive trầm, chất liệu thun gân, dễ phối lớp trong.\r\nQuần jeans màu xám denim, form rộng, đường may nổi, chất vải dày dặn.\r\nPhụ kiện: vòng cổ bạc, vòng tay, thắt lưng da, túi tote nâu.\r\nPhong cách: Năng động, pha chút utilitarian (công năng) với túi lớn và quần jeans thoải mái. \r\nMàu sắc chủ đạo: Kaki – Olive – Xám – Nâu. \r\nHoàn cảnh sử dụng: Phù hợp đi dạo phố, café, du lịch ngắn ngày hoặc tham gia hoạt động ngoài trời.\r\n', '1778062458_69fb147a2d4ea_set-so-mi-kaki-jeans-xam-nang-dong-1.jpg,1778062458_69fb147a2d2b9_set-so-mi-kaki-jeans-xam-nang-dong-2.jpg,1778062458_69fb147a2cf1e_set-so-mi-kaki-jeans-xam-nang-dong-3.jpg,1778062458_69fb147a2cc21_set-so-mi-kaki-jeans-xam-nang-dong-4.jpg', 7, 8, 'Phong Cách', 47, 0, '2026-05-30 17:14:25', 'male'),
(121, 'Set Sơ Mi Cổ Vest Denim Blue & Quần Short Kaki Beige – Năng Động', 129000.00, 0, 65000.00, 'Một set đồ mang hơi hướng Urban Casual, kết hợp giữa sự phóng khoáng của chất liệu giả denim và nét thanh lịch của form dáng tối giản. Đây là lựa chọn lý tưởng cho những chàng trai yêu thích sự thoải mái nhưng vẫn muốn giữ vẻ ngoài chỉn chu.\r\n\r\nChi tiết sản phẩm:\r\n\r\nÁo sơ mi: Thiết kế tay ngắn, cổ vest (notch lapel) hiện đại. Chất vải màu xanh Denim Blue với hiệu ứng dệt melange tinh tế, có túi ngực tiện lợi.\r\n\r\nÁo thun mặc trong: Cotton trắng basic, giúp tạo lớp layer sáng sủa và trẻ trung.\r\n\r\nQuần short: Màu Be (Beige) trung tính, chất vải kaki hoặc đũi dày dặn, có xếp ly nhẹ phía trước tạo độ đứng form và giúp cử động thoải mái.\r\n\r\nPhụ kiện đi kèm: Túi đeo chéo da màu nâu bò (Brown) tạo điểm nhấn tương phản ấm áp cho tổng thể màu lạnh.\r\n\r\nMàu sắc chủ đạo: Xanh Denim - Trắng - Be. Sự kết hợp màu sắc kinh điển này mang lại cảm giác dễ chịu, dịu mắt và cực kỳ \"hack\" tuổi.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho các buổi dã ngoại ngoài trời, đi cafe cùng bạn bè, đi học thêm hoặc những chuyến du lịch mùa hè.', '1778062624_69fb1520f262f_set-so-mi-denim-quan-short-beige-nang-dong-1.jpg,1778062624_69fb1520f1d2d_set-so-mi-denim-quan-short-beige-nang-dong-2.jpg', 10, 8, 'Phong Cách', 15, 0, '2026-05-30 17:17:11', 'male'),
(122, 'Set Hoodie Xám & Quần Xanh/Nâu & Quần Be – Năng Động Trẻ Trung', 89000.00, 5, 50000.00, 'Set đồ gồm: hoodie khóa kéo form rộng phối cùng áo thun trắng cơ bản và quần dáng suông thoải mái, tạo tổng thể năng động, hiện đại.\r\n\r\nChất liệu: cotton mềm cao cấp, thoáng mát, co giãn nhẹ giúp giữ form tốt và mang lại cảm giác dễ chịu khi mặc.\r\n\r\nMàu sắc:\r\n\r\nNâu: tông nâu phối quần be mang lại vẻ tối giản, tinh tế và trưởng thành.\r\n\r\nXám: tông xám phối quần xanh tạo cảm giác trẻ trung, năng động và hiện đại.\r\n\r\nPhong cách: năng động, hiện đại, pha chút retro, dễ phối cùng giày thể thao hoặc phụ kiện đơn giản.\r\n\r\nPhù hợp: dạo phố, đi học, đi cà phê, du lịch hoặc chụp ảnh lookbook casual.\r\n\r\nChi tiết điểm nhấn: khóa kéo kim loại sáng, form rộng thoải mái, và mũ lưỡi trai đồng bộ giúp tổng thể vừa tinh tế vừa nổi bật.', '1778062773_69fb15b52bcf6_set-hoodie-xam-quan-xanh-nang-dong-1.jpg,1778062773_69fb15b52b95f_set-hoodie-xam-quan-xanh-nang-dong-2.jpg,1778062773_69fb15b52c0f2_set-hoodie-nau-quan-be-nang-dong-3.jpg', 11, 8, 'Phong Cách', 49, 2, '2026-05-06 17:19:40', 'male'),
(123, 'Set Denim Xanh & Sơ Mi Kẻ Hàn Quốc', 119000.00, 0, 60000.00, 'Set đồ gồm: 01 Áo khoác Denim (bò), 01 Áo sơ mi kẻ sọc nhạt mặc trong và 01 Quần vải ống suông màu trắng kem.\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo khoác Denim: Màu xanh đậm (Indigo) cổ điển với form dáng hơi suông, tạo sự khỏe khoắn và nam tính.\r\n\r\nÁo sơ mi: Họa tiết kẻ sọc xanh trắng thanh mảnh, giúp set đồ có thêm điểm nhấn layering (phối lớp) tinh tế.\r\n\r\nQuần suông: Màu trắng kem (Cream/Off-white) với chất liệu vải kaki hoặc vải thô mềm, ống rộng giúp hack chiều cao và che khuyết điểm đôi chân hiệu quả.\r\n\r\nMàu sắc chủ đạo: Sự phối hợp hoàn hảo giữa tông Xanh Denim đậm và Trắng kem tươi sáng, mang lại cảm giác dễ chịu, sạch sẽ và thời thượng.\r\n\r\nHoàn cảnh sử dụng: Rất phù hợp cho sinh viên đi học, đi làm trong môi trường sáng tạo, đi cà phê cùng bạn bè hoặc dạo phố cuối tuần.', '1778062860_69fb160c203cf_set-denim-xanh-so-mi-ke-han-quoc-1.jpg,1778062860_69fb160c1fb61_set-denim-xanh-so-mi-ke-han-quoc-2.jpg,1778062860_69fb160c1f23a_set-denim-xanh-so-mi-ke-han-quoc-3.jpg', 7, 7, 'Phong Cách', 9, 0, '2026-05-30 17:21:06', 'male'),
(124, 'Set Áo Măng Tô Dạ & Áo Len Zip-up Hàn Quốc – [Đen Ghi / Đỏ Đô]', 109000.00, 15, 50000.00, 'Set đồ gồm: 01 Áo măng tô dạ dáng dài, 01 Áo len dệt gân cổ lọ có khóa kéo và 01 Quần tây ống suông xếp ly.\r\n\r\nTùy chọn màu sắc:\r\n\r\nMàu Đen Ghi: Mang lại vẻ ngoài bí ẩn, chuyên nghiệp và cực kỳ dễ phối đồ.\r\n\r\nMàu Đỏ (Wine Red/Burgundy): Tạo điểm nhấn ấn tượng, lãng tử và ấm áp, rất phù hợp cho các dịp lễ hội hoặc hẹn hò.\r\n\r\nChất liệu:\r\n\r\nÁo khoác: Vải dạ ép cao cấp, đứng form, không xù lông.\r\n\r\nÁo len: Len dệt gân có độ đàn hồi tốt, giữ nhiệt hiệu quả.\r\n\r\nQuần: Vải tuyết mưa hoặc kaki cao cấp, có độ rủ tự nhiên giúp che khuyết điểm chân tốt.\r\n\r\nHoàn cảnh sử dụng: Đi làm, dự tiệc sang trọng, hoặc đi dạo phố trong thời tiết đại hàn.', '1778062945_69fb166182140_set-ao-mang-to-da-ao-len-zip-up-do-ghi-korean-3.jpg,1778062945_69fb166182ab2_set-ao-mang-to-da-ao-len-zip-up-den-ghi-korean-2.jpg,1778062945_69fb166183433_set-ao-mang-to-da-ao-len-zip-up-den-ghi-korean-1.jpg', 14, 7, 'Phong Cách', 10, 0, '2026-05-30 17:22:32', 'male'),
(125, 'Set Cardigan Len Ghi & Quần Jean White Cream – Phong Cách Hàn Quốc', 129000.00, 0, 65000.00, 'Set đồ gồm: 01 Áo Cardigan len, 01 Áo thun trắng basic mặc trong và 01 Quần Jean ống suông màu trắng kem.\r\n\r\nChi tiết sản phẩm:\r\n\r\nÁo Cardigan: Chất liệu len dệt melange màu ghi sáng, mặt vải mềm mịn, có túi hai bên tiện lợi, phù hợp cho tiết trời thu đông se lạnh.\r\n\r\nÁo Thun: Cotton 100% co giãn, thấm hút tốt, cổ tròn đứng form.\r\n\r\nQuần Jean: Màu White Cream (trắng kem) thanh lịch, chất jean dày dặn nhưng vẫn có độ rủ nhẹ, ống suông rộng giúp tôn dáng và thoải mái khi di chuyển.\r\n\r\nMàu sắc: Phối hợp giữa tông Ghi sáng (Light Grey) và Trắng kem (Creamy White), mang lại tổng thể sáng sủa, sạch sẽ và rất \"nịnh\" mắt.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho những buổi đi học, đi làm công sở trẻ trung, hẹn hò cà phê hoặc dã ngoại cuối tuần.', '1778063036_69fb16bc3ce94_set-cardigan-quan-jean-white-cream-korean-1.jpg,1778063036_69fb16bc3c561_set-cardigan-quan-jean-white-cream-korean-2.jpg', 9, 7, 'Phong Cách', 24, 0, '2026-05-06 17:24:02', 'male'),
(126, 'Set Áo Khoác Da Bomber & Quần Suông Cạp Cao – Đen Classic Cá Tính', 119000.00, 3, 60000.00, 'Set đồ gồm: Sự kết hợp hoàn hảo giữa một chiếc Áo Khoác Da kiểu dáng Bomber mạnh mẽ, bo chun cổ tay và gấu áo, đi cùng Quần Tây ống suông (wide-leg) dáng rộng, cạp cao tôn dáng. (Lưu ý: Set đồ không bao gồm áo trong).\r\n\r\nPhong cách: Mang đậm phong cách Street Style hiện đại, pha chút Hip-hop và Unisex. Thiết kế chú trọng sự thoải mái nhưng vẫn cực kỳ thời thượng và \"chất chơi\".\r\n\r\nMàu sắc chủ đạo: Có 2 phiên bản màu sắc đối lập là Đen Classic cool ngầu, dễ phối đồ và Đỏ Rực Rỡ cá tính, giúp bạn nổi bật trong mọi đám đông.\r\n\r\nHoàn cảnh sử dụng: Phù hợp cho những buổi dạo phố, gặp gỡ bạn bè, đi pub, dự các sự kiện thời trang đường phố hoặc chụp ảnh nghệ thuật cá tính. Set đồ là lựa chọn lý tưởng cho mùa thu đông.', '1778063123_69fb17133d54f_set-ao-khoac-da-quan-suong-do-ca-tinh-3.jpg,1778063123_69fb17133df5d_set-ao-khoac-da-quan-suong-den-ca-tinh-2.jpg,1778063123_69fb17133e2a2_set-ao-khoac-da-quan-suong-den-ca-tinh-1.jpg', 8, 10, 'Phong Cách', 18, 0, '2026-05-06 17:25:30', 'male'),
(127, 'Set Cardigan & Quần Jean Rộng – Đỏ Urban / Xanh Rêu Cá Tính', 109000.00, 0, 50000.00, 'Set đồ gồm cardigan len dệt kim phối cùng quần jean ống rộng và mũ lưỡi trai thêu logo, tạo nên phong cách streetwear trẻ trung, phóng khoáng.\r\n\r\nChất liệu: len mềm cao cấp, denim wash bền màu.\r\n\r\nMàu sắc: đỏ đô nổi bật hoặc xanh rêu cá tính.\r\n\r\nPhong cách: năng động, hiện đại, pha chút retro.\r\n\r\nPhù hợp: dạo phố, chụp ảnh, đi cà phê hoặc tham dự sự kiện thời trang casual.\r\nChi tiết điểm nhấn gồm logo thêu trắng trên cardigan, móc khóa vàng ở quần jean, và túi denim mini đồng bộ, giúp tổng thể vừa tinh tế vừa nổi bật.', '1778063192_69fb1758c8a2d_set-cardigan-quan-jean-rong-xanh-3.jpg,1778063192_69fb1758c94b9_set-cardigan-quan-jean-rong-do-urban-2.jpg,1778063192_69fb1758c99f8_set-cardigan-quan-jean-rong-do-urban-1.jpg', 16, 10, 'Phong Cách', 13, 0, '2026-05-30 17:26:41', 'male'),
(128, 'Set Áo Khoác & Quần Jean – Trắng Kem Tối Giản / Xanh Rêu Cá Tính', 129000.00, 0, 60000.00, 'Set đồ gồm áo khoác vải nhẹ kiểu toggle phối cùng quần jean ống rộng và phụ kiện dây xích bạc, mang đậm phong cách streetwear hiện đại pha chút cổ điển.\r\n\r\nChất liệu: cotton pha linen thoáng mát, denim wash cao cấp.\r\n\r\nMàu sắc:\r\n\r\nTrắng kem: tinh tế, tối giản, dễ phối với nhiều phong cách.\r\n\r\nXanh rêu: cá tính, mạnh mẽ, phù hợp với gu thời trang đường phố.\r\n\r\nPhong cách: năng động, phóng khoáng, thể hiện cá tính riêng.\r\n\r\nPhù hợp: dạo phố, chụp ảnh, đi cà phê, hoặc tham dự sự kiện casual.\r\nChi tiết nổi bật gồm khóa toggle truyền thống, form áo khoác rộng, quần jean ống siêu rộng, và dây xích bạc trang trí, tạo điểm nhấn thời thượng cho tổng thể outfit.', '1778063382_69fb18162e91e_set-ao-khoac-quan-jean-xanh-reu-ca-tinh-4.jpg,1778063382_69fb18162f1a5_set-ao-khoac-quan-jean-trang-kem-toi-gian-3.jpg,1778063382_69fb18162f95f_set-ao-khoac-quan-jean-trang-kem-toi-gian-1.jpg,1778063382_69fb181630237_set-ao-khoac-quan-jean-trang-kem-toi-gian-2.jpg', 15, 10, 'Phong Cách', 19, 0, '2026-05-06 17:29:49', 'male'),
(129, 'Set Gilet Kẻ & Sơ Mi Ghi – Đen Lịch Lãm Quý Ông Công Sở', 129000.00, 0, 65000.00, 'Mô tả sản phẩm chung:\r\n•        Set đồ gồm: Combo 3 món cơ bản cho quý ông gồm Áo Gilet (vét không tay) phối nút chỉn chu, Quần Tây ống đứng (Slim-fit) tôn dáng và cà vạt cùng tông màu.\r\n•        Phong cách: Mang đậm phong cách Smart Casual và Business Professional, giúp người mặc toát lên vẻ ngoài chuyên nghiệp, nam tính nhưng vẫn trẻ trung và linh hoạt.\r\n•        Màu sắc chủ đạo: Gồm 3 phiên bản màu sắc kinh điển là Đen quyền lực, Xanh Navy hiện đại và Nâu Classic thời thượng.\r\n•        Hoàn cảnh sử dụng: Phù hợp cho môi trường văn phòng chuyên nghiệp, tham dự các buổi hội nghị, sự kiện quan trọng, lễ cưới hoặc những dịp đòi hỏi sự trang trọng và lịch sự.', '1778063462_69fb1866e69c4_set-gile-ke-so-mi-ghi-xanh-lich-lam-3.jpg,1778063462_69fb1866e7346_set-gile-ke-so-mi-ghi-nau-lich-lam-4.jpg,1778063462_69fb1866e7b2f_set-gile-ke-so-mi-ghi-den-lich-lam-2.jpg,1778063462_69fb1866e88af_set-gile-ke-so-mi-ghi-den-lich-lam-1.jpg', 20, 9, 'Phong Cách', 1, 0, '2026-05-06 17:31:09', 'male'),
(130, 'Set Sơ Mi Nâu & Quần Be – Thanh Lịch Tối Giản', 79000.00, 0, 40000.00, 'Set đồ gồm áo sơ mi nâu dài tay, áo tank top trắng, và quần âu be dáng suông, mang phong cách thanh lịch, tinh tế.\r\n\r\nChất liệu: cotton mềm mịn, thoáng khí; áo trong vải gân co giãn nhẹ; quần vải polyester pha cotton, giữ form chuẩn.\r\n\r\nMàu sắc:\r\n\r\nNâu: trầm ấm, sang trọng, tạo chiều sâu cho outfit.\r\n\r\nBe: nhẹ nhàng, trung tính, tôn lên vẻ tinh tế và hiện đại.\r\n\r\nPhong cách: thanh lịch, tối giản, phù hợp với xu hướng thời trang hiện nay.\r\n\r\nPhù hợp: đi làm, gặp gỡ, cà phê hoặc chụp ảnh lifestyle.\r\nChi tiết nổi bật gồm form áo sơ mi oversize thoải mái, áo trong trắng tạo điểm sáng, và quần be ống suông tôn dáng, tạo tổng thể vừa trang nhã vừa tự nhiên.', '1778063820_69fb19ccd5264_set-so-mi-nau-quan-be-thanh-lich-1.jpg,1778063820_69fb19ccd44dd_set-so-mi-nau-quan-be-thanh-lich-3.jpg,1778063820_69fb19ccd4c26_set-so-mi-nau-quan-be-thanh-lich-2.jpg', 11, 9, 'Phong Cách', 15, 0, '2026-05-06 17:37:07', 'male'),
(131, 'Set Yếm Cổ Đổ Sexy – Đỏ Quyến Rũ Phong Cách Club', 99000.00, 5, 50000.00, 'Mô tả sản phẩm chung:\r\n• Set đồ gồm: Combo 2 món gồm Áo yếm cổ đổ thiết kế ôm dáng, phối khóa kim loại nổi bật và Quần short ngắn form ôm tôn chân, đính chi tiết cá tính.\r\n• Phong cách: Mang đậm phong cách Sexy Glam & Clubwear, giúp người mặc toát lên vẻ ngoài quyến rũ, tự tin và nổi bật trong mọi không gian tiệc tùng về đêm.\r\n• Màu sắc chủ đạo: Tông Đỏ rượu (Burgundy) quyến rũ kết hợp Đen cá tính, tạo hiệu ứng sang trọng và thu hút ánh nhìn.\r\n• Hoàn cảnh sử dụng: Phù hợp cho các buổi đi bar, club, party, sự kiện giải trí ban đêm, chụp ảnh thời trang hoặc dạo phố phong cách cá tính, đặc biệt lý tưởng khi bạn muốn trở thành tâm điểm với outfit nổi bật và cuốn hút.', '1778063895_69fb1a17ac0f1_set-yem-co-do-do-sexy-club-1.png,1778063895_69fb1a17abc32_set-yem-co-do-do-sexy-club-2.png,1778063895_69fb1a17ab194_set-yem-co-do-do-sexy-club-4.png,1778063895_69fb1a17ab680_set-yem-co-do-do-sexy-club-3.png', 8, 11, 'Phong Cách', 7, 0, '2026-04-06 17:38:22', 'female'),
(132, 'Set Áo Halter Brazil & Chân Váy Xếp Ly – Vàng Xanh Cá Tính Street Style', 89000.00, 0, 45000.00, 'Mô tả sản phẩm chung:\r\n• Set đồ gồm: Combo 3 món gồm Áo halter top phối dây cổ ôm dáng nổi bật với họa tiết Brazil, Chân váy ngắn xếp ly form cá tính phối dây rút và chi tiết kim loại, đi kèm mũ lưỡi trai tone xanh tạo điểm nhấn sporty. (Có thể mix thêm boots để hoàn thiện outfit như hình mẫu).\r\n• Phong cách: Mang đậm phong cách Sporty Sexy & Street Style, kết hợp giữa sự năng động, khỏe khoắn và nét quyến rũ hiện đại, phù hợp với những cô nàng cá tính, thích nổi bật.\r\n• Màu sắc chủ đạo: Tông Vàng – Xanh Brazil nổi bật kết hợp Xám denim và Đen, tạo tổng thể trẻ trung, bắt mắt và đậm chất thời trang đường phố.\r\n• Hoàn cảnh sử dụng: Phù hợp cho các dịp dạo phố, đi chơi, cafe, sự kiện ngoài trời, festival, du lịch hoặc chụp ảnh street style, đặc biệt lý tưởng cho những ngày muốn lên đồ năng động nhưng vẫn thu hút và khác biệt.', '1778063967_69fb1a5f76b7b_set-halter-brazil-vang-xanh-street-style-1.png,1778063967_69fb1a5f76886_set-halter-brazil-vang-xanh-street-style-2.png,1778063967_69fb1a5f765a1_set-halter-brazil-vang-xanh-street-style-3.png,1778063967_69fb1a5f762f8_set-halter-brazil-vang-xanh-street-style-4.png', 10, 11, 'Phong Cách', 14, 0, '2026-05-06 17:39:34', 'female'),
(133, 'Set Áo Tube Rút Dây & Quần Short Jean – Xanh Denim Cá Tính', 79000.00, 0, 40000.00, 'Set Áo Tube Rút Dây & Quần Short Jean mang đến hơi thở hiện đại, pha trộn giữa nét quyến rũ và sự năng động. Với thiết kế độc đáo từ thương hiệu LOOPFIT, bộ trang phục giúp tôn lên những đường nét thanh mảnh và phong cách thời trang dẫn đầu xu hướng.\r\n\r\nSản phẩm bao gồm: 01 Áo tube (áo quây) dáng rút dây và 01 Quần short jean rách tua rua.\r\n\r\nPhong cách: Streetwear hiện đại, Y2K cá tính nhưng không kém phần mềm mại, nữ tính nhờ chi tiết bèo nhún.\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo Tube: Thiết kế rút dây tạo nếp gấp gợi cảm, dáng trễ vai tôn vòng một và bờ vai trần. Chất liệu vải mềm mại, có độ co giãn tốt giúp người mặc luôn cảm thấy thoải mái.\r\n\r\nQuần Short Jean: Điểm nhấn là các đường rách tua rua sành điệu phối cùng dải bèo bất đối xứng tạo sự bay bổng. Hai bên hông có dây rút cá tính, dễ dàng điều chỉnh độ ôm, giúp đôi chân trông dài và thon gọn hơn.\r\n\r\nMàu sắc chủ đạo: Xanh Denim (Blue) trẻ trung, dễ dàng phối cùng các phụ kiện bạc hoặc túi xách cầm tay.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho các buổi dạo phố, du lịch biển, tham gia các lễ hội âm nhạc hoặc những buổi hẹn hò cafe cùng bạn bè.', '1778064033_69fb1aa133ef1_set-ao-tube-rut-day-quan-short-jean-xanh-denim-ca-tinh-1.png,1778064033_69fb1aa133478_set-ao-tube-rut-day-quan-short-jean-xanh-denim-ca-tinh-2.png,1778064033_69fb1aa132dc8_set-ao-tube-rut-day-quan-short-jean-xanh-denim-ca-tinh-3.png,1778064033_69fb1aa13246d_set-ao-tube-rut-day-quan-short-jean-xanh-denim-ca-tinh-4.png', 10, 11, 'Phong Cách', 23, 0, '2026-05-30 17:40:40', 'female'),
(134, 'Set Blazer Crop & Chân Váy Xếp Ly – Ghi Phối Hồng Balletcore Sang Chảnh', 99000.00, 0, 50000.00, 'Set Blazer Crop & Chân Váy Xếp Ly là sự kết hợp hoàn hảo giữa nét thanh lịch của phong cách công sở và sự ngọt ngào của xu hướng Balletcore. Với tông màu ghi trung tính điểm xuyết sắc hồng pastel, bộ cánh mang đến diện mạo chuẩn \"tiểu thư hiện đại\", vừa tri thức vừa nữ tính.\r\n\r\nSet đồ gồm: 01 Áo Blazer dáng lửng (crop), 01 Áo sơ mi hồng mặc trong và 01 Chân váy xếp ly ngắn.\r\n\r\nPhong cách: Office Chic pha lẫn Preppy tiểu thư, đậm chất thời trang cao cấp.\r\n\r\nMàu sắc chủ đạo: Xám Ghi (Grey) phối cùng Hồng Pastel (Soft Pink).\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo Blazer Crop: Thiết kế form đứng, tay dài sang trọng, độ dài vừa phải giúp ăn gian chiều cao và tôn vòng eo.\r\n\r\nÁo Sơ Mi: Tông hồng ngọt ngào làm điểm nhấn nổi bật trên nền xám trung tính.\r\n\r\nChân Váy: Dáng xếp ly trẻ trung, được nhấn nhá bằng hai chiếc nơ hồng nhỏ xinh ở phần cạp váy, tạo nên vẻ ngoài điệu đà, tinh tế.\r\n\r\nHoàn cảnh sử dụng: Phù hợp để đi làm trong môi trường sáng tạo, đi tiệc trà chiều, dạo phố cuối tuần hoặc tham gia các sự kiện đòi hỏi sự chỉn chu nhưng vẫn muốn giữ nét trẻ trung.', '1778064127_69fb1affb05e5_set-blazer-crop-chan-vay-xep-ly-ghi-hong-balletcore-1.png,1778064127_69fb1affaff4e_set-blazer-crop-chan-vay-xep-ly-ghi-hong-balletcore-2.png,1778064127_69fb1affafa56_set-blazer-crop-chan-vay-xep-ly-ghi-hong-balletcore-3.png,1778064127_69fb1affaf718_set-blazer-crop-chan-vay-xep-ly-ghi-hong-balletcore-4.png', 9, 12, 'Phong Cách', 15, 0, '2026-05-06 17:42:14', 'female'),
(135, 'Đầm Sơ Mi Kẻ Sọc Phối Nơ – Xanh Sky Dáng Xòe Tiểu Thư', 109000.00, 0, 55000.00, 'Đầm Sơ Mi Kẻ Sọc Phối Nơ mang đến vẻ đẹp thanh lịch, trẻ trung và tràn đầy năng lượng. Với sự kết hợp tinh tế giữa họa tiết kẻ cổ điển và form dáng hiện đại, đây là món đồ không thể thiếu trong tủ đồ của những cô nàng yêu thích phong cách nhẹ nhàng, tinh khôi.\r\n\r\nSản phẩm bao gồm: 01 Đầm liền thân dáng xòe.\r\n\r\nPhong cách: Feminine (Nữ tính), Preppy (Học đường thanh lịch) và tiểu thư sang chảnh.\r\n\r\nMàu sắc chủ đạo: Xanh Sky (Xanh nhạt) phối sọc trắng, tạo cảm giác dịu mắt và tôn da.\r\n\r\nChi tiết thiết kế:\r\n\r\nCổ sơ mi & Tay bồng: Phần cổ sơ mi thanh lịch kết hợp cùng tay ngắn bo nhẹ tạo độ phồng, mang lại nét cổ điển và che khuyết điểm bắp tay tốt.\r\n\r\nĐiểm nhấn nơ đen: Chiếc nơ đen nhỏ xinh đính ở ngực áo tạo điểm nhấn tương phản nổi bật, giúp tổng thể bộ trang phục thêm phần thu hút.\r\n\r\nForm dáng: Vòng eo được nhấn gọn gàng kết hợp chân váy xòe chữ A giúp tôn vinh vóc dáng, tạo hiệu ứng đôi chân thon dài.\r\n\r\nHoàn cảnh sử dụng: Rất phù hợp để diện đi làm công sở, đi học, dạo phố cuối tuần hoặc tham gia các buổi tiệc trà, picnic ngoài trời.', '1778064199_69fb1b4787ec9_dam-so-mi-ke-soc-phoi-no-xanh-sky-tieu-thu-1.png,1778064199_69fb1b4787a71_dam-so-mi-ke-soc-phoi-no-xanh-sky-tieu-thu-2.png,1778064199_69fb1b4787033_dam-so-mi-ke-soc-phoi-no-xanh-sky-tieu-thu-3.png', 7, 12, 'Phong Cách', 20, 0, '2026-05-30 17:43:26', 'female'),
(136, 'Set Áo Sơ Mi Kẻ & Chân Váy Nơ – Nâu Cafe Phong Cách Dark Academia', 119000.00, 5, 60000.00, 'Mô tả sản phẩm\r\nSet Áo Sơ Mi Kẻ & Chân Váy Nơ là sự kết hợp hoàn hảo cho những cô nàng yêu thích vẻ đẹp cổ điển, tri thức nhưng vẫn muốn giữ nét trẻ trung, phá cách. Với tông màu nâu trầm ấm chủ đạo, bộ trang phục mang đậm hơi thở Dark Academia phối cùng phong cách Coquette đang cực kỳ thịnh hành.\r\n\r\nSet đồ gồm: 01 Áo sơ mi kẻ sọc dài tay và 01 Chân váy ngắn phối bèo.\r\n\r\nPhong cách: Dark Academia, Vintage pha chút nữ tính (Coquette).\r\n\r\nMàu sắc chủ đạo: Nâu Cafe (Brown) phối cùng họa tiết kẻ sọc và chi tiết trắng kem.\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo sơ mi: Họa tiết kẻ sọc cổ điển, điểm nhấn là hàng nơ đen thắt dọc trước ngực tạo nên sự mới lạ và điệu đà.\r\n\r\nChân váy: Dáng váy ngắn trẻ trung, thiết kế xếp ly bèo trắng ở gấu váy tạo độ xòe nhẹ. Chi tiết nơ thắt lệch bên hông giúp tôn lên vòng eo và tạo điểm nhấn bắt mắt.\r\n\r\nHoàn cảnh sử dụng: Phù hợp diện đi học, đi làm, dạo phố trong tiết trời thu đông hoặc tham gia các buổi triển lãm nghệ thuật, cafe sách cùng bạn bè.', '1778064297_69fb1ba95be12_set-ao-so-mi-ke-chan-vay-no-nau-cafe-dark-academia-1.png,1778064297_69fb1ba95bb6d_set-ao-so-mi-ke-chan-vay-no-nau-cafe-dark-academia-2.png,1778064297_69fb1ba95b827_set-ao-so-mi-ke-chan-vay-no-nau-cafe-dark-academia-3.png', 9, 12, 'Phong Cách', 14, 0, '2026-05-06 17:45:04', 'female');
INSERT INTO `products` (`id`, `name`, `price`, `discount_percent`, `deposit_price`, `description`, `image`, `stock`, `category_id`, `category`, `rented_count`, `rented_quantity`, `created_at`, `gender`) VALUES
(137, 'Set Sơ Mi Lụa Cổ V & Quần Tây Slimfit – Đen Huyền Bí Style Gợi Cảm', 129000.00, 50, 65000.00, 'Set Sơ Mi Lụa & Quần Tây Slimfit là bản phối hoàn hảo dành cho những quý ông theo đuổi phong cách lãng tử và quyến rũ. Bộ trang phục tập trung vào chất liệu cao cấp và những đường cắt tinh tế, giúp tôn vinh hình thể nam tính một cách khéo léo và đẳng cấp.\r\n\r\nSet đồ gồm: 01 Áo sơ mi lụa cổ V sâu và 01 Quần tây dáng Slimfit.\r\n\r\nPhong cách: Sexy Masculine (Nam tính gợi cảm), tối giản và sang trọng.\r\n\r\nMàu sắc chủ đạo: Đen Tuyền (All Black) – mang lại vẻ ngoài bí ẩn, quyền lực và cực kỳ tôn dáng.\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo sơ mi: Thiết kế cổ V khoét sâu phóng khoáng, không hàng khuy truyền thống, tạo điểm nhấn tập trung vào khuôn ngực săn chắc. Chất liệu lụa rủ nhẹ, mềm mại, tạo sự chuyển động uyển chuyển theo cơ thể.\r\n\r\nQuần tây: Dáng Slimfit ôm vừa vặn, tôn lên đôi chân dài và gọn gàng. Chất vải có độ đứng form nhưng vẫn co giãn nhẹ, mang lại sự thoải mái khi di chuyển.\r\n\r\nHoàn cảnh sử dụng: Là outfit lý tưởng cho những buổi hẹn hò lãng mạn tại các không gian sang trọng, đi tiệc tối (Party), tham dự sự kiện thời trang hoặc những buổi dạo phố đêm đầy phong cách.', '1778064403_69fb1c132befb_set-so-mi-lua-quan-tay-den-huyen-bi-goi-cam-1.jpg,1778064403_69fb1c132b47b_set-so-mi-lua-quan-tay-den-huyen-bi-goi-cam-3.jpg', 8, 11, 'Phong Cách', 12, 0, '2026-05-06 17:46:50', 'male'),
(138, 'Set Áo Ba Lỗ & Quần Short Jean Wash – Đen Xám Khói Cá Tính (Kèm Đai Studded)', 79000.00, 0, 40000.00, 'Nâng tầm phong cách đường phố với set đồ mang đậm hơi thở Y2K và Grunge. Sự kết hợp giữa các tông màu trung tính trầm mặc và phụ kiện kim loại tạo nên một vẻ ngoài vừa bụi bặm, vừa hiện đại, khẳng định cái tôi cá nhân mạnh mẽ.\r\n\r\nSet đồ bao gồm: * 01 Áo ba lỗ (Tank top) ôm dáng, chất liệu thun gân co giãn.\r\n\r\n01 Quần short Jean form rộng (Jorts) với hiệu ứng wash loang màu xám khói độc đáo.\r\n\r\n01 Thắt lưng da đính đinh tán (Studded belt) tạo điểm nhấn nổi loạn.\r\n\r\nPhong cách: Streetwear, Y2K, Hip-hop, Grunge.\r\n\r\nMàu sắc chủ đạo: Đen và Xám khói (Acid wash).\r\n\r\nĐặc điểm nổi bật:\r\n\r\nÁo ba lỗ tối giản giúp tôn vóc dáng khỏe khoắn.\r\n\r\nQuần short Jean thiết kế túi hộp và các đường may nổi (patchwork) tạo chiều sâu cho trang phục.\r\n\r\nPhụ kiện đai lưng đính đinh tán là \"linh hồn\" giúp set đồ trở nên gai góc và có gu hơn.\r\n\r\nHoàn cảnh sử dụng: Phù hợp cho những buổi dạo phố, đi chơi cùng bạn bè, tham gia các sự kiện âm nhạc, underground hoặc chụp ảnh lookbook theo concept cá tính.', '1778064472_69fb1c580fe4a_set-ao-ba-lo-quan-short-jean-xam-khoi-1.jpg,1778064472_69fb1c580f6e5_set-ao-ba-lo-quan-short-jean-xam-khoi-2.jpg,1778064472_69fb1c580e7b3_set-ao-ba-lo-quan-short-jean-xam-khoi-4.jpg,1778064472_69fb1c580efbe_set-ao-ba-lo-quan-short-jean-xam-khoi-3.jpg', 7, 11, 'Phong Cách', 12, 0, '2026-05-06 17:47:59', 'male'),
(139, 'Set Đồ Thể Thao Compression Spider & Quần Cargo – Đen Phối Ghi Mạnh Mẽ', 89000.00, 0, 45000.00, 'Sự kết hợp hoàn hảo giữa công năng và thời trang, set đồ này được thiết kế dành riêng cho những chàng trai yêu thích vận động nhưng vẫn muốn giữ vẻ ngoài cực \"ngầu\". Với điểm nhấn là chiếc áo nén cơ in họa tiết Spider và quần túi hộp form rộng, tổng thể mang đến một diện mạo đầy năng lượng và bứt phá.\r\n\r\nSet đồ bao gồm: * 01 Áo thun Compression (áo nén cơ) ngắn tay, in logo Spider nổi bật ở ngực.\r\n\r\n01 Quần Cargo (quần túi hộp) dáng suông rộng, bo gấu nhẹ nhàng.\r\n\r\n01 Áo khoác dù (Track Jacket) phối màu đen - ghi xám tối giản.\r\n\r\nPhong cách: Sporty Chic, Gymwear, Techwear.\r\n\r\nMàu sắc chủ đạo: Đen (chủ đạo) phối cùng Ghi xám.\r\n\r\nĐặc điểm nổi bật:\r\n\r\nÁo thun chất liệu co giãn 4 chiều, hỗ trợ cơ bắp trong quá trình tập luyện.\r\n\r\nQuần Cargo vải kaki hoặc dù bền bỉ, túi hộp tiện lợi tạo form dáng cứng cáp, bụi bặm.\r\n\r\nÁo khoác dù đi kèm giúp giữ ấm sau khi tập hoặc tạo lớp layering cực thời thượng khi dạo phố.\r\n\r\nHoàn cảnh sử dụng: Rất thích hợp để mặc đi tập gym, chạy bộ, tham gia các hoạt động thể thao ngoài trời hoặc mặc như một set đồ streetwear cá tính để đi cafe, dạo phố cùng bạn bè.', '1778064548_69fb1ca43e34e_set-the-thao-spider-quan-cargo-den-manh-me-1.jpg,1778064548_69fb1ca43e226_set-the-thao-spider-quan-cargo-den-manh-me-2.jpg,1778064548_69fb1ca43e017_set-the-thao-spider-quan-cargo-den-manh-me-3.jpg,1778064548_69fb1ca43de06_set-the-thao-spider-quan-cargo-den-manh-me-4.jpg', 18, 11, 'Phong Cách', 10, 0, '2026-05-06 17:49:15', 'male'),
(140, 'Set Áo Polo Thể Thao & Quần Short Cargo – Trắng Đen Y2K Dễ Thương', 79000.00, 0, 40000.00, 'Mô tả sản phẩm\r\nSự giao thoa hoàn hảo giữa tinh thần thể thao năng động và làn sóng Y2K đang dẫn đầu xu hướng. Với thiết kế áo polo form rộng phối màu đáng yêu kết hợp cùng quần short túi hộp bụi bặm, set đồ mang đến một diện mạo vừa cá tính, vừa có nét tinh nghịch, dễ thương rất riêng cho các bạn nam.\r\n\r\nSet đồ bao gồm: * 01 Áo polo cổ bẻ thể thao, khóa kéo trước ngực phối họa tiết graphic \"Newizard\" hiện đại.\r\n\r\n01 Quần short Cargo (quần đùi túi hộp) nhiều ngăn cá tính, form suông thoải mái.\r\n\r\nPhong cách: Cyber Y2K, Streetwear, Sporty dễ thương, Blockette core.\r\n\r\nMàu sắc chủ đạo: Trắng và Đen tương phản nổi bật.\r\n\r\nĐặc điểm nổi bật:\r\n\r\nÁo polo được cắt phối mảng màu đen ở vai tạo cảm giác bờ vai rộng, chất vải thoáng mát kết hợp cùng các chi tiết graphic ngôi sao mang hơi hướng tương lai nhưng lại rất trẻ trung, đáng yêu.\r\n\r\nQuần short kaki túi hộp dày dặn, vừa tạo độ đứng form vừa đem lại sự năng động, thoải mái khi vận động.\r\n\r\nTổng thể dễ dàng kết hợp cùng các phụ kiện đi kèm như thắt lưng mặt kim loại, kính mắt trắng, tai nghe chụp tai hay một đôi sneaker trắng thuần để hoàn thiện vibe \"vừa ngầu vừa bảnh\".\r\n\r\nHoàn cảnh sử dụng: Cực kỳ lý tưởng cho những buổi đi chơi, dạo phố, hẹn hò, đi cà phê cùng bạn bè, tham gia các buổi tiệc ngoài trời hoặc diện khi đi du lịch, chụp ảnh phong cách lofi/Y2K.', '1778064627_69fb1cf339696_set-polo-the-thao-short-cargo-trang-den-y2k-dang-yeu-1.jpg,1778064627_69fb1cf339048_set-polo-the-thao-short-cargo-trang-den-y2k-dang-yeu-2.jpg,1778064627_69fb1cf338881_set-polo-the-thao-short-cargo-trang-den-y2k-dang-yeu-3.jpg', 7, 12, 'Phong Cách', 26, 0, '2026-05-06 17:50:34', 'male'),
(141, 'Set Áo Thun Kẻ Ngang & Quần Short Parachute – Xanh Navy Streetwear Dễ Thương', 89000.00, 0, 45000.00, 'Mang đến vẻ ngoài trẻ trung và đầy sức sống, set đồ là sự kết hợp hoàn hảo giữa họa tiết kẻ ngang kinh điển và form dáng rộng rãi hiện đại. Đây là lựa chọn lý tưởng cho những chàng trai yêu thích phong cách tối giản nhưng vẫn muốn toát lên vẻ đáng yêu, năng động.\r\n\r\nSet đồ bao gồm:\r\n\r\n01 Áo thun cổ tròn tay lỡ với họa tiết kẻ ngang phối các tông màu xanh navy, xanh ghi và trắng kem.\r\n\r\n01 Quần short Parachute (vải dù) màu đen, thiết kế túi hộp (cargo) sáng tạo và đai thắt lưng tinh tế.\r\n\r\nPhong cách: Streetwear, Casual, Urban Style, \"Boyfriend\" Style.\r\n\r\nMàu sắc chủ đạo: Xanh Navy, Trắng Kem và Đen.\r\n\r\nĐặc điểm nổi bật:\r\n\r\nÁo thun kẻ ngang không bao giờ lỗi mốt, tạo cảm giác thân thiện và đầy đặn cho vóc dáng.\r\n\r\nQuần short vải dù nhẹ, thoáng mát, thiết kế túi hộp mang lại vẻ ngoài đa năng và khỏe khoắn.\r\n\r\nSet đồ cực kỳ \"dễ tính\", khi phối cùng các phụ kiện như mũ len (beanie), túi đeo chéo và sneaker sẽ tạo nên một tổng thể vô cùng hài hòa và có gu.\r\n\r\nHoàn cảnh sử dụng: Phù hợp cho các bạn nam diện đi học, đi chơi, dạo phố, đi du lịch hoặc tham gia các hoạt động ngoại khóa ngoài trời.', '1778064702_69fb1d3ea5369_set-ao-thun-ke-ngang-quan-short-du-den-de-thuong-1.jpg,1778064702_69fb1d3ea4ff8_set-ao-thun-ke-ngang-quan-short-du-den-de-thuong-2.jpg,1778064702_69fb1d3ea4d18_set-ao-thun-ke-ngang-quan-short-du-den-de-thuong-3.jpg', 9, 12, 'Phong Cách', 17, 0, '2026-05-30 17:51:49', 'male'),
(142, 'Set Áo Polo Retro Ba Sọc & Quần Jean Ống Rộng – Trắng Đỏ Năng Động Dễ Thương', 99000.00, 0, 50000.00, 'Khơi dậy tinh thần cổ điển với sự kết hợp mang đậm hơi hướng Blokecore – xu hướng thời trang lấy cảm hứng từ trang phục bóng đá cổ điển. Set đồ mang lại vẻ ngoài vừa thanh lịch, vừa tinh nghịch, cực kỳ phù hợp cho những chàng trai yêu thích vẻ đẹp nam tính nhưng vẫn giữ được nét trẻ trung, gần gũi.\r\n\r\nSet đồ bao gồm:\r\n\r\n01 Áo Polo Retro: Thiết kế cổ bẻ thanh lịch, phối màu trắng tinh khôi cùng chi tiết ba sọc đỏ nổi bật ở tay áo và cổ áo.\r\n\r\n01 Quần Jean Baggy: Dáng ống rộng thoải mái, màu xanh denim sáng (Light Blue) mang lại cảm giác nhẹ nhàng, dễ chịu.\r\n\r\nPhong cách: Retro Sporty, Blokecore, Vintage Streetwear.\r\n\r\nMàu sắc chủ đạo: Trắng, Đỏ và Xanh Denim sáng.\r\n\r\nĐặc điểm nổi bật:\r\n\r\nÁo polo chất liệu thun cotton thoáng mát, thấm hút mồ hôi tốt, tạo form dáng đứng nhưng vẫn mềm mại.\r\n\r\nQuần jean ống rộng giúp che khuyết điểm chân tốt và tạo sự thoải mái tối đa khi di chuyển.\r\n\r\nSự phối hợp giữa tông màu nóng (đỏ) và lạnh (xanh sáng) tạo nên một tổng thể hài hòa, thu hút ánh nhìn một cách tự nhiên.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho các buổi hẹn hò, đi cà phê chụp ảnh, dạo phố cuối tuần hoặc tham gia các hoạt động ngoại khóa ngoài trời.', '1778064788_69fb1d94e419a_set-polo-retro-trang-do-quan-jean-ong-rong-1.jpg,1778064788_69fb1d94e389d_set-polo-retro-trang-do-quan-jean-ong-rong-2.jpg,1778064788_69fb1d94e30a0_set-polo-retro-trang-do-quan-jean-ong-rong-3.jpg', 4, 12, 'Phong Cách', 36, 0, '2026-05-06 17:53:15', 'male'),
(143, 'Set Blazer Double-Breasted & Quần Âu – Xanh Navy Phối Trắng Lịch Lãm Dự Tiệc', 319000.00, 10, 159500.00, 'Set đồ bao gồm:\r\n\r\n01 Áo khoác Blazer khuy đúp (Double-Breasted): Thiết kế 6 khuy sang trọng, màu xanh Navy đậm (đã kèm sẵn áo sơ mi sọc xanh và cà vạt chấm bi đồng điệu bên trong).\r\n\r\n01 Quần âu dáng ôm vừa (Slim-fit Trousers): Màu trắng tinh khôi, đường ly quần được là phẳng phiu, tôn dáng và kéo dài chân hiệu quả.\r\n\r\nPhong cách: Gentleman, Sartorial, Elegant, Luxury Event.\r\n\r\nMàu sắc chủ đạo: Xanh Navy và Trắng.\r\n\r\nĐặc điểm nổi bật:\r\n\r\nÁo Blazer với thiết kế khuy đúp giúp định hình form dáng tốt, tạo hiệu ứng bờ vai vững chãi và vòng eo gọn gàng cho phái mạnh.\r\n\r\nChất liệu vải tuyết mưa hoặc kaki hàn cao cấp dày dặn, giữ form cực chuẩn, không lo bị nhăn nhúm khi ngồi tiệc lâu.\r\n\r\nQuy tắc phối màu tương phản giữa thân trên trầm ấm và thân dưới sáng màu mang lại hơi thở thời trang Ý (Sprezzatura) vừa phóng khoáng vừa đẳng cấp.\r\n\r\nOutfit cực kỳ hoàn hảo khi kết hợp cùng các phụ kiện như đồng hồ dây da cổ điển và một đôi giày Loafer đen bóng bẩy.\r\n\r\nHoàn cảnh sử dụng: Là lựa chọn đẳng cấp nhất dành cho các bạn nam đi dự tiệc cưới, tiệc cuối năm (Year End Party), tham gia các sự kiện, hội nghị lớn hoặc các buổi tối hẹn hò tại nhà hàng 5 sao.', '1778064990_69fb1e5eb5f85_set-blazer-khuy-dup-quan-au-trang-du-tiec-1.jpg,1778064990_69fb1e5eb5aaa_set-blazer-khuy-dup-quan-au-trang-du-tiec-2.jpg,1778064990_69fb1e5eb54ff_set-blazer-khuy-dup-quan-au-trang-du-tiec-3.jpg,\r\n1778064990_69fb1e5eb3bfe_set-blazer-khuy-dup-quan-au-trang-xanh-pastel-4.jpg,1778064990_69fb1e5eb4a62_set-blazer-khuy-dup-quan-au-trang-xanh-pastel-1.jpg,1778064990_69fb1e5eb44ee_set-blazer-khuy-dup-quan-au-trang-xanh-pastel-2.jpg', 15, 15, 'Theo Dịp', 16, 0, '2026-05-06 17:56:37', 'male'),
(144, 'Set Áo Vest Gilet Đúp & Sơ Mi Lụa – Đỏ Rượu Phối Đen Huyền Bí Dự Tiệc', 299000.00, 10, 90000.00, 'Mô tả sản phẩm\r\nHóa thân thành một quý tộc thời hiện đại với set đồ mang đậm dấu ấn phong cách Dark Academia pha chút Gothic lãng mạn. Sự kết hợp đầy ma mị giữa sắc đỏ rượu vang nồng nàn và màu đen tuyền huyền bí tạo nên một diện mạo vô cùng lôi cuốn, quyền lực và đẳng cấp, giúp bạn trở thành tâm điểm của mọi ánh nhìn trong đêm tiệc.\r\n\r\nSet đồ bao gồm:\r\n\r\n01 Áo sơ mi dài tay: Chất liệu lụa satin mượt mà, mang sắc đỏ rượu (Burgundy) sang trọng với phần cổ bẻ đứng chỉn chu.\r\n\r\n01 Áo gilet khuy đúp (Double-Breasted Vest): Màu đen tối giản, thiết kế ôm sát vóc dáng giúp tôn lên vòng ngực và bờ vai săn chắc.\r\n\r\n01 Áo khoác Blazer dáng dài (Overcoat): Form suông rộng phóng khoáng màu đen, tạo lớp layering đầy chiều sâu thời trang.\r\n\r\n01 Quần âu ống rộng (Wide-leg Trousers): Màu đen, cạp cao tôn dáng và kéo dài chân, mang lại bước đi vô cùng uyển chuyển, lãng tử.\r\n\r\nPhong cách: Dark Academia, Gothic Gentleman, Vampire Core, Sartorial.\r\n\r\nMàu sắc chủ đạo: Đỏ Rượu (Burgundy) và Đen Tuyền.\r\n\r\nĐặc điểm nổi bật:\r\n\r\nChất liệu sơ mi lụa satin có độ bóng nhẹ kết hợp cùng chất vải gilet, quần âu dày dặn tạo hiệu ứng tương phản chất liệu cực kỳ cao cấp.\r\n\r\nThiết kế gilet khuy đúp kết hợp quần ống rộng mang đậm hơi thở thời trang cổ điển hoàng gia châu Âu nhưng được biến tấu đầy hiện đại.\r\n\r\nOutfit trở nên hoàn hảo, ma mị khi phối cùng các phụ kiện như thắt lưng da đen, nhẫn bạc đính đá đỏ và một đôi giày Tây Oxford đen bóng loáng.\r\n\r\nHoàn cảnh sử dụng: Là lựa chọn độc bản và đẳng cấp nhất cho các buổi tiệc tối sang trọng, tiệc hóa trang (Halloween), sự kiện nghệ thuật, dạ tiệc cuối năm (Prom/Year End Party) hoặc các buổi hẹn hò lãng mạn tại các lounge, nhà hàng mang không gian cổ điển.', '1778065174_69fb1f16156f5_set-gilet-khuy-dup-so-mi-do-ruou-du-tiec-1.jpg,1778065174_69fb1f16150a2_set-gilet-khuy-dup-so-mi-do-ruou-du-tiec-2.jpg,1778065174_69fb1f16149b4_set-gilet-khuy-dup-so-mi-do-ruou-du-tiec-3.jpg,1778065174_69fb1f161428b_set-gilet-khuy-dup-so-mi-do-ruou-du-tiec-4.jpg\r\n', 12, 15, 'Theo Dịp', 12, 0, '2026-05-06 17:59:40', 'male'),
(145, 'Set Sơ Mi Hawaii Họa Tiết Cây Dừa & Quần Short – Đen Tropical Phóng Khoáng Du Lịch', 99000.00, 0, 49500.00, 'Mô tả sản phẩm\r\nSẵn sàng cho những chuyến vi vu đầy nắng và gió biển với set đồ mang đậm hơi thở Tropical (Nhiệt đới) phóng khoáng. Sự kết hợp giữa chiếc sơ mi họa tiết cây dừa đặc trưng và chiếc quần short thun mát mẻ mang đến cho phái mạnh diện mạo tự do, năng động và vô cùng thư thái đúng chất nghỉ dưỡng.\r\n\r\nSet đồ bao gồm:\r\n\r\n01 Áo sơ mi Hawaii tay ngắn: Màu đen chủ đạo, in họa tiết cây dừa màu be nhã nhặn, thiết kế cổ bẻ rộng phóng khoáng (đã phối kèm một chiếc áo thun trơn màu trắng mỏng nhẹ lót bên trong tạo độ layer).\r\n\r\n01 Quần short thun/dù cao cấp: Màu đen tuyền, thiết kế cạp chun co giãn thoải mái có dây rút tinh tế, chiều dài ngang đùi năng động.\r\n\r\nPhong cách: Resortwear, Tropical Streetwear, Beach Casual.\r\n\r\nMàu sắc chủ đạo: Đen, Trắng kem và điểm nhấn họa tiết màu Be.\r\n\r\nĐặc điểm nổi bật:\r\n\r\nÁo sơ mi chất liệu voan lụa hoặc đũi Hàn siêu nhẹ, mềm mại và bay rủ nhẹ nhàng, mang lại cảm giác mát lạnh ngay khi mặc.\r\n\r\nLối phối layer mở cúc áo sơ mi lộ áo thun trắng bên trong giúp set đồ trông có gu, thời trang hơn và lên hình check-in cực kỳ \"ăn ảnh\".\r\n\r\nThiết kế hướng đến sự tiện dụng tối đa với chất vải ít nhăn, dễ dàng gấp gọn vào vali du lịch. Outfit hoàn thiện tuyệt đối khi đi kèm các phụ kiện như kính mát đen vuông cá tính và một đôi dép sandal hai quai đế trấu thời thượng.\r\n\r\nHoàn cảnh sử dụng: Là lựa chọn điểm 10 cho những chuyến đi du lịch biển, dạo phố đi bộ tại các thành phố du lịch, đi resort nghỉ dưỡng, tham gia các buổi tiệc bể bơi (Pool party) hoặc diện đi cà phê cuối tuần cùng bạn bè trong thời tiết mùa hè.', '1778068584_69fb2c687d5e9_set-so-mi-hawaii-cay-dua-quan-short-den-du-lich-1.jpg,1778068584_69fb2c687cc00_set-so-mi-hawaii-cay-dua-quan-short-den-du-lich-2.jpg,1778068584_69fb2c687c1a2_set-so-mi-hawaii-cay-dua-quan-short-den-du-lich-3.jpg', 9, 16, 'Theo Dịp', 1, 0, '2026-05-06 18:56:32', 'male'),
(146, 'Set Áo Polo Len Tăm Kẻ Sọc & Quần Short Đũi – Be Phối Đỏ Kem Nhẹ Nhàng Du Lịch', 119000.00, 0, 59500.00, 'Định hình phong cách nghỉ dưỡng sang xịn và tinh tế với set đồ mang hơi hướng Quiet Luxury pha chút Vintage. Sự kết hợp giữa tông màu be ấm áp chủ đạo cùng họa tiết kẻ sọc thanh lịch mang đến cho phái mạnh diện mạo lãng tử, thư giãn nhưng vẫn vô cùng chỉn chu khi check-in tại các điểm đến du lịch.\r\n\r\nSet đồ bao gồm:\r\n\r\n01 Áo Polo dệt kim/len tăm: Thiết kế cổ bẻ chữ V không khuy (Johnny collar) phóng khoáng, phối họa tiết kẻ sọc dọc màu đỏ đô và beige cổ điển.\r\n\r\n01 Quần short đũi/linen: Màu be (khaki nhạt) đồng điệu, thiết kế cạp chun co giãn thoải mái có dây rút tiện lợi.\r\n\r\nPhong cách: Resortwear, Vintage Casual, Old Money Esthetic, Beachside Chic.\r\n\r\nMàu sắc chủ đạo: Be (Beige), Trắng Kem và Đỏ Đô.\r\n\r\nĐặc điểm nổi bật:\r\n\r\nÁo polo chất liệu len tăm dệt kim mỏng nhẹ, có độ thoáng khí cao và thấm hút tốt, vừa ôm nhẹ tôn dáng vừa tạo cảm giác cao cấp.\r\n\r\nQuần short đũi mềm mại, mộc mạc mang lại sự mát mẻ tối đa cho những ngày hè oi ả hoặc khi đi dạo trên bờ cát.\r\n\r\nSự đồng bộ về tông màu Earth-tone (màu sắc tự nhiên của đất) giúp set đồ vô cùng dịu mắt và tôn da. Outfit sẽ đạt điểm tuyệt đối khi đi cùng các phụ kiện đi kèm như kính mát mắt vuông gọng kim loại vàng đen sang chảnh và một đôi dép slide (dép bánh mì) màu be tối giản, năng động.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho các chuyến du lịch biển, nghỉ dưỡng tại resort, đi dạo phố cổ, tham gia các buổi tiệc nướng BBQ ngoài trời hoặc diện đi cà phê lộng gió cùng bạn bè.', '1778068669_69fb2cbd8d051_set-polo-len-tam-ke-soc-quan-short-dui-be-du-lich-1.jpg,1778068669_69fb2cbd8ca07_set-polo-len-tam-ke-soc-quan-short-dui-be-du-lich-2.jpg,1778068669_69fb2cbd8c2cd_set-polo-len-tam-ke-soc-quan-short-dui-be-du-lich-3.jpg', 12, 16, 'Theo Dịp', 4, 0, '2026-04-06 18:57:56', 'male'),
(147, 'Set Áo Sơ Mi Đũi Cổ Ve Rộng & Quần Suông Dài – Xanh Bạc Hà Phối Kem Lãng Tử Du Lịch', 119000.00, 0, 59500.00, 'Tận hưởng kỳ nghỉ trọn vẹn với diện mạo mộc mạc, tinh tế nhưng không kém phần thời thượng. Set đồ là sự kết hợp hoàn hảo giữa tông màu xanh bạc hà dịu mát và màu kem trang nhã trên nền chất liệu đũi (linen) – chất liệu \"vàng\" cho những chuyến hành trình du lịch dạo phố hay nghỉ dưỡng.\r\n\r\nSet đồ bao gồm:\r\n\r\n01 Áo sơ mi đũi tay ngắn: Thiết kế cổ ve rộng phóng khoáng (camp collar), màu xanh bạc hà (Mint Green) dịu mắt và trẻ trung.\r\n\r\n01 Quần dài ống suông rộng: Màu trắng kem (Off-white), thiết kế cạp chun thoải mái giúp người mặc dễ dàng di chuyển.\r\n\r\nPhong cách: Resortwear, Casual Lãng Tử, Minimalist Travel Style.\r\n\r\nMàu sắc chủ đạo: Xanh Bạc Hà và Trắng Kem.\r\n\r\nĐặc điểm nổi bật:\r\n\r\nChất liệu vải đũi nhăn tự nhiên, nhẹ, thoáng khí tối đa và thấm hút mồ hôi cực tốt, tạo cảm giác bay bổng khi đón gió biển.\r\n\r\nForm dáng quần ống suông dài kết hợp cùng áo sơ mi cổ mở giúp tôn lên chiều cao và mang lại vẻ ngoài lãng tử, tự do.\r\n\r\nOutfit được nâng tầm thời thượng khi phối cùng các phụ kiện tone-sur-tone như kính mát gọng vuông mắt xanh, đồng hồ dây kim loại mặt xanh lịch lãm và một đôi giày sneaker trắng tối giản.\r\n\r\nHoàn cảnh sử dụng: Cực kỳ lý tưởng cho những chuyến du lịch nghỉ dưỡng, dạo phố cổ (như Hội An, Phú Quốc), đi cà phê check-in không gian ngoài trời, dạo bờ biển buổi chiều tối hoặc các buổi tiệc tối nhẹ nhàng tại resort.', '1778068780_69fb2d2c20690_set-so-mi-dui-co-ve-quan-suong-xanh-bac-ha-1.jpg,1778068780_69fb2d2c2000f_set-so-mi-dui-co-ve-quan-suong-xanh-bac-ha-2.jpg,1778068780_69fb2d2c1fc49_set-so-mi-dui-co-ve-quan-suong-xanh-bac-ha-3.jpg', 8, 16, 'Theo Dịp', 3, 0, '2026-05-30 18:59:47', 'male'),
(148, 'Set Áo Thun Body & Chân Váy Nhung – Than Tím Phối Vàng Pastel Năng Động', 129000.00, 0, 65000.00, 'Set Áo Thun Body & Chân Váy Nhung là sự lựa chọn tuyệt vời cho những buổi đi chơi cần sự thoải mái nhưng vẫn phải đảm bảo tính thời trang. Sự kết hợp giữa tông màu trầm của áo và sắc vàng bơ của chân váy tạo nên một tổng thể trẻ trung, nổi bật và vô cùng hút mắt.\r\n\r\nSet đồ gồm: 01 Áo thun cổ tròn ôm body và 01 Chân váy nhung tăm dáng ngắn.\r\n\r\nPhong cách: Sporty Chic (Thể thao sang chảnh), trẻ trung và hiện đại.\r\n\r\nMàu sắc chủ đạo: Than tím (Deep Purple/Navy) phối cùng Vàng Pastel (Lemon Yellow).\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo thun: Chất liệu thun co giãn ôm sát đường cong cơ thể, thiết kế tối giản với điểm nhấn là logo tinh tế ở ngực, giúp tôn dáng và cực kỳ dễ phối đồ.\r\n\r\nChân váy: Chất liệu nhung tăm (corduroy) bền đẹp, thiết kế dáng chữ A ngắn giúp kéo dài đôi chân. Phần cạp váy có dây thắt nơ xinh xắn, vừa tạo điểm nhấn nữ tính vừa giúp điều chỉnh vòng eo linh hoạt.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho các hoạt động đi chơi dạo phố, hẹn hò cafe, đi xem phim hoặc tham gia các hoạt động ngoại khóa năng động cùng bạn bè.', '1778068891_69fb2d9b92f3b_set-ao-thun-body-chan-vay-nhung-than-tim-vang-pastel-1.png,1778068891_69fb2d9b926f9_set-ao-thun-body-chan-vay-nhung-than-tim-vang-pastel-2.png,1778068891_69fb2d9b91dca_set-ao-thun-body-chan-vay-nhung-than-tim-vang-pastel-3.png,1778068891_69fb2d9b91497_set-ao-thun-body-chan-vay-nhung-than-tim-vang-pastel-4.png', 7, 13, 'Theo Dịp', 2, 0, '2026-05-06 19:01:38', 'female'),
(149, 'Set Áo Thun Lệch Vai & Quần Short Jean – Xanh Than Cá Tính Streetstyle', 119000.00, 0, 60000.00, 'Set Áo Thun Lệch Vai & Quần Short Jean mang đến một diện mạo phóng khoáng, đậm chất đường phố dành cho những cô nàng yêu thích sự tự do và năng động. Đây là bộ trang phục \"must-have\" để làm mới phong cách hàng ngày với những điểm nhấn cắt xẻ đầy ngẫu hứng.\r\n\r\nSet đồ gồm: 01 Áo thun in chữ dáng lệch vai và 01 Quần short jean ống rộng.\r\n\r\nPhong cách: Streetwear, Casual năng động và một chút hơi hướng Âu Mỹ.\r\n\r\nMàu sắc chủ đạo: Xanh Than (Navy) và Xanh Denim sáng.\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo thun: Form áo rộng rãi, điểm nhấn là thiết kế lệch vai khoe khéo xương quai xanh gợi cảm nhưng vẫn giữ được nét tinh nghịch. Họa tiết in chữ \"EAST COAST\" mang lại vẻ ngoài trẻ trung, hiện đại.\r\n\r\nQuần Short Jean: Dáng quần basic với ống rộng thoải mái, chất jean bền đẹp, độ dài vừa phải giúp tôn dáng và cực kỳ dễ vận động.\r\n\r\nHoàn cảnh sử dụng: Là lựa chọn hàng đầu cho những buổi đi chơi phố, du lịch, dã ngoại hoặc tham gia các hoạt động ngoài trời, dạo cafe cùng bạn bè.', '1778068994_69fb2e028d2ff_set-ao-thun-lech-vai-quan-short-jean-xanh-than-streetstyle-1.jpg,1778068994_69fb2e028cd8f_set-ao-thun-lech-vai-quan-short-jean-xanh-than-streetstyle-2.jpg,1778068994_69fb2e028c77d_set-ao-thun-lech-vai-quan-short-jean-xanh-than-streetstyle-3.jpg,1778068994_69fb2e028be4b_set-ao-thun-lech-vai-quan-short-jean-xanh-than-streetstyle-4.jpg', 6, 13, 'Theo Dịp', 16, 0, '2026-05-06 19:03:21', 'female'),
(150, 'Set Áo Baby Tee & Quần Short Jean Culottes – Đỏ Rượu Phối Denim Y2K', 119000.00, 0, 60000.00, 'Set Áo Baby Tee & Quần Short Jean Culottes là bản phối hoàn hảo cho những tín đồ thời trang yêu thích sự phá cách và hơi hướng retro. Sự kết hợp giữa tông màu đỏ rượu (burgundy) thời thượng cùng dáng quần denim ống rộng tạo nên một tổng thể vừa năng động, vừa đậm chất \"streetstyle\" đường phố.\r\n\r\nSet đồ gồm: 01 Áo thun Baby Tee ôm dáng và 01 Quần short jean dáng dài ống rộng (Culottes).\r\n\r\nPhong cách: Y2K Aesthetic, Urban Streetwear, năng động và cá tính.\r\n\r\nMàu sắc chủ đạo: Đỏ Rượu (Burgundy) và Xanh Denim đậm (Dark Blue).\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo Baby Tee: Thiết kế cổ tròn, tay ngắn ôm sát giúp tôn lên đường nét cơ thể một cách tinh tế. Chất liệu thun gân mềm mại, co giãn tốt, mang lại sự thoải mái tối đa khi vận động.\r\n\r\nQuần Short Jean: Dáng quần lửng ống rộng độc đáo, được xử lý màu denim loang nhẹ tạo vẻ ngoài bụi bặm, cá tính. Form dáng này không chỉ giúp che khuyết điểm mà còn cực kỳ tôn chiều cao khi phối cùng các dòng giày sneaker.\r\n\r\nHoàn cảnh sử dụng: Là outfit lý tưởng cho những buổi đi chơi phố, dạo sắm, tham gia các buổi workshop sáng tạo, hay tụ tập bạn bè tại những quán cafe có không gian hiện đại, cá tính.', '1778069114_69fb2e7ae8a22_set-ao-baby-tee-quan-short-jean-culottes-do-ruou-y2k-1.jpg,1778069114_69fb2e7ae8701_set-ao-baby-tee-quan-short-jean-culottes-do-ruou-y2k-2.jpg,1778069114_69fb2e7ae8376_set-ao-baby-tee-quan-short-jean-culottes-do-ruou-y2k-3.jpg,1778069114_69fb2e7ae7eee_set-ao-baby-tee-quan-short-jean-culottes-do-ruou-y2k-4.jpg', 7, 13, 'Theo Dịp', 11, 0, '2026-05-06 19:05:21', 'female'),
(151, 'Đầm Cúp Ngực Lụa Satin Dáng Xòe – Xanh Navy Sang Trọng Quyến rũ', 199000.00, 0, 100000.00, 'Đầm Cúp Ngực Lụa Satin Dáng Xòe là sự lựa chọn hoàn hảo cho những quý cô muốn tỏa sáng với vẻ đẹp kiêu sa và quyền lực. Thiết kế chú trọng vào việc tôn vinh vóc dáng nhờ sự kết hợp giữa chất liệu lụa satin cao cấp và form dáng xòe tầng bồng bềnh, tạo nên một tổng thể vô cùng thu hút.\r\n\r\nSản phẩm bao gồm: 01 Đầm liền thân cúp ngực.\r\n\r\nPhong cách: Elegant (Sang trọng), Luxury (Cao cấp) và đầy nữ tính.\r\n\r\nMàu sắc chủ đạo: Xanh Navy (Xanh than) – màu sắc đại diện cho sự quý phái và vô cùng tôn da.\r\n\r\nChi tiết thiết kế:\r\n\r\nDáng cúp ngực: Thiết kế ôm sát vòng một giúp khoe khéo bờ vai trần và xương quai xanh thanh mảnh. Phần eo được nhấn gọn bởi một dải nơ mảnh đồng màu, tạo hiệu ứng vòng eo \"con kiến\".\r\n\r\nTùng váy xòe tầng: Chân váy được may từ chất liệu lụa satin có độ bóng nhẹ sang trọng, xếp lớp tầng tinh tế tạo độ phồng tự nhiên. Lớp lót lưới (tulle) bên dưới giúp chiếc váy luôn giữ được form dáng bồng bềnh như một đóa hoa.\r\n\r\nChất liệu: Lụa Satin cao cấp phối lưới, mềm mại và bắt sáng tốt dưới ánh đèn tiệc.\r\n\r\nHoàn cảnh sử dụng: Sản phẩm là lựa chọn tối ưu cho các buổi tiệc tối (Evening Party), tiệc cưới, sự kiện quan trọng (Gala Dinner) hoặc những buổi hẹn hò sang trọng.', '1778069202_69fb2ed24cf78_dam-cup-nguc-lua-satin-dang-xoe-xanh-navy-sang-trong-1.jpg,1778069202_69fb2ed24c13f_dam-cup-nguc-lua-satin-dang-xoe-xanh-navy-sang-trong-2.jpg,1778069202_69fb2ed24c835_dam-cup-nguc-lua-satin-dang-xoe-xanh-navy-sang-trong-3.jpg', 7, 15, 'Theo Dịp', 22, 0, '2026-05-06 19:06:49', 'female'),
(152, 'Set Áo Yếm Cổ Đổ & Chân Váy Sequin – Trắng Gold Sang Chảnh Dự Tiệc', 309000.00, 5, 93000.00, 'Set Áo Yếm Cổ Đổ & Chân Váy Sequin là bản phối hoàn hảo dành cho những tâm điểm của bữa tiệc. Sự kết hợp giữa nét thanh lịch của sắc trắng và sự hào nhoáng của ánh kim (gold) tạo nên một diện mạo đẳng cấp, giúp bạn tự tin tỏa sáng dưới mọi ánh đèn.\r\n\r\nSet đồ gồm: 01 Áo yếm cổ đổ ôm body và 01 Chân váy mini đính đá/sequin cao cấp.\r\n\r\nPhong cách: Glamorous (Quyến rũ), Luxury (Sang trọng) và Party-ready.\r\n\r\nMàu sắc chủ đạo: Trắng Tinh Khôi phối Vàng Ánh Kim (Gold).\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo Yếm: Thiết kế cổ đổ (cowl neck) mềm mại tạo vẻ ngoài nữ tính, gợi cảm. Kiểu dáng yếm khoe khéo bờ vai thon và phần lưng trần quyến rũ. Chất liệu thun lụa cao cấp có độ bóng nhẹ, ôm sát tôn dáng.\r\n\r\nChân Váy Sequin: Điểm nhấn đắt giá với hàng nghìn mảnh sequin lấp lánh được đính kết tỉ mỉ, tạo hiệu ứng bắt sáng cực tốt. Dáng váy ngắn trẻ trung giúp đôi chân trông dài và thon gọn hơn.\r\n\r\nHoàn cảnh sử dụng: Lựa chọn hàng đầu cho các buổi tiệc tối (Evening Party), đi Club/Bar, tiệc sinh nhật sang trọng hoặc các sự kiện lễ hội cuối năm.', '1778069345_69fb2f6175105_set-ao-yem-co-do-chan-vay-sequin-trang-gold-du-tiec-1.png,1778069345_69fb2f6174d36_set-ao-yem-co-do-chan-vay-sequin-trang-gold-du-tiec-2.jpg,1778069345_69fb2f6174918_set-ao-yem-co-do-chan-vay-sequin-trang-gold-du-tiec-4.png', 8, 15, 'Theo Dịp', 10, 0, '2026-05-06 19:09:12', 'female'),
(153, 'Đầm Cúp Ngực Floral Tầng – Ghi Sáng Phối Voan Kiêu Kỳ', 249000.00, 5, 75000.00, 'Đầm Cúp Ngực Floral Tầng là sự kết hợp tinh tế giữa nghệ thuật in hoa chìm và kỹ thuật may tầng bồng bềnh, mang đến một vẻ đẹp thoát tục cho các quý cô trong những bữa tiệc sang trọng. Với tông màu ghi sáng trung tính nhưng vô cùng bắt mắt, thiết kế này giúp tôn vinh làn da và thần thái kiêu sa của người mặc.\r\n\r\nSản phẩm bao gồm: 01 Đầm liền thân dáng ngắn.\r\n\r\nPhong cách: Fairy-core (Thần tiên), tiểu thư sang chảnh và quyến rũ.\r\n\r\nMàu sắc chủ đạo: Ghi sáng (Light Grey) phối họa tiết hoa ẩn tinh tế.\r\n\r\nChi tiết thiết kế:\r\n\r\nDáng cúp ngực: Form áo corset ôm sát giúp định hình vòng eo, tôn vinh vòng một và bờ vai trần thanh mảnh.\r\n\r\nChân váy xếp tầng: Sử dụng chất liệu voan kính cao cấp xếp lớp tạo độ phồng nhẹ nhàng, uyển chuyển trong từng bước đi.\r\n\r\nHọa tiết: Hoa nhí in chìm mang hơi hướng cổ điển, tạo chiều sâu cho bề mặt vải và tăng thêm phần nghệ thuật cho bộ trang phục.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho các dịp dự tiệc cưới, tiệc sinh nhật, các buổi dạ tiệc tối hoặc sự kiện quan trọng đòi hỏi sự chỉn chu và nổi bật.', '1778069449_69fb2fc932236_dam-cup-nguc-floral-tang-ghi-sang-kieu-ky-1.jpg,1778069449_69fb2fc931801_dam-cup-nguc-floral-tang-ghi-sang-kieu-ky-2.jpg,1778069449_69fb2fc931dc5_dam-cup-nguc-floral-tang-ghi-sang-kieu-ky-3.jpg', 8, 15, 'Theo Dịp', 18, 0, '2026-05-06 19:10:56', 'female'),
(154, 'Đầm Cúp Ngực Denim Phối Bèo – Xám Khói Tiểu Thư Hẹn Hò', 159000.00, 10, 50000.00, 'Đầm Cúp Ngực Denim Phối Bèo là sự lựa chọn ngọt ngào cho những buổi hẹn hò lãng mạn. Với sự phá cách từ chất liệu denim cứng cáp nhưng lại mang form dáng xòe điệu đà, bộ trang phục giúp bạn trông vừa trẻ trung, vừa có nét kiêu kỳ của một nàng tiểu thư hiện đại.\r\n\r\nSản phẩm bao gồm: 01 Đầm liền thân cúp ngực.\r\n\r\nPhong cách: Coquette Aesthetic, tiểu thư sang chảnh, nhẹ nhàng.\r\n\r\nMàu sắc chủ đạo: Xám Khói (Smoky Grey) phối Trắng Tinh Khôi.\r\n\r\nChi tiết thiết kế:\r\n\r\nPhần ngực: Thiết kế cúp ngực đính nơ trắng to bản làm điểm nhấn, giúp tôn lên bờ vai trần và vòng một đầy đặn. Đường cắt corset ở thân áo giúp định hình vòng eo thon gọn.\r\n\r\nTùng váy: Dáng xòe rộng với lớp lót bèo nhún trắng xếp tầng bên dưới, tạo độ phồng tự nhiên và sự uyển chuyển khi di chuyển.\r\n\r\nChất liệu: Denim mềm cao cấp có độ đứng form vừa phải, kết hợp cùng voan mềm mại ở phần bèo nhún.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho dịp hẹn hò buổi tối, đi xem phim, dạo phố hoặc tham gia các buổi tiệc trà chiều sang trọng.', '1778069645_69fb308d27284_dam-cup-nguc-denim-phoi-beo-xam-khoi-tieu-thu-1 (1).jpg,1778069645_69fb308d26d89_dam-cup-nguc-denim-phoi-beo-xam-khoi-tieu-thu-2.jpg,1778069645_69fb308d26849_dam-cup-nguc-denim-phoi-beo-xam-khoi-tieu-thu-3.jpg', 11, 14, 'Theo Dịp', 23, 0, '2026-05-30 19:14:11', 'female'),
(155, 'Set Áo Tweed Crop Tay Bồng & Quần Short Cạp Cao – Kem Be Thanh Lịch Hẹn Hò', 119000.00, 10, 55000.00, 'Set Áo Tweed Crop & Quần Short Cạp Cao là sự lựa chọn hoàn hảo cho một buổi hẹn hò tinh tế. Bộ trang phục kết hợp giữa chất liệu Tweed sang trọng và form dáng hiện đại, mang đến vẻ ngoài vừa ngọt ngào, vừa thời thượng, giúp bạn ghi điểm tuyệt đối trong mắt đối phương.\r\n\r\nSet đồ gồm: 01 Áo Tweed dáng lửng (crop top) và 01 Quần short vải cạp cao kèm đai lưng.\r\n\r\nPhong cách: Elegant Chic (Thanh lịch), sang chảnh và nữ tính.\r\n\r\nMàu sắc chủ đạo: Màu Kem Be (Beige) phối Trắng Tinh Khôi, tạo cảm giác nhẹ nhàng và cực kỳ tôn da.\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo Tweed: Thiết kế tay bồng tiểu thư, cổ bẻ thanh lịch kết hợp cùng hàng khuy ngọc trai sang trọng. Dáng áo crop top giúp khéo léo khoe vòng eo thon gọn.\r\n\r\nQuần Short: Thiết kế cạp cao xếp ly (paper-bag waist) đi kèm thắt lưng đồng màu, tạo hiệu ứng đôi chân dài miên man. Chi tiết túi phối viền ngọc trai tạo sự đồng bộ hoàn hảo với áo.\r\n\r\nHoàn cảnh sử dụng: Rất phù hợp cho những buổi hẹn hò cafe sáng, ăn trưa tại nhà hàng, dạo phố cuối tuần hoặc những buổi gặp mặt thân mật cần sự chỉn chu.', '1778069842_69fb31527f07d_set-ao-tweed-crop-quan-short-cap-cao-kem-be-1.jpg,1778069842_69fb31527e9a0_set-ao-tweed-crop-quan-short-cap-cao-kem-be-2.jpg,1778069842_69fb31527d697_set-ao-tweed-crop-quan-short-cap-cao-kem-be-4 (1).jpg,1778069842_69fb31527ddee_set-ao-tweed-crop-quan-short-cap-cao-kem-be-4.jpg', 8, 14, 'Theo Dịp', 13, 0, '2026-05-06 19:17:29', 'female'),
(156, 'Đầm Hai Dây Cup Ngực – Trắng Tinh Khôi Dáng Xòe Tiểu Thư', 119000.00, 10, 60000.00, 'Đầm Hai Dây Cup Ngực là hiện thân của sự thuần khiết và nét nữ tính dịu dàng, cực kỳ phù hợp cho những buổi hẹn hò đầu tiên. Với thiết kế tối giản nhưng chú trọng vào form dáng, chiếc đầm giúp nàng toát lên vẻ ngoài thanh lịch, mong manh nhưng không kém phần cuốn hút.\r\n\r\nSản phẩm bao gồm: 01 Đầm liền thân hai dây.\r\n\r\nPhong cách: Minimalist (Tối giản), Romantic (Lãng mạn) và tiểu thư nhẹ nhàng.\r\n\r\nMàu sắc chủ đạo: Trắng Tinh Khôi (White) – sắc màu giúp tôn vinh vẻ đẹp tự nhiên và dễ dàng kết hợp với mọi loại phụ kiện.\r\n\r\nChi tiết thiết kế:\r\n\r\nPhần ngực: Thiết kế cúp ngực tinh tế kết hợp cùng hai dây mảnh giúp khoe khéo bờ vai trần và xương quai xanh gợi cảm.\r\n\r\nForm dáng: Vòng eo được nhấn cao tạo tỉ lệ cơ thể cân đối, kết hợp cùng chân váy xòe chữ A bồng bềnh, tạo nên nét uyển chuyển, duyên dáng trong mỗi bước đi.\r\n\r\nChất liệu: Vải cao cấp có độ đứng form nhẹ nhưng vẫn giữ được sự mềm mại, thoáng mát, mang lại cảm giác thoải mái suốt buổi hẹn.\r\n\r\nHoàn cảnh sử dụng: Là lựa chọn tuyệt vời cho các buổi hẹn hò ăn tối lãng mạn, đi dạo dưới ánh hoàng hôn trên biển, tham gia các buổi tiệc ngoài trời hoặc đi du lịch nghỉ dưỡng.', '1778069986_69fb31e295d59_dam-hai-day-cup-nguc-trang-tinh-khoi-tieu-thu-1.jpg,1778069986_69fb31e2955c2_dam-hai-day-cup-nguc-trang-tinh-khoi-tieu-thu-2.jpg,1778069986_69fb31e294c7e_dam-hai-day-cup-nguc-trang-tinh-khoi-tieu-thu-3.jpg', 8, 14, 'Theo Dịp', 22, 0, '2026-05-06 19:19:53', 'female'),
(157, 'Set Áo Sát Nách Cài Khuy & Quần Short – Xanh Sky Mát Mẻ Du Lịch', 119000.00, 5, 60000.00, 'Set Áo Sát Nách Cài Khuy & Quần Short là trang phục \"giải nhiệt\" hoàn hảo cho những chuyến du lịch mùa hè. Với chất liệu thoáng mát và tông màu xanh dịu mắt, bộ đồ không chỉ mang lại sự thoải mái tối đa khi di chuyển mà còn giúp nàng có những tấm hình \"sống ảo\" chuẩn phong cách tiểu thư nhẹ nhàng.\r\n\r\nSet đồ gồm: 01 Áo sát nách cài khuy trước và 01 Quần short vải cạp cao.\r\n\r\nPhong cách: Minimalist (Tối giản), Fresh & Sweet (Trẻ trung, ngọt ngào).\r\n\r\nMàu sắc chủ đạo: Xanh Sky (Xanh da trời nhạt) phối cùng Trắng Kem, tạo cảm giác thanh khiết và tràn đầy năng lượng.\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo sát nách: Thiết kế cổ vuông hiện đại, hàng khuy bọc đồng màu chạy dọc thân áo tạo điểm nhấn tinh tế. Chi tiết dây thắt nơ nhỏ xinh trên vai giúp tăng thêm vẻ nữ tính, điệu đà.\r\n\r\nQuần Short: Form quần chữ A cạp cao giúp tôn vòng eo và tạo cảm giác đôi chân thon dài hơn. Chất liệu vải mềm mại, có độ đứng form vừa phải, phù hợp cho các hoạt động dã ngoại.\r\n\r\nHoàn cảnh sử dụng: Lựa chọn lý tưởng để diện đi du lịch biển, đi dã ngoại, dạo phố ở những thành phố du lịch hoặc tham gia các hoạt động ngoài trời vào mùa hè.', '1778070086_69fb32461e324_set-ao-sat-nach-cai-khuy-quan-short-xanh-sky-du-lich-1.jpg,1778070086_69fb32461d976_set-ao-sat-nach-cai-khuy-quan-short-xanh-sky-du-lich-2.jpg,1778070086_69fb32461cf40_set-ao-sat-nach-cai-khuy-quan-short-xanh-sky-du-lich-3.jpg', 12, 16, 'Theo Dịp', 5, 0, '2026-05-06 19:21:32', 'female'),
(158, 'Set Áo Tube Rút Dây Hoa Văn & Quần Short Jean Tua Rua – Xanh Denim Phóng Khoáng', 129000.00, 5, 65000.00, 'Set Áo Tube & Quần Short Jean mang hơi hướng phong cách Boho-chic phối cùng nét hiện đại của Streetwear, là outfit không thể thiếu trong vali du lịch của những cô nàng cá tính. Với chất liệu denim bền bỉ và các chi tiết rút dây linh hoạt, bộ trang phục vừa giúp bạn tôn dáng triệt để, vừa mang lại sự thoải mái trong mọi hoạt động khám phá.\r\n\r\nSet đồ gồm: 01 Áo tube (áo quây) họa tiết hoa văn chìm và 01 Quần short jean rách tua rua.\r\n\r\nPhong cách: Travel Style, Cá tính, Phóng khoáng và Gợi cảm.\r\n\r\nMàu sắc chủ đạo: Xanh Denim (Blue Denim) với họa tiết hoa loang nghệ thuật.\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo Tube Rút Dây: Thiết kế áo quây ôm sát, có chi tiết rút dây ở giữa ngực tạo nếp nhăn gợi cảm. Phần gấu áo phối bèo voan bất đối xứng tạo độ bay bổng mỗi khi di chuyển.\r\n\r\nQuần Short Jean: Quần jean ngắn với phần gấu rách tua rua sành điệu. Điểm nhấn là chi tiết đan dây và rút nơ hai bên hông, phối cùng bèo voan dọc thân quần tạo sự đồng bộ hoàn hảo với áo.\r\n\r\nChất liệu: Vải mềm mại, có độ co giãn tốt giúp thoải mái khi vận động ngoài trời.\r\n\r\nHoàn cảnh sử dụng: Hoàn hảo cho các chuyến du lịch biển, dạo phố tại các thành phố du lịch, tham gia lễ hội âm nhạc ngoài trời hoặc chụp ảnh check-in phong cách \"Style Your Vibe\".', '1778070192_69fb32b0d2046_set-ao-tube-rut-day-quan-short-jean-xanh-denim-du-lich-1.jpg,1778070192_69fb32b0d1825_set-ao-tube-rut-day-quan-short-jean-xanh-denim-du-lich-2.jpg,1778070192_69fb32b0d0ee9_set-ao-tube-rut-day-quan-short-jean-xanh-denim-du-lich-3.jpg', 5, 16, 'Theo Dịp', 17, 0, '2026-05-06 19:23:19', 'female'),
(159, 'Set Áo Yếm Voan & Quần Short Jean – Xanh Ombre Bay Bổng Du Lịch', 119000.00, 5, 60000.00, 'Set Áo Yếm Voan & Quần Short Jean mang đến một diện mạo đầy tự do, phóng khoáng, cực kỳ phù hợp cho những khung hình du lịch rạng rỡ. Sự kết hợp giữa chất liệu voan tơ mềm mại và denim cá tính tạo nên một tổng thể đối lập đầy thú vị, giúp nàng vừa nữ tính lại vừa năng động.\r\n\r\nSet đồ gồm: 01 Áo yếm voan dáng xòe và 01 Quần short jean tua rua phối bèo.\r\n\r\nPhong cách: Boho-chic, du lịch biển, trẻ trung và bay bổng.\r\n\r\nMàu sắc chủ đạo: Xanh Sky Ombre (Xanh loang) dịu mắt phối cùng xanh Denim sáng.\r\n\r\nChi tiết thiết kế:\r\n\r\nÁo Yếm: Thiết kế cổ yếm cách điệu với chi tiết rút dây ngực tinh tế. Chất liệu voan mỏng nhẹ phối tầng tạo độ xòe mềm mại, bay bổng theo từng bước chân hoặc làn gió biển.\r\n\r\nQuần Short Jean: Form quần ngắn năng động, điểm nhấn là các đường rách tua rua bụi bặm và dải bèo voan dọc hai bên hông đồng điệu với áo, giúp đôi chân trông thon dài và nổi bật hơn.\r\n\r\nHoàn cảnh sử dụng: Là outfit \"vàng\" cho những chuyến đi biển, nghỉ dưỡng resort, dạo phố cổ hoặc tham gia các buổi party ngoài trời khi đi du lịch.', '1778070288_69fb3310b0d6e_set-ao-yem-voan-quan-short-jean-xanh-ombre-du-lich-1.jpg,1778070288_69fb3310b0252_set-ao-yem-voan-quan-short-jean-xanh-ombre-du-lich-2.jpg,1778070288_69fb3310af866_set-ao-yem-voan-quan-short-jean-xanh-ombre-du-lich-3.jpg', 10, 16, 'Theo Dịp', 32, 0, '2026-05-06 19:24:56', 'female');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL,
  `color` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `rented_quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `color`, `stock`, `rented_quantity`) VALUES
(24, 159, 'S', 'Xanh', 2, 0),
(25, 159, 'M', 'Xanh', 10, 0),
(28, 158, 'S', 'Hồng', 3, 0),
(29, 158, 'M', 'Hồng', 3, 0),
(30, 157, 'S', 'Xanh', 5, 0),
(31, 157, 'M', 'Xanh', 4, 0),
(32, 157, 'L', 'Xanh', 3, 0),
(33, 156, 'S', 'Trắng', 4, 0),
(34, 156, 'M', 'Trắng', 4, 0),
(35, 155, 'S', 'Be', 3, 0),
(36, 155, 'M', 'Be', 5, 0),
(37, 154, 'M', 'Xám', 6, 0),
(38, 154, 'L', 'Xám', 5, 0),
(39, 153, 'S', 'Xanh', 4, 0),
(40, 153, 'M', 'Xanh', 4, 0),
(41, 152, 'S', 'Vàng', 3, 0),
(42, 152, 'M', 'Vàng', 5, 0),
(43, 151, 'S', 'Xanh than', 3, 0),
(44, 151, 'L', 'Xanh than', 4, 0),
(45, 150, 'S', 'Đỏ', 2, 0),
(46, 150, 'L', 'Đỏ', 3, 0),
(47, 150, 'M', 'Đỏ', 2, 0),
(48, 149, 'M', 'Xanh than', 3, 0),
(49, 149, 'L', 'Xanh than', 3, 0),
(50, 148, 'S', 'Vàng', 4, 0),
(51, 148, 'M', 'Vàng', 3, 0),
(52, 147, 'M', 'Xanh', 5, 0),
(53, 147, 'L', 'Xanh', 3, 0),
(54, 146, 'L', 'Be', 6, 0),
(55, 146, 'XL', 'Be', 6, 0),
(56, 145, 'L', 'Đen', 4, 0),
(57, 145, 'XL', 'Đen', 5, 0),
(58, 144, 'L', 'Đen', 5, 0),
(59, 144, 'XL', 'Đen', 4, 0),
(60, 144, 'M', 'Đen', 3, 0),
(61, 143, 'M', 'Xanh', 3, 0),
(62, 143, 'L', 'Xanh', 5, 0),
(63, 143, 'XL', 'Đen', 3, 0),
(64, 143, 'M', 'Đen', 4, 0),
(65, 142, 'M', 'Trắng', 4, 0),
(66, 141, 'M', 'Đen', 4, 0),
(67, 141, 'L', 'Đen', 3, 0),
(68, 141, 'XL', 'Đen', 2, 0),
(69, 140, 'M', 'Đen', 2, 0),
(70, 140, 'L', 'Đen', 5, 0),
(71, 139, 'L', 'Đen', 5, 0),
(72, 139, 'XL', 'Đen', 7, 0),
(73, 139, 'XXL', 'Đen', 6, 0),
(74, 138, 'M', 'Đen', 4, 0),
(75, 138, 'L', 'Đen', 3, 0),
(78, 136, 'S', 'Nâu', 4, 0),
(79, 136, 'M', 'Nâu', 2, 0),
(80, 136, 'L', 'Nâu', 3, 0),
(81, 135, 'S', 'Xanh', 3, 0),
(82, 135, 'M', 'Xanh', 4, 0),
(83, 134, 'M', 'Xám', 4, 0),
(84, 134, 'L', 'Xám', 5, 0),
(85, 133, 'M', 'Xanh', 4, 0),
(86, 133, 'L', 'Xanh', 6, 0),
(87, 132, 'S', 'Vàng', 3, 0),
(88, 132, 'M', 'Vàng', 4, 0),
(89, 132, 'L', 'Vàng', 3, 0),
(90, 131, 'M', 'Đỏ', 4, 0),
(91, 131, 'L', 'Đỏ', 4, 0),
(92, 130, 'M', 'Nâu', 5, 0),
(93, 130, 'L', 'Nâu', 6, 0),
(94, 129, 'M', 'Xanh', 4, 0),
(95, 129, 'L', 'Xanh', 4, 0),
(96, 129, 'XL', 'Nâu', 4, 0),
(97, 129, 'XXL', 'Nâu', 5, 0),
(98, 129, 'M', 'Nâu', 3, 0),
(99, 128, 'M', 'Xanh', 5, 0),
(100, 128, 'M', 'Trắng', 4, 0),
(101, 128, 'L', 'Trắng', 6, 0),
(102, 127, 'M', 'Xanh', 5, 0),
(103, 127, 'L', 'Xanh', 7, 0),
(104, 127, 'M', 'Đỏ', 4, 0),
(105, 126, 'L', 'Đỏ', 4, 0),
(106, 126, 'M', 'Nâu', 4, 0),
(107, 125, 'M', 'Trắng', 5, 0),
(108, 125, 'S', 'Trắng', 4, 0),
(109, 124, 'M', 'Đen', 3, 1),
(110, 124, 'L', 'Đen', 4, 0),
(111, 124, 'M', 'Đỏ', 5, 0),
(112, 124, 'L', 'Đỏ', 2, 0),
(113, 123, 'L', 'Xanh', 3, 0),
(114, 123, 'XL', 'Xanh', 4, 0),
(115, 122, 'L', 'Xanh', 4, 0),
(116, 122, 'XL', 'Xanh', 3, 0),
(117, 122, 'L', 'Đỏ', 4, 0),
(118, 122, 'XL', 'Đỏ', 2, 0),
(119, 121, 'L', 'Xanh', 3, 0),
(120, 121, 'XL', 'Xanh', 4, 0),
(121, 121, 'XXL', 'Xanh', 3, 0),
(122, 120, 'L', 'Nâu', 4, 0),
(123, 120, 'XL', 'Nâu', 3, 0),
(124, 119, 'M', 'Xanh', 4, 0),
(125, 119, 'L', 'Xanh', 4, 0),
(126, 119, 'L', 'Trắng', 4, 0),
(127, 118, 'L', 'Đen', 4, 0),
(128, 118, 'L', 'Đỏ', 3, 0),
(129, 118, 'XL', 'Đỏ', 4, 0),
(130, 117, 'L', 'Đen', 3, 0),
(131, 117, 'XL', 'Đen', 4, 0),
(132, 117, 'L', 'Trắng', 3, 0),
(133, 116, 'L', 'Đỏ', 4, 0),
(134, 116, 'XL', 'Đỏ', 5, 0),
(135, 116, 'XL', 'Xanh', 4, 0),
(136, 115, 'XL', 'Nâu', 3, 0),
(137, 115, 'XXL', 'Nâu', 4, 0),
(138, 114, 'L', 'Nâu', 3, 0),
(139, 114, 'XL', 'Nâu', 4, 0),
(140, 114, 'L', 'Đỏ', 2, 0),
(141, 114, 'XL', 'Đỏ', 4, 0),
(142, 113, 'S', 'Trắng', 3, 0),
(143, 113, 'M', 'Trắng', 4, 0),
(144, 113, 'S', 'Hồng', 4, 0),
(145, 113, 'M', 'Hồng', 2, 0),
(146, 112, 'S', 'Vàng', 4, 0),
(147, 112, 'M', 'Vàng', 4, 0),
(148, 112, 'L', 'Vàng', 3, 0),
(149, 112, 'M', 'Xanh', 4, 0),
(150, 112, 'S', 'Xanh', 3, 0),
(151, 111, 'M', 'Xanh', 3, 0),
(152, 111, 'L', 'Xanh', 4, 0),
(153, 111, 'S', 'Xanh than', 3, 0),
(154, 111, 'L', 'Xanh than', 2, 0),
(155, 111, 'S', 'Trắng', 5, 0),
(156, 111, 'M', 'Trắng', 4, 0),
(157, 110, 'M', 'Trắng', 4, 0),
(158, 110, 'L', 'Trắng', 4, 0),
(159, 109, 'S', 'Đen', 4, 0),
(160, 109, 'M', 'Đen', 5, 0),
(161, 109, 'L', 'Đen', 3, 0),
(162, 109, 'XL', 'Đen', 2, 0),
(163, 108, 'M', 'Trắng', 4, 0),
(164, 108, 'L', 'Trắng', 4, 0),
(165, 108, 'M', 'Đen', 3, 0),
(166, 108, 'L', 'Đen', 5, 0),
(167, 107, 'S', 'Trắng', 3, 0),
(168, 107, 'M', 'Trắng', 4, 0),
(169, 107, 'M', 'Xanh than', 4, 0),
(170, 107, 'L', 'Xanh than', 3, 0),
(171, 107, 'M', 'Xanh', 3, 0),
(172, 106, 'S', 'Hồng', 4, 0),
(173, 106, 'M', 'Hồng', 5, 0),
(174, 106, 'L', 'Nâu', 6, 0),
(175, 106, 'S', 'Nâu', 4, 0),
(176, 106, 'S', 'Xanh', 3, 0),
(177, 106, 'M', 'Xanh', 1, 0),
(178, 105, 'M', 'Trắng', 2, 0),
(179, 105, 'L', 'Trắng', 4, 0),
(180, 105, 'S', 'Be', 5, 0),
(181, 105, 'M', 'Be', 4, 0),
(182, 105, 'S', 'Xanh', 4, 0),
(183, 104, 'M', 'Xám', 3, 0),
(184, 104, 'L', 'Xám', 4, 0),
(185, 103, 'S', 'Xanh', 3, 0),
(186, 103, 'M', 'Xanh', 2, 0),
(187, 103, 'M', 'Vàng', 4, 0),
(188, 103, 'M', 'Hồng', 3, 0),
(189, 103, 'S', 'Hồng', 4, 0),
(190, 102, 'S', 'Đen', 4, 0),
(191, 102, 'M', 'Đen', 3, 0),
(192, 102, 'M', 'Vàng', 4, 0),
(193, 102, 'L', 'Vàng', 3, 0),
(194, 102, 'S', 'Trắng', 4, 0),
(195, 102, 'L', 'Trắng', 3, 0),
(196, 101, 'S', 'Xanh', 3, 0),
(197, 101, 'M', 'Xanh', 4, 0),
(198, 101, 'S', 'Hồng', 5, 0),
(199, 101, 'M', 'Hồng', 3, 0),
(200, 100, 'M', 'Be', 3, 0),
(201, 100, 'L', 'Be', 4, 0),
(202, 99, 'S', 'Xanh than', 2, 0),
(203, 99, 'M', 'Xanh than', 4, 0),
(204, 99, 'S', 'Đỏ', 5, 0),
(205, 99, 'L', 'Đỏ', 4, 0),
(206, 98, 'M', 'Trắng', 4, 0),
(207, 98, 'L', 'Trắng', 5, 0),
(208, 98, 'S', 'Đen', 3, 0),
(209, 98, 'M', 'Đen', 3, 0),
(210, 97, 'M', 'Nâu', 3, 0),
(211, 97, 'L', 'Nâu', 4, 0),
(212, 96, 'S', 'Xanh', 3, 0),
(213, 96, 'M', 'Xanh', 4, 0),
(214, 96, 'M', 'Đỏ', 5, 0),
(215, 96, 'L', 'Đỏ', 4, 0),
(216, 137, 'XL', 'Đen', 4, 0),
(217, 137, 'L', 'Đen', 4, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `discount_percent` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `min_order_value` decimal(10,2) DEFAULT 0.00,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `usage_limit` int(11) DEFAULT 100,
  `used_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `promotions`
--

INSERT INTO `promotions` (`id`, `code`, `discount_percent`, `discount_amount`, `min_order_value`, `start_date`, `end_date`, `usage_limit`, `used_count`) VALUES
(5, 'SALE30', 30, 0.00, 150000.00, '2026-04-25 22:22:00', '2026-05-15 22:23:00', 3, 3),
(6, 'SALE100K', 0, 100000.00, 899000.00, '2026-04-30 02:12:00', '2026-05-08 01:15:00', 7, 0),
(8, 'LOOPFIT', 0, 10000.00, 200000.00, '2026-05-03 13:29:00', '2026-05-13 13:29:00', 3, 3),
(11, 'SALE150', 0, 15000.00, 35000.00, '2026-05-03 22:01:00', '2026-05-09 22:01:00', 5, 0),
(13, 'OK', 10, 0.00, 199000.00, '2026-05-22 14:51:00', '2026-06-06 21:51:00', 15, 2),
(14, 'SALE', 0, 15000.00, 20000.00, '2026-05-27 14:51:00', '2026-06-06 14:51:00', 10, 5);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_hidden` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`, `is_hidden`) VALUES
(55, 15, 96, 5, 'Bộ đồ này cực kỳ chất, mặc lên trẻ trung năng động, chất vải thoáng mát. Sẽ ủng hộ shop lần sau!', '2026-05-01 10:30:00', 0),
(56, 22, 96, 4, 'Đẹp nhưng hơi rộng so với size, nói chung ổn.', '2026-05-05 14:20:00', 0),
(57, 15, 102, 5, 'Váy xòe xinh lắm, đi chơi rất hợp. 10 điểm!', '2026-05-02 09:15:00', 0),
(58, 22, 102, 4, 'Đẹp nhưng hơi ngắn so với hình, nhìn chung ok.', '2026-05-06 16:45:00', 0),
(59, 15, 105, 5, 'Chất vải tốt, form đẹp, đi làm hay đi chơi đều được.', '2026-05-03 11:00:00', 0),
(60, 15, 105, 3, 'Sản phẩm đẹp nhưng giao hàng hơi chậm.', '2026-05-07 13:30:00', 0),
(61, 15, 108, 5, 'Siêu phẩm! Mặc lên chất vl, phối đồ cực dễ.', '2026-05-04 08:45:00', 0),
(62, 22, 108, 4, 'Rất đẹp nhưng size L hơi chật so với người 70kg.', '2026-05-08 19:00:00', 0),
(63, 15, 109, 5, 'Áo khoác da đẹp quá, chất liệu mềm, form chuẩn.', '2026-05-05 12:10:00', 0),
(64, 22, 143, 5, 'Blazer đẹp sang trọng, mặc đi tiệc rất hợp.', '2026-05-06 17:20:00', 0),
(65, 22, 96, 2, 'Chất vải không như mong đợi, hơi thất vọng.', '2026-05-09 10:00:00', 1),
(66, 22, 102, 1, 'Sản phẩm lỗi, không giống ảnh shop.', '2026-05-10 15:30:00', 1),
(67, 15, 117, 5, 'ao dep', '2026-05-28 14:48:03', 0),
(68, 15, 116, 5, 'Đồ rất đẹp và mới', '2026-05-30 19:03:17', 0),
(69, 15, 98, 5, 'Đẹp lắm, shop tư vấn nhiệt tình', '2026-05-30 19:04:13', 0),
(70, 15, 159, 5, 'Đồ mới hợp thời trang, shop tư vấn nhiệt tình', '2026-05-31 20:28:46', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `stickers`
--

CREATE TABLE `stickers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `stickers`
--

INSERT INTO `stickers` (`id`, `name`, `code`, `image_url`, `is_active`) VALUES
(1, 'Vui', 'happy', '😊', 1),
(2, 'Buồn', 'sad', '😢', 1),
(3, 'Tim', 'heart', '❤️', 1),
(4, 'Cười lớn', 'laugh', '😂', 1),
(5, 'Like', 'like', '👍', 1),
(6, 'Thả tim', 'love', '💕', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `status`, `created_at`) VALUES
(1, 'Admin', 'admin@loopfit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'admin', 'active', '2026-04-03 19:45:28'),
(3, 'Admin', 'admin2@loopfit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'admin', 'active', '2026-04-04 09:31:48'),
(15, 'Hong Nhung', 'hongnhungthi4@gmail.com', '$2y$10$j0dRlT1lTW5RL2BnlGxi5O3UJi7UsPB16vWqYC4Oz78A9y9LGLDR6', '0937281643', 'Linh Xuân, Thủ Đức', 'user', 'active', '2026-04-17 13:57:59'),
(22, 'Thanh Tuyền Trần', 'tranngocviet020818@gmail.com', '$2y$10$nWZO10GJzASnyij/xskYbeVh.DsTyYtgWsbcCN.1Mq6h1Gf8etlrS', '0967951028', 'Phường Linh Xuân, Thủ Đức', 'user', 'active', '2026-05-23 17:41:08'),
(23, 'Nguyễn Thị Thanh', 'thanh.nguyen@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', '123 Lê Lợi, Quận 1, TP.HCM', 'user', 'active', '2026-04-01 08:00:00'),
(24, 'Trần Văn Minh', 'minh.tran@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0978123456', '456 Nguyễn Huệ, Quận 1, TP.HCM', 'user', 'active', '2026-04-05 10:30:00'),
(25, 'Lê Thị Hương', 'huong.le@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0933456789', '789 CMT8, Quận 3, TP.HCM', 'user', 'active', '2026-04-10 14:20:00'),
(26, 'Thanh', 'thanhtuyen30042006@gmail.com', '$2y$10$TURefSkcO9Ps7X.LQ134aOJQ2wBJMFOLwVeAgOV75fFsSOliq0XG6', '0984746520', 'Thủ Đức', 'user', 'active', '2026-05-27 16:51:23');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `bulk_promotions`
--
ALTER TABLE `bulk_promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_scope_type` (`scope_type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `fk_bulk_promotions_category` (`category_id`);

--
-- Chỉ mục cho bảng `bulk_promotion_categories`
--
ALTER TABLE `bulk_promotion_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_promo_category` (`bulk_promotion_id`,`category_id`);

--
-- Chỉ mục cho bảng `bulk_promotion_items`
--
ALTER TABLE `bulk_promotion_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_promotion_product` (`bulk_promotion_id`,`product_id`),
  ADD KEY `idx_bulk_promotion_id` (`bulk_promotion_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_user_type` (`user_type`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_reference` (`reference_id`,`reference_type`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_user_is_read` (`user_id`,`is_read`),
  ADD KEY `idx_created_at_type` (`created_at`,`type`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_details_order` (`order_id`),
  ADD KEY `idx_order_details_product` (`product_id`);

--
-- Chỉ mục cho bảng `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_rented_count` (`rented_count`);

--
-- Chỉ mục cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_size_color` (`product_id`,`size`,`color`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `stickers`
--
ALTER TABLE `stickers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `bulk_promotions`
--
ALTER TABLE `bulk_promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `bulk_promotion_categories`
--
ALTER TABLE `bulk_promotion_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT cho bảng `bulk_promotion_items`
--
ALTER TABLE `bulk_promotion_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT cho bảng `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT cho bảng `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT cho bảng `stickers`
--
ALTER TABLE `stickers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `bulk_promotions`
--
ALTER TABLE `bulk_promotions`
  ADD CONSTRAINT `fk_bulk_promotions_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `bulk_promotion_items`
--
ALTER TABLE `bulk_promotion_items`
  ADD CONSTRAINT `fk_bulk_promo_items_bulk_promotion` FOREIGN KEY (`bulk_promotion_id`) REFERENCES `bulk_promotions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bulk_promo_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
