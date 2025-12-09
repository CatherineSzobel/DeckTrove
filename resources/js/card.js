import { initTabs } from "./tabs.js"; // Note the relative path "./"

document.addEventListener("DOMContentLoaded", () => {
    // Tabs
    document.querySelectorAll(".tab-container").forEach(initTabs);
});
