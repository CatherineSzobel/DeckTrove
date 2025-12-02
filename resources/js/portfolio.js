document.addEventListener("DOMContentLoaded", function () {
    // Open modal
    document.querySelectorAll("[data-modal-target]").forEach((button) => {
        button.addEventListener("click", () => {
            console.log('Modal open button clicked');
            const modalId = button.getAttribute("data-modal-target");
            const modal = document.getElementById(modalId);
            modal.classList.remove("hidden");
            modal.classList.add("flex"); // Tailwind flex for centering
        });
    });

    // Close modal
    document.querySelectorAll("[data-close-modal]").forEach((el) => {
        el.addEventListener("click", () => {
            const modal = el.closest("[id]");
            modal.classList.add("hidden");
            modal.classList.remove("flex");
        });
    });

    // Close modal on ESC key
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            document.querySelectorAll("[id]").forEach((modal) => {
                if (!modal.classList.contains("hidden")) {
                    modal.classList.add("hidden");
                    modal.classList.remove("flex");
                }
            });
        }
    });
});
