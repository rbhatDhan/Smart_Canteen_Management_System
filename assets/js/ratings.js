/* Star ratings on the order-history page. Posts to api/ratings_submit.php. */
(function () {
    'use strict';

    var cfg = window.CANTEEN_RATE || {};

    document.querySelectorAll('.rating-stars').forEach(function (group) {
        var stars = Array.prototype.slice.call(group.querySelectorAll('.rating-star'));
        var current = parseInt(group.getAttribute('data-rating'), 10) || 0;
        var orderKey = parseInt(group.getAttribute('data-order'), 10);

        function paint(n) {
            stars.forEach(function (star, i) {
                star.classList.toggle('filled', i < n);
                star.setAttribute('aria-checked', i + 1 === current ? 'true' : 'false');
            });
        }

        stars.forEach(function (star) {
            var value = parseInt(star.getAttribute('data-value'), 10);
            star.addEventListener('mouseenter', function () { paint(value); });
            star.addEventListener('focus', function () { paint(value); });
            star.addEventListener('mouseleave', function () { paint(current); });
            star.addEventListener('blur', function () { paint(current); });

            star.addEventListener('click', function () {
                fetch(cfg.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': cfg.csrf },
                    body: JSON.stringify({ order: orderKey, stars: value })
                })
                .then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (data) {
                        return { ok: res.ok, data: data };
                    });
                })
                .then(function (r) {
                    if (!r.ok || !r.data.success) {
                        throw new Error(r.data.message || 'Could not save your rating.');
                    }
                    current = value;
                    group.setAttribute('data-rating', String(value));
                    paint(current);
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success',
                        title: 'Thanks for rating!', timer: 1500, showConfirmButton: false
                    });
                })
                .catch(function (err) {
                    paint(current);
                    Swal.fire({ icon: 'error', title: 'Rating not saved', text: err.message, confirmButtonColor: '#0d9488' });
                });
            });
        });
    });
})();
