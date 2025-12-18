document.addEventListener("DOMContentLoaded", () => {
    const marquees = document.querySelectorAll(".marquee");

    marquees.forEach((marquee) => {
        let scrollAmount = 0;
        const speed = 1; // pixels per frame, adjust as needed
        const containerWidth = marquee.scrollWidth;

        // Duplicate content for infinite scroll
        marquee.innerHTML += marquee.innerHTML;

        function animate() {
            scrollAmount += speed;
            if (scrollAmount >= containerWidth) scrollAmount = 0;
            marquee.style.transform = `translateX(-${scrollAmount}px)`;
            requestAnimationFrame(animate);
        }

        animate();
    });
});
