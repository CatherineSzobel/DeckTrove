document.addEventListener('DOMContentLoaded', function () {
    // Get elements
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');

    // Only proceed if both exist
    if (!avatarInput || !avatarPreview) return;

    // When user selects a file, update the preview
    avatarInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            avatarPreview.src = url;

            // Optional: revoke object URL after image loads to free memory
            avatarPreview.onload = () => URL.revokeObjectURL(url);
        }
    });
});
