-- Tắt kiểm tra khóa ngoại
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa dữ liệu cũ
TRUNCATE TABLE `products`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `cart`;
TRUNCATE TABLE `order_details`;

-- Bật lại kiểm tra khóa ngoại
SET FOREIGN_KEY_CHECKS = 1;

-- Danh mục sản phẩm
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Áo', 'Áo thun, áo sơ mi, áo khoác...'),
(2, 'Quần', 'Quần jeans, quần âu, quần short...'),
(3, 'Váy', 'Váy liền, đầm dự tiệc, váy ngắn...');

-- Sản phẩm mẫu
INSERT INTO `products` (`name`, `price`, `description`, `image`, `stock`, `category_id`, `created_at`) VALUES
('Áo thun basic', 100000, 'Áo thun cotton thoáng mát, phù hợp mặc hàng ngày', 'ao_thun_basic.jpg', 10, 1, NOW()),
('Quần jeans rách', 200000, 'Quần jeans phong cách đường phố, rách gối nhẹ', 'quan_jeans_rach.jpg', 5, 2, NOW()),
('Váy trắng công sở', 250000, 'Váy trắng thanh lịch, phù hợp đi làm hoặc dự tiệc', 'vay_trang_cong_so.jpg', 7, 3, NOW());