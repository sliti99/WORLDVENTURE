document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const addBtn = document.getElementById('add-destination');
    const modal = document.getElementById('destination-modal');
    const closeBtn = document.querySelector('.close-btn');
    const form = document.getElementById('destination-form');
    const tableBody = document.querySelector('tbody');

    // Données simulées
    let destinations = [
        { id: 1, name: 'France', continent: 'europe', image: 'france.jpg' },
        { id: 2, name: 'Japon', continent: 'asia', image: 'japon.jpg' }
    ];

    // Afficher les destinations
    function renderDestinations() {
        tableBody.innerHTML = '';
        destinations.forEach(dest => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${dest.id}</td>
                <td><img src="assets/img/destinations/${dest.image}" width="50"></td>
                <td>${dest.name}</td>
                <td>${dest.continent}</td>
                <td>
                    <button class="edit-btn" data-id="${dest.id}"><i class="fas fa-edit"></i></button>
                    <button class="delete-btn" data-id="${dest.id}"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        // Ajouter les événements
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', deleteDestination);
        });
    }

    // Gérer la modale
    function toggleModal() {
        modal.classList.toggle('hidden');
        document.body.classList.toggle('no-scroll');
    }

    // Ajouter une destination
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('destination-name').value;
        const continent = document.getElementById('destination-continent').value;
        const imageInput = document.getElementById('destination-image');
        
        // Simulation d'upload
        const imageName = imageInput.files[0] ? imageInput.files[0].name : 'default.jpg';
        
        // Ajouter à la liste
        const newId = destinations.length > 0 ? Math.max(...destinations.map(d => d.id)) + 1 : 1;
        destinations.push({
            id: newId,
            name,
            continent,
            image: imageName
        });
        
        // Réinitialiser et fermer
        form.reset();
        toggleModal();
        renderDestinations();
        
        alert('Destination ajoutée avec succès!');
    });

    // Supprimer une destination
    function deleteDestination(e) {
        const id = parseInt(e.target.closest('button').dataset.id);
        if (confirm('Supprimer cette destination ?')) {
            destinations = destinations.filter(d => d.id !== id);
            renderDestinations();
        }
    }

    // Événements
    addBtn.addEventListener('click', toggleModal);
    closeBtn.addEventListener('click', toggleModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) toggleModal();
    });

    // Initialisation
    renderDestinations();
});