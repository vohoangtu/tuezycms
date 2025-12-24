<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - TuzyCMS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .checkout-summary {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .summary-total {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
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
        .promotion-code {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .promotion-code input {
            flex: 1;
        }
        .btn-apply {
            padding: 12px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
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
        <div class="checkout-container">
            <div class="checkout-form">
                <h1>Thông tin thanh toán</h1>
                
                <form id="checkoutForm">
                    <h2>Thông tin giao hàng</h2>
                    <div class="form-group">
                        <label>Họ và tên *</label>
                        <input type="text" name="shipping_full_name" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="shipping_email" required>
                        </div>
                        <div class="form-group">
                            <label>Số điện thoại *</label>
                            <input type="tel" name="shipping_phone" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ *</label>
                        <input type="text" name="shipping_address" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phường/Xã *</label>
                            <input type="text" name="shipping_ward" required>
                        </div>
                        <div class="form-group">
                            <label>Quận/Huyện *</label>
                            <input type="text" name="shipping_district" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tỉnh/Thành phố *</label>
                            <input type="text" name="shipping_province" required>
                        </div>
                        <div class="form-group">
                            <label>Mã bưu điện</label>
                            <input type="text" name="shipping_postal_code">
                        </div>
                    </div>

                    <h2 style="margin-top: 30px;">Thông tin thanh toán</h2>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="sameAsShipping" checked>
                            Giống thông tin giao hàng
                        </label>
                    </div>
                    <div id="billingInfo" style="display: none;">
                        <div class="form-group">
                            <label>Họ và tên *</label>
                            <input type="text" name="billing_full_name">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="billing_email">
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại *</label>
                                <input type="tel" name="billing_phone">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Địa chỉ *</label>
                            <input type="text" name="billing_address">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phường/Xã *</label>
                                <input type="text" name="billing_ward">
                            </div>
                            <div class="form-group">
                                <label>Quận/Huyện *</label>
                                <input type="text" name="billing_district">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tỉnh/Thành phố *</label>
                                <input type="text" name="billing_province">
                            </div>
                            <div class="form-group">
                                <label>Mã bưu điện</label>
                                <input type="text" name="billing_postal_code">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="notes" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Phương thức thanh toán *</label>
                        <select name="payment_method" required>
                            <option value="vnpay">VNPay</option>
                            <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="checkout-summary">
                <h2>Đơn hàng của bạn</h2>
                <div id="orderSummary">
                    <p>Đang tải...</p>
                </div>
                
                <div class="promotion-code">
                    <input type="text" id="promotionCode" placeholder="Mã khuyến mãi">
                    <button class="btn-apply" onclick="applyPromotion()">Áp dụng</button>
                </div>

                <div class="summary-item">
                    <span>Tạm tính:</span>
                    <span id="subtotal">0 ₫</span>
                </div>
                <div class="summary-item">
                    <span>Phí vận chuyển:</span>
                    <span id="shippingFee">0 ₫</span>
                </div>
                <div class="summary-item">
                    <span>Giảm giá:</span>
                    <span id="discount">0 ₫</span>
                </div>
                <div class="summary-item summary-total">
                    <span>Tổng cộng:</span>
                    <span id="total">0 ₫</span>
                </div>

                <button class="btn-checkout" onclick="submitOrder()">Đặt hàng</button>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 TuzyCMS. All rights reserved.</p>
        </div>
    </footer>

    <script>
        let cartData = null;
        let appliedPromotion = null;

        // Load cart and calculate summary
        async function loadCartSummary() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            if (cart.length === 0) {
                window.location.href = '/cart';
                return;
            }

            try {
                const response = await fetch('/admin/api/cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({items: cart})
                });

                cartData = await response.json();
                
                if (!cartData.valid) {
                    alert('Có lỗi xảy ra: ' + cartData.errors.join(', '));
                    return;
                }

                displayOrderSummary(cartData);
            } catch (error) {
                console.error('Error loading cart:', error);
                alert('Không thể tải thông tin giỏ hàng');
            }
        }

        function displayOrderSummary(data) {
            const summaryHtml = data.items.map(item => `
                <div class="summary-item">
                    <div>
                        <strong>${item.productName}</strong><br>
                        <small>${item.quantity} x ${formatPrice(item.unitPrice)}</small>
                    </div>
                    <span>${formatPrice(item.totalPrice)}</span>
                </div>
            `).join('');

            document.getElementById('orderSummary').innerHTML = summaryHtml;
            document.getElementById('subtotal').textContent = formatPrice(data.totals.subtotal);
            document.getElementById('shippingFee').textContent = formatPrice(data.totals.shippingFee);
            document.getElementById('discount').textContent = formatPrice(data.totals.discount);
            document.getElementById('total').textContent = formatPrice(data.totals.total);
        }

        async function applyPromotion() {
            const code = document.getElementById('promotionCode').value;
            if (!code) {
                alert('Vui lòng nhập mã khuyến mãi');
                return;
            }

            try {
                const response = await fetch(`/admin/api/promotions.php?code=${code}`);
                const promotion = await response.json();
                
                if (promotion.error) {
                    alert('Mã khuyến mãi không hợp lệ');
                    return;
                }

                appliedPromotion = promotion;
                // Recalculate totals with promotion
                // This would need to be done on the server side
                alert('Áp dụng mã khuyến mãi thành công!');
            } catch (error) {
                console.error('Error applying promotion:', error);
                alert('Không thể áp dụng mã khuyến mãi');
            }
        }

        async function submitOrder() {
            const form = document.getElementById('checkoutForm');
            const formData = new FormData(form);
            
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const shippingAddress = {
                fullName: formData.get('shipping_full_name'),
                phone: formData.get('shipping_phone'),
                email: formData.get('shipping_email'),
                address: formData.get('shipping_address'),
                ward: formData.get('shipping_ward'),
                district: formData.get('shipping_district'),
                province: formData.get('shipping_province'),
                postalCode: formData.get('shipping_postal_code'),
            };

            let billingAddress = shippingAddress;
            if (!document.getElementById('sameAsShipping').checked) {
                billingAddress = {
                    fullName: formData.get('billing_full_name'),
                    phone: formData.get('billing_phone'),
                    email: formData.get('billing_email'),
                    address: formData.get('billing_address'),
                    ward: formData.get('billing_ward'),
                    district: formData.get('billing_district'),
                    province: formData.get('billing_province'),
                    postalCode: formData.get('billing_postal_code'),
                };
            }

            try {
                const response = await fetch('/admin/api/orders.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        customer_id: 1, // TODO: Get from session
                        cart_items: cart,
                        payment_method: formData.get('payment_method'),
                        shipping_address: shippingAddress,
                        billing_address: billingAddress,
                        promotion_code: appliedPromotion?.code || null,
                        notes: formData.get('notes'),
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    // Clear cart
                    localStorage.removeItem('cart');
                    
                    // Redirect to payment or order confirmation
                    if (formData.get('payment_method') === 'vnpay') {
                        // Redirect to payment gateway
                        window.location.href = `/payment/vnpay?order_id=${result.id}`;
                    } else {
                        window.location.href = `/order/confirm?order_number=${result.order_number}`;
                    }
                } else {
                    alert('Có lỗi xảy ra: ' + (result.error || 'Không thể tạo đơn hàng'));
                }
            } catch (error) {
                console.error('Error submitting order:', error);
                alert('Không thể đặt hàng. Vui lòng thử lại.');
            }
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(price);
        }

        // Toggle billing info
        document.getElementById('sameAsShipping').addEventListener('change', function() {
            document.getElementById('billingInfo').style.display = this.checked ? 'none' : 'block';
        });

        // Load on page load
        loadCartSummary();
    </script>
</body>
</html>

