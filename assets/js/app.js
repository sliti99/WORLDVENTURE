document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const destinationsGrid = document.querySelector('.destinations-grid');
    const loadMoreBtn = document.getElementById('load-more');
    const continentFilter = document.getElementById('continent-filter');
    const resetFilterBtn = document.getElementById('reset-filter');
    const counterDisplay = document.querySelector('.hero-header p');

    // Données complètes des destinations
    const destinationsData = [
        { name: "France", image: "france.jpg", continent: "europe", link: "destinations/france.html" },
        { name: "Japon", image: "japon.jpg", continent: "asia", link: "destinations/japon.html" },
        { name: "Italie", image: "italie.jpg", continent: "europe", link: "destinations/italie.html" },
        { name: "Canada", image: "canada.jpg", continent: "america", link: "destinations/canada.html" },
        { name: "Espagne", image: "espagne.jpg", continent: "europe", link: "destinations/espagne.html" },
        { name: "États-Unis", image: "usa.jpg", continent: "america", link: "destinations/usa.html" },
        { name: "Australie", image: "australie.jpg", continent: "oceania", link: "destinations/australie.html" },
        { name: "Maroc", image: "maroc.jpg", continent: "africa", link: "destinations/maroc.html" },
        { name: "Brésil", image: "bresil.jpg", continent: "america", link: "destinations/bresil.html" },
        { name: "Thaïlande", image: "thailande.jpg", continent: "asia", link: "destinations/thailande.html" },
        { name: "Égypte", image: "egypte.jpg", continent: "africa", link: "destinations/egypte.html" },
        { name: "Norvège", image: "norvege.jpg", continent: "europe", link: "destinations/norvege.html" },
        { name: "Tunisie", image: "tunisie.jpg", continent: "africa", link: "destinations/tunisie.html" },
        { name: "Turquie", image: "turquie.jpg", continent: "europe", link: "destinations/turquie.html" },
        { name: "Allemagne", image: "allemagne.jpg", continent: "europe", link: "destinations/allemagne.html" },
        { name: "Tchad", image: "tchad.jpg", continent: "africa", link: "destinations/tchad.html" },
        { name: "Inde", image: "inde.jpg", continent: "asia", link: "destinations/inde.html" },
        { name: "Maldives", image: "maldives.jpg", continent: "asia", link: "destinations/maldives.html" },
        { name: "Russie", image: "russie.jpg", continent: "europe", link: "destinations/russie.html" }
    ];

    // Variables de pagination
    let currentIndex = 0;
    const perLoad = 4;
    let filteredData = [...destinationsData];

    // Générer une carte de destination
    function createDestinationCard(destination) {
        const card = document.createElement('a');
        card.href = destination.link;
        card.className = 'destination-card fade-in';
        card.setAttribute('data-continent', destination.continent);
        card.innerHTML = `
            <div class="card-image" style="background-image: url('assets/img/destinations/${destination.image}')"></div>
            <div class="card-overlay">
                <h3>${destination.name}</h3>
                <span class="explore-btn">Voir détails <i class="fas fa-arrow-right"></i></span>
            </div>
        `;
        return card;
    }

    // Charger les destinations
    function loadDestinations() {
        const fragment = document.createDocumentFragment();
        const endIndex = Math.min(currentIndex + perLoad, filteredData.length);
        
        for (let i = currentIndex; i < endIndex; i++) {
            const card = createDestinationCard(filteredData[i]);
            fragment.appendChild(card);
        }
        
        destinationsGrid.appendChild(fragment);
        currentIndex = endIndex;
        
        // Mettre à jour le compteur
        counterDisplay.textContent = `Découvrez nos destinations (${Math.min(currentIndex, filteredData.length)}/${filteredData.length} affichées)`;
        
        // Masquer le bouton si tout est chargé
        loadMoreBtn.style.display = currentIndex >= filteredData.length ? 'none' : 'block';
        
        // Observer les nouvelles cartes pour les animations
        observeNewCards();
    }

    // Observer les nouvelles cartes pour les animations
    function observeNewCards() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        const cards = document.querySelectorAll('.destination-card:not(.fade-in-visible)');
        cards.forEach(card => observer.observe(card));
    }

    // Filtrage par continent
    function applyFilter() {
        const selectedContinent = continentFilter.value;
        filteredData = selectedContinent === 'all' 
            ? [...destinationsData] 
            : destinationsData.filter(d => d.continent === selectedContinent);
        
        // Réinitialiser la pagination
        currentIndex = 0;
        destinationsGrid.innerHTML = '';
        loadDestinations();
    }

    // Réinitialiser le filtre
    function resetFilter() {
        continentFilter.value = 'all';
        applyFilter();
    }

    // Événements
    loadMoreBtn.addEventListener('click', loadDestinations);
    continentFilter.addEventListener('change', applyFilter);
    resetFilterBtn.addEventListener('click', resetFilter);

    // Initialisation
    loadDestinations();
});