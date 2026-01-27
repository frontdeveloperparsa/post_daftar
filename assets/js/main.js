// جلوگیری از double submit در همه فرم‌ها
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // اگر قبلاً submit شده، جلوگیری کن
            if (form.dataset.submitted === 'true') {
                e.preventDefault();
                return;
            }
            form.dataset.submitted = 'true';

            // دکمه‌ها رو غیرفعال کن
            const buttons = form.querySelectorAll('button[type="submit"]');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = btn.innerHTML + ' <span class="spinner-border spinner-border-sm" role="status"></span>';
            });
        });
    });
});