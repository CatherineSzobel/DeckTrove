document.addEventListener("DOMContentLoaded", () => {
    const returnButton = document.querySelector(".return_button");
    if (returnButton) {
        returnButton.addEventListener("click", (e) => {
            e.preventDefault();
            const selectedSeries =
                localStorage.getItem("selectedSeries") || "yugioh";
            window.location.href = `/${selectedSeries}/cards`;
        });
    }
});
