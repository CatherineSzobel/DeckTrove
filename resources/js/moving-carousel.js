/** Endless horizontal scroll for the dashboard's random cards. */
document.addEventListener("DOMContentLoaded", () => {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    document.querySelectorAll(".marquee").forEach((marquee) => {
        const width = marquee.scrollWidth;
        let offset = 0;
        let paused = false;

        // Duplicate the cards so the loop is seamless.
        marquee.innerHTML += marquee.innerHTML;
        marquee.addEventListener("mouseenter", () => (paused = true));
        marquee.addEventListener("mouseleave", () => (paused = false));

        function animate() {
            if (!paused) {
                offset = (offset + 1) % width;
                marquee.style.transform = `translateX(-${offset}px)`;
            }
            requestAnimationFrame(animate);
        }

        animate();
    });
});
