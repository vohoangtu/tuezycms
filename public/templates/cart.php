<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - TuzyCMS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="container">
                <div class="logo">TuzyCMS</div>
                <ul class="menu">
                    <li><a href="/">Trang chủ</a></li>
                    <li><a href="/products">Sản phẩm</a></li>
                    <li><a href="/articles">Bài viết</a></li>
                    <li><a href="/cart">Giỏ hàng</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container" style="max-width: 1200px; margin: 40px auto; padding: 20px;">
            <h1>Giỏ hàng</h1>
            <div id="cartContent">
                <p>Đang tải...</p>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 TuzyCMS. All rights reserved.</p>
        </div>
    </footer>

    <style>
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .cart-table th, .cart-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .cart-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-control button {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        .quantity-control input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 5px;
        }
        .cart-summary {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-total {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #ddd;
        }
        .btn-checkout {
            width: 100%;
            padding: 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-checkout:hover {
            background: #c0392b;
        }
        .btn-remove {
            color: #e74c3c;
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
    <script>
        let cartItems = [];
        let cartSummary = null;

        async function loadCart() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const cartContent = document.getElementById('cartContent');
            
            if (cart.length === 0) {
                cartContent.innerHTML = '<p>Giỏ hàng trống. <a href="/products">Tiếp tục mua sắm</a></p>';
                return;
            }

            try {
                // Get cart summary from API
                const response = await fetch('/admin/api/cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({items: cart})
                });

                cartSummary = await response.json();
                
                if (!cartSummary.valid) {
                    cartContent.innerHTML = '<p>Có lỗi xảy ra: ' + cartSummary.errors.join(', ') + '</p>';
                    return;
                }

                displayCart(cartSummary);
            } catch (error) {
                console.error('Error loading cart:', error);
                cartContent.innerHTML = '<p>Không thể tải giỏ hàng</p>';
            }
        }

        function displayCart(summary) {
            const cartContent = document.getElementById('cartContent');
            
            const tableHtml = `
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        ${summary.items.map(item => `
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div>
                                            <strong>${item.productName}</strong><br>
                                            <small>SKU: ${item.productSku}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${formatPrice(item.unitPrice)}</td>
                                <td>
                                    <div class="quantity-control">
                                        <button onclick="updateQuantity(${item.productId}, -1)">-</button>
                                        <input type="number" value="${item.quantity}" min="1" 
                                               onchange="updateQuantity(${item.productId}, 0, this.value)">
                                        <button onclick="updateQuantity(${item.productId}, 1)">+</button>
                                    </div>
                                </td>
                                <td>${formatPrice(item.totalPrice)}</td>
                                <td>
                                    <button class="btn-remove" onclick="removeItem(${item.productId})">Xóa</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span>${formatPrice(summary.totals.subtotal)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>${formatPrice(summary.totals.shippingFee)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Giảm giá:</span>
                        <span>${formatPrice(summary.totals.discount)}</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Tổng cộng:</span>
                        <span>${formatPrice(summary.totals.total)}</span>
                    </div>
                    <button class="btn-checkout" onclick="window.location.href='/checkout'">
                        Thanh toán
                    </button>
                </div>
            `;

            cartContent.innerHTML = tableHtml;
        }

        function updateQuantity(productId, delta, newValue = null) {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const item = cart.find(i => i.productId === productId);
            
            if (item) {
                if (newValue !== null) {
                    item.quantity = parseInt(newValue) || 1;
                } else {
                    item.quantity = Math.max(1, item.quantity + delta);
                }
                
                if (item.quantity <= 0) {
                    removeItem(productId);
                    return;
                }
            } else if (delta > 0) {
                cart.push({productId: productId, quantity: 1});
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }

        function removeItem(productId) {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const filtered = cart.filter(i => i.productId !== productId);
            localStorage.setItem('cart', JSON.stringify(filtered));
            loadCart();
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(price);
        }

        // Load cart on page load
        loadCart();
    </script>
</body>
</html>

