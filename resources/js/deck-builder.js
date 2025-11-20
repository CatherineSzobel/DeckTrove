document.addEventListener("DOMContentLoaded", async () => {

    const app = document.getElementById("deckApp");
    const game = app.dataset.game;

    const searchInput = document.getElementById("searchInput");
    const cardResults = document.getElementById("cardResults");

    const mainDeck = document.getElementById("mainDeck");
    const extraDeck = document.getElementById("extraDeck");
    const sideDeck = document.getElementById("sideDeck");

    const mainCount = document.getElementById("mainCount");
    const extraCount = document.getElementById("extraCount");
    const sideCount = document.getElementById("sideCount");

    let allCards = [];
    let deckCards = [];

    // Load card DB based on game
    if (game === "yugioh") {
        allCards = await loadYuGiOhCards();
    } else if (game === "magic") {
        allCards = await loadMTGCards();
    }

    // SEARCH
    searchInput.addEventListener("input", () => {
        const q = searchInput.value.toLowerCase();
        displaySearchResults(allCards.filter(c => c.name.toLowerCase().includes(q)));
    });

    function displaySearchResults(list) {
        cardResults.innerHTML = "";

        list.forEach(card => {
            const div = document.createElement("div");
            div.className = "cursor-pointer";
            div.innerHTML = `
                <img src="${card.image}" class="rounded shadow cardImage">
                <p class="text-sm">${card.name}</p>
            `;
            div.onclick = () => addCard(card);
            cardResults.appendChild(div);
        });
    }

    function addCard(card) {
        let zone = "main";

        if (game === "yugioh") {
            if (isExtraDeckCard(card)) zone = "extra";
        }

        pushToDeck(card, zone);
        refreshDeckUI();
    }

    function pushToDeck(card, zone) {
        let entry = deckCards.find(c => c.id === card.id && c.zone === zone);

        if (entry) {
            entry.count++;
        } else {
            deckCards.push({ id: card.id, card, zone, count: 1 });
        }
    }

    function refreshDeckUI() {
        // Reset
        mainDeck.innerHTML = "";
        if (extraDeck) extraDeck.innerHTML = "";
        if (sideDeck) sideDeck.innerHTML = "";

        let main = 0, extra = 0, side = 0;

        deckCards.forEach(entry => {
            const box = document.createElement("div");
            box.innerHTML = `
                <img src="${entry.card.image}" class="rounded shadow">
                <p class="text-xs text-center">x${entry.count}</p>
            `;
            box.className = "relative";

            box.onclick = () => removeCard(entry);

            if (entry.zone === "main") {
                mainDeck.appendChild(box);
                main += entry.count;
            }
            if (entry.zone === "extra") {
                extraDeck.appendChild(box);
                extra += entry.count;
            }
            if (entry.zone === "side") {
                sideDeck.appendChild(box);
                side += entry.count;
            }
        });

        mainCount.innerText = main;
        if (extraCount) extraCount.innerText = extra;
        if (sideCount) sideCount.innerText = side;

        document.getElementById("cardData").value = JSON.stringify(deckCards);
    }

    function removeCard(entry) {
        entry.count--;
        if (entry.count <= 0) {
            deckCards = deckCards.filter(c => c !== entry);
        }
        refreshDeckUI();
    }
});

// Helper for Yu-Gi-Oh! extra deck rules
function isExtraDeckCard(card) {
    const extraTypes = [
        "Fusion Monster",
        "Synchro Monster",
        "XYZ Monster",
        "Link Monster"
    ];
    return extraTypes.includes(card.type);
}

async function loadYuGiOhCards() {
    const res = await fetch("https://db.ygoprodeck.com/api/v7/cardinfo.php");
    const json = await res.json();

    return json.data.map(c => ({
        id: c.id,
        name: c.name,
        type: c.type,
        image: c.card_images[0].image_url
    }));
}

async function loadMTGCards() {
    const res = await fetch("https://api.scryfall.com/cards/search?q=legal:commander");
    const json = await res.json();

    return json.data.map(c => ({
        id: c.id,
        name: c.name,
        type: c.type_line,
        image: c.image_uris?.normal || ""
    }));
}
