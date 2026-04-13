document.addEventListener("DOMContentLoaded", function () {

    const gender = document.getElementById("filter-gender");
    const price  = document.getElementById("filter-price");

    function fetchProducts() {
        fetch(`/LoopFit/public/api/products?gender=${gender.value}&price=${price.value}`)
            .then(res => res.json())
            .then(data => renderProducts(data));
    }

    gender.addEventListener("change", fetchProducts);
    price.addEventListener("change", fetchProducts);

});

function renderProducts(products) {
    const container = document.querySelector(".product-list");
    container.innerHTML = "";

    products.forEach(p => {
        container.innerHTML += `
            <div class="product-card">
                <a href="/LoopFit/public/product/${p.id}">
                    <img src="/LoopFit/public/uploads/products/${p.image}">
                    <h3>${p.name}</h3>
                    <p>${Number(p.price).toLocaleString()}đ</p>
                </a>
            </div>
        `;
    });
}