function initPrintListToggles() {
    document.querySelectorAll("[data-toggle-target]").forEach((button) => {
        button.addEventListener("click", () => {
            const list = document.getElementById(button.dataset.toggleTarget);
            if (!list) return;

            const hidden = list.classList.toggle("hidden");
            button.textContent = hidden ? "Show more" : "Show less";
            button.setAttribute("aria-expanded", String(!hidden));
        });
    });
}

/** Flips between the faces of a double-faced Magic card. */
function initTransformButton() {
    const button = document.getElementById("transformButton");
    const image = document.getElementById("cardImage");
    if (!button || !image?.dataset.faces) return;

    const faces = JSON.parse(image.dataset.faces);
    let index = 0;

    button.addEventListener("click", () => {
        index = (index + 1) % faces.length;
        image.src = faces[index];
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initPrintListToggles();
    initTransformButton();
});
