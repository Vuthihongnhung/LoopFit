function toggleMenu(element) {
    // 1. Tìm submenu (thẻ ul) ngay sau thẻ li vừa click
    const submenu = element.nextElementSibling;

    if (submenu && submenu.classList.contains('submenu')) {
        // Kiểm tra trạng thái hiển thị thực tế
        const isHidden = window.getComputedStyle(submenu).display === "none";

        // 2. ĐÓNG TẤT CẢ các menu khác trước khi mở cái mới (Hiệu ứng Accordion)
        document.querySelectorAll('.submenu').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.sidebar-menu li').forEach(li => {
            li.classList.remove('active'); // Gỡ bỏ xoay mũi tên của các mục khác
        });

        // 3. Xử lý mục vừa được click
        if (isHidden) {
            submenu.style.display = "block";
            element.classList.add('active'); // THÊM CLASS ĐỂ XOAY MŨI TÊN
        } else {
            submenu.style.display = "none";
            element.classList.remove('active'); // GỠ CLASS ĐỂ XOAY VỀ CŨ
        }
    }
}

// Giữ nguyên hàm open/close cũ
function openMenu() {
    document.getElementById("sidebar").classList.add("active");
    document.getElementById("overlay").classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeMenu() {
    document.getElementById("sidebar").classList.remove("active");
    document.getElementById("overlay").classList.remove("active");
    document.body.style.overflow = "auto";
}

//*Tăng giảm số lượng trong product_detail

function changeImg(el) {
    document.getElementById("mainImg").src = el.src;
}

function increase() {
    let qty = document.getElementById("qty");
    qty.value = parseInt(qty.value) + 1;
}

function decrease() {
    let qty = document.getElementById("qty");
    if (qty.value > 1) {
        qty.value = parseInt(qty.value) - 1;
    }
}

 //*thêm vào giỏ hàng
function addToCart() {
    // lấy size
    const size = document.querySelector(".size-btn.active")?.innerText;

    // lấy ngày
    const rentDate = document.getElementById("rentDate").value;
    const returnDate = document.getElementById("returnDate").value;

    // số lượng
    const qty = document.getElementById("qty").value;

    // fake product id (sau này lấy từ backend)
    const productId = 1;

    // validate
    if (!size) {
        alert("Vui lòng chọn size!");
        return;
    }

    if (!rentDate || !returnDate) {
        alert("Vui lòng chọn ngày thuê!");
        return;
    }

    // tạo object
    const item = {
        product_id: productId,
        size: size,
        rent_date: rentDate,
        return_date: returnDate,
        quantity: qty
    };

    console.log("Đã thêm:", item);

    // lưu localStorage (giả lập giỏ hàng)
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart.push(item);
    localStorage.setItem("cart", JSON.stringify(cart));

    alert("Đã thêm vào giỏ!");
}
