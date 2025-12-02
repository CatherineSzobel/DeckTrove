document.querySelectorAll(".carousel-container").forEach((container) => {
    const slides = container.querySelector(".carousel-slides");
    const dots = container.querySelectorAll(".carousel-dots button");
    const prevBtn = container.querySelector(".carousel-prev");
    const nextBtn = container.querySelector(".carousel-next");
    const totalSlides = slides.children.length;
    let index = 0;

    function updateCarousel() {
        slides.style.transform = `translateX(-${index * 100}%)`;
        dots.forEach((dot, i) => {
            dot.classList.toggle("bg-blue-500", i === index);
            dot.classList.toggle("bg-gray-400", i !== index);
        });
    }

    prevBtn.addEventListener("click", () => {
        index = (index - 1 + totalSlides) % totalSlides;
        updateCarousel();
    });

    nextBtn.addEventListener("click", () => {
        index = (index + 1) % totalSlides;
        updateCarousel();
    });

    dots.forEach((dot, i) => {
        dot.addEventListener("click", () => {
            index = i;
            updateCarousel();
        });
    });

    // Optional: auto-slide every 5 seconds
    setInterval(() => {
        index = (index + 1) % totalSlides;
        updateCarousel();
    }, 5000);

    updateCarousel();
});
