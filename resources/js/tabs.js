const active = ["active", "text-blue-600"];
const inactive = ["inactive", "text-gray-500"];

export function initTabs(container) {
    const tabs = container.querySelectorAll(".tab-link");
    const panels = container.querySelectorAll(".tab-panel");

    if (!tabs.length || !panels.length) return;

    panels.forEach(panel => panel.classList.add("hidden"));

    activateFirstTab(tabs,panels)
    
    tabs.forEach(tab => {
        tab.addEventListener("click", (e) => {
            e.preventDefault();

            deactivateTabs(tabs);
            hideAllPanels(panels);

            activateClickedTab(tab);
            showTargetPanel(tab, container);
        });
    });
}
function activateFirstTab(tabs,panels) {
    tabs[0].classList.add(...active);
    tabs[0].classList.remove(...inactive);
    tabs[0].setAttribute("aria-selected", "true");
    panels[0].classList.remove("hidden");
}
function deactivateTabs(tabs) {
    tabs.forEach(tab => {
        tab.classList.remove(...active);
        tab.classList.add(...inactive);
        tab.setAttribute("aria-selected", "false");
    });
}

function hideAllPanels(panels) {
    panels.forEach(panel => panel.classList.add("hidden"));
}

function activateClickedTab(tab) {
    tab.classList.remove(...inactive);
    tab.classList.add(...active);
    tab.setAttribute("aria-selected", "true");
}

function showTargetPanel(tab, container) {
    const target = container.querySelector(`#${tab.dataset.tabTarget}`);
    if (target) target.classList.remove("hidden");
}

export function initAllTabs() {
    document.querySelectorAll(".tab-container").forEach(initTabs);
}
