//Carousel effect

const slides = document.getElementById("carousel-slides");
const dots = document.querySelectorAll("#carousel-dots button");
const totalSlides = slides.children.length;
let index = 0;

function updateCarousel() {
    slides.style.transform = `translateX(-${index * 100}%)`;
    dots.forEach((dot, i) => {
        dot.classList.toggle("bg-blue-500", i === index);
        dot.classList.toggle("bg-gray-400", i !== index);
    });
}

document.getElementById("prev").addEventListener("click", () => {
    index = (index - 1 + totalSlides) % totalSlides;
    updateCarousel();
});

document.getElementById("next").addEventListener("click", () => {
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
