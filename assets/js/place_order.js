/* Place-order page: quantity controls + Razorpay Checkout (test or live keys, set in .env). */
(function () {
    'use strict';

    var cfg = window.CANTEEN_PAY || {};
    var totalAmount = 0;
    var busy = false;

    function qtyInput(id) { return document.getElementById('qty-' + id); }

    window.incrementQty = function (dishId) {
        var input = qtyInput(dishId);
        var value = parseInt(input.value) || 0;
        if (value < 10) {
            input.value = value + 1;
            updateTotal();
        }
    };

    window.decrementQty = function (dishId) {
        var input = qtyInput(dishId);
        var value = parseInt(input.value) || 0;
        if (value > 0) {
            input.value = value - 1;
            updateTotal();
        }
    };

    function updateTotal() {
        var total = 0;
        document.querySelectorAll('.qty-input').forEach(function (input) {
            var price = parseFloat(input.getAttribute('data-price'));
            var qty = parseInt(input.value) || 0;
            total += price * qty;
        });
        totalAmount = total;
        document.getElementById('totalPrice').innerHTML =
            '₹' + (Number.isInteger(total) ? total.toFixed(0) : total.toFixed(2));
    }
    window.updateTotal = updateTotal;

    window.resetOrder = function () {
        document.querySelectorAll('.qty-input').forEach(function (input) { input.value = 0; });
        updateTotal();
        Swal.fire({
            icon: 'info',
            title: 'Order Reset',
            text: 'Your order has been reset.',
            timer: 2000,
            showConfirmButton: false
        });
    };

    function setBusy(flag) {
        busy = flag;
        var btn = document.getElementById('payBtn');
        if (btn) { btn.disabled = flag; btn.style.opacity = flag ? '0.7' : ''; }
    }

    function postJson(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': cfg.csrf },
            body: JSON.stringify(payload)
        }).then(function (res) {
            return res.json().catch(function () { return { success: false }; }).then(function (data) {
                return { ok: res.ok, data: data };
            });
        });
    }

    function showError(title, text) {
        Swal.fire({ icon: 'error', title: title, text: text, confirmButtonColor: '#0d9488' });
    }

    function verifyPayment(response) {
        Swal.fire({
            title: 'Confirming payment...',
            text: 'Please do not close this page.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: function () { Swal.showLoading(); }
        });
        postJson(cfg.verifyUrl, {
            razorpay_order_id: response.razorpay_order_id,
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_signature: response.razorpay_signature
        }).then(function (r) {
            if (r.ok && r.data.success) {
                window.location.href = 'place_order.php?paid=1';
                return;
            }
            setBusy(false);
            showError('Payment not confirmed', r.data.message || 'We could not confirm your payment.');
        }).catch(function () {
            setBusy(false);
            showError('Network problem', 'We could not confirm your payment. If money was debited, your order will still be created automatically.');
        });
    }

    window.payNow = function () {
        if (busy) { return; }

        if (totalAmount <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one dish!',
                confirmButtonColor: '#0d9488'
            });
            return;
        }
        if (!cfg.keyReady) {
            showError('Payments unavailable', 'Online payments are not configured yet. Please contact the canteen.');
            return;
        }

        var items = {};
        document.querySelectorAll('.qty-input').forEach(function (input) {
            var qty = parseInt(input.value) || 0;
            if (qty > 0) { items[input.id.replace('qty-', '')] = qty; }
        });

        setBusy(true);
        postJson(cfg.createUrl, { items: items }).then(function (r) {
            if (!r.ok || !r.data.success) {
                setBusy(false);
                showError('Could not start payment', r.data.message || 'Please try again.');
                return;
            }
            var order = r.data;
            var rzp = new Razorpay({
                key: order.key,
                amount: order.amount,
                currency: order.currency,
                name: order.name,
                description: order.description,
                order_id: order.order_id,
                prefill: order.prefill,
                theme: { color: '#0d9488' },
                handler: verifyPayment,
                modal: { ondismiss: function () { setBusy(false); } }
            });
            // Razorpay's own dialog shows the failure and lets the student retry; we just record it.
            rzp.on('payment.failed', function (resp) {
                var err = (resp && resp.error) || {};
                postJson(cfg.failedUrl, {
                    order_id: order.order_id,
                    payment_id: err.metadata && err.metadata.payment_id,
                    reason: err.description || 'Payment failed'
                });
            });
            rzp.open();
        }).catch(function () {
            setBusy(false);
            showError('Network problem', 'Could not reach the server. Please try again.');
        });
    };
})();
