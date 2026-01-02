document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');

    if (!avatarInput || !avatarPreview) return;

    avatarInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            avatarPreview.src = url;

            avatarPreview.onload = () => URL.revokeObjectURL(url);
        }
    });
});
