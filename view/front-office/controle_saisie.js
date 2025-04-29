 document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    form.addEventListener('submit', function (event) {
        let valid = true;

        // Réinitialiser les messages d'erreur
        resetErrors();

        // Validation du nom
        const nom = document.getElementById('nom');
        if (!nom.value.trim()) {
            valid = false;
            document.getElementById('nomError').textContent = "Veuillez entrer votre nom complet.";
            document.getElementById('nomError').style.display = "block";
        }

        // Validation de l'email
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim()) {
            valid = false;
            document.getElementById('emailError').textContent = "Veuillez entrer une adresse email.";
            document.getElementById('emailError').style.display = "block";
        } else if (!emailRegex.test(email.value)) {
            valid = false;
            document.getElementById('emailError').textContent = "Veuillez entrer une adresse email valide.";
            document.getElementById('emailError').style.display = "block";
        }

        // Validation du mot de passe
        const mdp = document.getElementById('mdp');
        if (!mdp.value.trim()) {
            valid = false;
            document.getElementById('mdpError').textContent = "Veuillez entrer un mot de passe.";
            document.getElementById('mdpError').style.display = "block";
        } else if (mdp.value.length < 6) {
            valid = false;
            document.getElementById('mdpError').textContent = "Le mot de passe doit contenir au moins 6 caractères.";
            document.getElementById('mdpError').style.display = "block";
        }

        // Validation du téléphone
        const tel = document.getElementById('tel');
        const telRegex = /^[0-9]{8}$/;
        if (!tel.value.trim()) {
            valid = false;
            document.getElementById('telError').textContent = "Veuillez entrer un numéro de téléphone.";
            document.getElementById('telError').style.display = "block";
        } else if (!telRegex.test(tel.value)) {
            valid = false;
            document.getElementById('telError').textContent = "Le numéro de téléphone doit comporter 8 chiffres.";
            document.getElementById('telError').style.display = "block";
        }

        // Validation de la ville
        const ville = document.getElementById('ville');
        if (!ville.value || ville.selectedIndex === 0) {
            valid = false;
            const errorSpan = document.createElement('span');
            errorSpan.textContent = "Veuillez sélectionner une ville.";
            errorSpan.className = "error";
            errorSpan.style.display = "block";
            errorSpan.style.color = "red";
            ville.parentNode.appendChild(errorSpan);
        }

        // Validation de la date de naissance
        const daten = document.getElementById('daten');
        if (!daten.value) {
            valid = false;
            document.getElementById('datenError').textContent = "Veuillez sélectionner une date de naissance.";
            document.getElementById('datenError').style.display = "block";
        }

        if (!valid) {
            event.preventDefault();
        }
    });

    // Réinitialisation des messages d'erreur
    function resetErrors() {
        const errorSpans = document.querySelectorAll('.error');
        errorSpans.forEach(span => {
            span.textContent = "";
            span.style.display = "none";
        });

        // Supprimer les spans d'erreur ajoutés dynamiquement (ville)
        const ville = document.getElementById('ville');
        const nextSiblings = Array.from(ville.parentNode.childNodes).slice(1);
        nextSiblings.forEach(el => {
            if (el.className === "error") el.remove();
        });
    }
});
 
