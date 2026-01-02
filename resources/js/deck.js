
function toggleSection(zone) {
    const grid = document.getElementById(`${zone}-cards`);
    const btn = document.getElementById(`${zone}-toggle-btn`);
    if (!grid || !btn) return;

    const isHidden = grid.style.display === "none";
    grid.style.display = isHidden ? "grid" : "none";
    btn.textContent = isHidden ? "−" : "+";
}

document.querySelectorAll('.group').forEach((el) => {
    const img = el.querySelector('img'); 
    const panel = el.querySelector('div.absolute'); 

    if (!img || !panel) return;

    img.addEventListener('mouseenter', () => {
        panel.classList.remove('hidden');
    });

    img.addEventListener('mouseleave', () => {
        panel.classList.add('hidden');
    });

     document.querySelectorAll("[data-zone]").forEach((header) => {
        header.addEventListener("click", () => {
            const zone = header.dataset.zone;
            toggleSection(zone);
        });
    });
});