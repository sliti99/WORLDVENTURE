
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche d'hôtels</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #315f9e; /* Bleu roi */
            background-image: url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-blend-mode: overlay;
            min-height: 100vh;
        }
        .search-container {
            max-width: 800px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(5px);
        }
        h1 {
            text-align: center;
            color: #cceeff;
            margin-bottom: 25px;
            font-size: 28px;
            position: relative;
            padding-bottom: 15px;
        }
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #cceeff, #cceeff);
            border-radius: 2px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #002D72;
            font-size: 15px;
        }
        select, input[type="date"], input[type="number"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(65, 105, 225, 0.3);
            border-radius: 8px;
            font-size: 15px;
            background-color: #f8faff;
            transition: all 0.3s ease;
        }
        select, input[type="text"], input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(65, 105, 225, 0.3);
            border-radius: 8px;
            font-size: 15px;
            background-color: #f8faff;
            transition: all 0.3s ease;
        }
        select:focus, input:focus {
            outline: none;
            border-color: #4169E1;
            box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.2);
        }
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234169E1' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
        }
        .counter {
            display: flex;
            align-items: center;
        }
        .counter button {
            padding: 10px 15px;
            background: linear-gradient(to bottom, #1593f0, #1593f0);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 45, 114, 0.2);
        }
        .counter button:hover {
            background: linear-gradient(to bottom, #00b6b5, #00b6b5);
            transform: translateY(-1px);
        }
        .counter button:active {
            transform: translateY(0);
        }
        .counter input {
            width: 60px;
            text-align: center;
            margin: 0 12px;
            font-weight: 600;
            color: #002D72;
        }
        .search-btn {
            background: linear-gradient(to right, #0f94e6, #4169E1);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 17px;
            font-weight: 600;
            width: 100%;
            margin-top: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 45, 114, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .search-btn:hover {
            background: linear-gradient(to right, #00b6b5, #00b6b5);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 45, 114, 0.4);
        }
        .search-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 600px) {
            .search-container {
                padding: 20px;
                margin: 20px auto;
            }
            h1 {
                font-size: 24px;
            }
            .counter button {
                padding: 8px 12px;
            }
            body {
                padding: 10px;
                background-attachment: scroll;
            }
        }
    </style>
</head>
<body>
    <div class="search-container">
        <h1>Remplir ce formualire pour creer un compte </h1>
        <form action="../ajout.php" method="POST">
            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom" class="form-control"  >
                <span id="nomError" class="error" style="display:none; color:red;"></span>
            </div>
        
            <div class="form-group">
                <label for="email">Adresse Email</label>
                <input type="text" id="email" name="email" class="form-control"  >
                <span id="emailError" class="error" style="display:none; color:red;"></span>
            </div>
        
            <div class="form-group">
                <label for="mdp">Mot de passe</label>
                <input type="text" id="mdp" name="mdp" class="form-control"  >
                <span id="mdpError" class="error" style="display:none; color:red;"></span>
            </div>
        
            <div class="form-group">
                <label for="tel">Téléphone</label>
                <input type="text" id="tel" name="tel" class="form-control"  >
                <span id="telError" class="error" style="display:none; color:red;"></span>
            </div>
        
            <select id="ville" name="ville" class="form-control">
                <option value="">-- Sélectionnez une ville --</option>
                <option value="Tunis">Tunis</option>
                <option value="Ariana">Ariana</option>
                <option value="Ben Arous">Ben Arous</option>
                <option value="La Manouba">La Manouba</option>
                <option value="Nabeul">Nabeul</option>
                <option value="Sousse">Sousse</option>
                <option value="Monastir">Monastir</option>
                <option value="Mahdia">Mahdia</option>
                <option value="Sfax">Sfax</option>
                <option value="Gabès">Gabès</option>
                <option value="Gafsa">Gafsa</option>
                <option value="Kairouan">Kairouan</option>
                <option value="Kasserine">Kasserine</option>
                <option value="Médenine">Médenine</option>
                <option value="Tataouine">Tataouine</option>
                <option value="Tozeur">Tozeur</option>
                <option value="Kebili">Kebili</option>
                <option value="Jendouba">Jendouba</option>
                <option value="Beja">Beja</option>
                <option value="Le Kef">Le Kef</option>
                <option value="Siliana">Siliana</option>
                <option value="Zaghouan">Zaghouan</option>
            </select>
            
        
            <div class="form-group">
                <label for="daten">Date de naissance</label>
                <input type="date" id="daten" name="daten" class="form-control"  >
                <span id="datenError" class="error" style="display:none; color:red;"></span>
            </div>

            <input type="hidden" id="role" name="role" value="utilisateur normal">

        
            <button class="search-btn" > S'inscrire </button>
        </form>
        
       
    </div>
    <script>
      function adjustPlaceCount(change) {
        var placeInput = document.getElementById('place');
        var currentValue = parseInt(placeInput.value);
        var newValue = currentValue + change;
        if (newValue >= 1) {
            placeInput.value = newValue;
        }
    }
    </script>
    <script src="controle_saisie.js"></script>
</body>
</html>
 