<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WorldVenture</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #ffffff;
      position: relative;
      min-height: 100vh;
      overflow-x: hidden;
    }

    .background-image {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 50%;
      background-image: url('background.jpg');
      background-size: cover;
      background-position: center;
      filter: brightness(0.5) blur(2px);
      z-index: -2;
    }

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 50%;
      background: linear-gradient(to bottom right, rgba(10, 10, 30, 0.4), rgba(0, 70, 140, 0.4));
      z-index: -1;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 0.5rem 2rem;
      position: absolute;
      top: 0;
      width: 100%;
      z-index: 100;
    }

    .logo {
      width: 150px;
      height: auto;
      margin-top: 0.5rem;
    }

    .slogan {
      font-size: 3.2rem;
      font-weight: 900;
      color: #cceeff;
      text-shadow: 2px 2px 8px #000000;
      text-align: center;
      margin-top: 6rem;
      position: relative;
      padding: 0 2rem;
    }

    .main-nav {
      display: flex;
      justify-content: center;
      margin: 4rem auto 2rem; /* Augmenté la marge supérieure pour descendre les boutons */
      width: 100%;
    }

    .main-nav-links {
      display: flex;
      gap: 1.5rem;
    }

    .main-nav-links a {
      background: linear-gradient(135deg, #1e90ff, #0099cc);
      padding: 1.2rem 2.5rem;
      border-radius: 2rem;
      color: white;
      font-size: 1.4rem;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
      border: 2px solid #003366;
    }

    .main-nav-links a:hover {
      transform: translateY(-6px) scale(1.03);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
      background: linear-gradient(135deg, #00bfae, #0099cc);
    }

    .login-btn {
      background: linear-gradient(135deg, #1e90ff, #0099cc);
      padding: 1rem 2rem;
      border-radius: 2rem;
      color: white;
      font-size: 1.3rem;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
      border: 2px solid #003366;
      margin-top: 0.5rem;
    }

    .login-btn:hover {
      transform: translateY(-6px) scale(1.03);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
      background: linear-gradient(135deg, #00bfae, #0099cc);
    }

    .attraction-section {
      margin: 20rem auto 5rem; /* Ajusté pour descendre la section */
      max-width: 1000px;
      text-align: center;
      padding: 3rem;
      background: linear-gradient(135deg, #4e79b7, #1e4e8e);
      border-radius: 2rem;
      backdrop-filter: blur(6px);
      animation: fadeIn 2s ease;
      border: 2px solid #003366;
    }

    .attraction-section h2 {
      font-size: 3rem;
      color: #ffffff;
      margin-bottom: 1.5rem;
    }

    .attraction-section p {
      font-size: 1.6rem;
      color: #e6f7ff;
      margin-bottom: 2rem;
    }

    .attraction-section button {
      padding: 1rem 2.5rem;
      font-size: 1.4rem;
      font-weight: bold;
      color: #ffffff;
      background: linear-gradient(135deg, #00bfff, #007acc);
      border: none;
      border-radius: 2rem;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
      display: inline-block;
      margin-top: 1.5rem;
    }

    .attraction-section button:hover {
      background: linear-gradient(135deg, #007acc, #005f99);
      transform: scale(1.05);
    }

    .highlight-boxes {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 2rem;
      margin-top: 3rem;
    }

    .highlight-box {
      background-color: rgba(255, 255, 255, 0.1);
      padding: 2rem;
      border-radius: 1.5rem;
      width: 250px;
      text-align: center;
      backdrop-filter: blur(6px);
      transition: transform 0.3s ease;
    }

    .highlight-box:hover {
      transform: translateY(-8px);
    }

    .highlight-box h3 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: #cceeff;
    }

    .highlight-box p {
      font-size: 1.2rem;
      color: #e0f0ff;
    }

    .about-us {
      position: fixed;
      bottom: 1.5rem;
      left: 2rem;
      color: #003366;
      font-size: 1.5rem; /* Taille augmentée */
      font-weight: bold;
      text-decoration: none;
      z-index: 100;
    }

    .about-us:hover {
      text-decoration: underline;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    main#content {
      padding: 4rem 2rem;
      text-align: center;
      font-size: 2.8rem;
      font-weight: bold;
      background-color: rgba(0, 0, 0, 0.4);
      margin: 4rem 2rem;
      border-radius: 2rem;
      color: #ffffff;
      backdrop-filter: blur(4px);
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }

    @media (max-width: 768px) {
      header {
        padding: 0.3rem 1rem;
      }
      .logo {
        width: 120px;
      }
      .slogan {
        font-size: 2.2rem;
        margin-top: 5rem;
      }
      .main-nav {
        margin: 3rem auto 1.5rem;
      }
      .main-nav-links {
        flex-wrap: wrap;
        justify-content: center;
        gap: 1rem;
      }
      .main-nav-links a {
        font-size: 1.1rem;
        padding: 1rem 1.8rem;
      }
      .login-btn {
        font-size: 1.1rem;
        padding: 0.8rem 1.5rem;
      }
      .attraction-section {
        margin: 16rem auto 3rem;
        padding: 2rem;
      }
      .attraction-section h2 {
        font-size: 2.2rem;
      }
      .attraction-section p {
        font-size: 1.2rem;
      }
      .about-us {
        font-size: 1.2rem;
        bottom: 1rem;
        left: 1rem;
      }
    }

    /* Blog section styles */
    .blog-section {
      margin: 6rem auto;
      max-width: 1200px;
      padding: 2rem;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 1.5rem;
      border: 2px solid #003366;
    }
    
    .blog-section h2 {
      font-size: 2.5rem;
      color: #ffffff;
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .blog-preview {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2rem;
    }
    
    .blog-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      color: #0b2447;
    }
    
    .blog-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.3);
    }
    
    .blog-card-img {
      height: 180px;
      background: linear-gradient(135deg, #1e90ff, #0099cc);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 2rem;
    }
    
    .blog-card-content {
      padding: 1.5rem;
    }
    
    .blog-card-title {
      font-size: 1.3rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
      color: #0b2447;
    }
    
    .blog-card-excerpt {
      font-size: 1rem;
      color: #64748b;
      margin-bottom: 1.5rem;
      line-height: 1.5;
    }
    
    .blog-card-meta {
      display: flex;
      justify-content: space-between;
      font-size: 0.85rem;
      color: #64748b;
      border-top: 1px solid #e2e8f0;
      padding-top: 1rem;
    }
    
    .blog-card-link {
      display: inline-block;
      background: linear-gradient(135deg, #1e90ff, #0099cc);
      color: white;
      padding: 0.5rem 1.2rem;
      border-radius: 2rem;
      text-decoration: none;
      font-weight: 600;
      margin-top: 0.5rem;
      transition: transform 0.2s;
    }
    
    .blog-card-link:hover {
      transform: translateY(-2px);
    }
  </style>
</head>
<body>
  <div class="background-image"></div>
  <div class="overlay"></div>
  
  <header>
    <img src="logo.png" alt="WorldVenture Logo" class="logo">
    <a href="#" class="login-btn" onclick="handleClick('login')">Se connecter</a>
  </header>

  <h1 class="slogan">Your next adventure starts here</h1>

  <nav class="main-nav">
    <div class="main-nav-links">
      <a href="#" onclick="handleClick('destinations')">Destinations</a>
      <a href="#" onclick="handleClick('offres')">Nos offres</a>
      <a href="../blog part/views/blog_frontend.php">Blog</a>
      <a href="#" onclick="handleClick('contact')">Contact</a>
      <a href="#" onclick="handleClick('reclamation')">Réclamation</a>
    </div>
  </nav>

  <section class="attraction-section">
    <h2>Explorez le monde avec WorldVenture</h2>
    <p>Des offres exclusives, des destinations de rêve, un service client 24/7.<br>
    Lancez votre prochaine aventure dès aujourd'hui avec nous !</p>
    <button onclick="alert('Lancement des offres bientôt disponible !')">Je découvre</button>

    <div class="highlight-boxes">
      <div class="highlight-box">
        <h3>Destinations de rêve</h3>
        <p>Découvrez des lieux magiques sélectionnés avec soin pour vous émerveiller.</p>
      </div>
      <div class="highlight-box">
        <h3>Offres personnalisées</h3>
        <p>Voyagez selon vos envies et votre budget avec nos formules exclusives.</p>
      </div>
      <div class="highlight-box">
        <h3>Communauté & Blog</h3>
        <p>Lisez les récits inspirants d'autres aventuriers et partagez le vôtre.</p>
      </div>
    </div>
  </section>

  <!-- Blog Section -->
  <section class="blog-section">
    <h2>Derniers Articles de Blog</h2>
    <div class="blog-preview">
      <?php
      // Include blog controller to fetch latest posts
      require_once '../blog part/controllers/controller.php';
      
      // Create controller instance
      $controller = new BlogController();
      
      // Get latest posts (limited to 3)
      $latestPosts = $controller->getLatestPosts(3);
      
      if (!empty($latestPosts)) {
        foreach ($latestPosts as $post) {
          echo '<div class="blog-card">';
          echo '<div class="blog-card-img"><i class="fas fa-blog"></i></div>';
          echo '<div class="blog-card-content">';
          echo '<h3 class="blog-card-title">' . htmlspecialchars($post['title']) . '</h3>';
          echo '<p class="blog-card-excerpt">' . htmlspecialchars(substr($post['content'], 0, 120)) . '...</p>';
          echo '<div class="blog-card-meta">';
          echo '<span>' . date('d M Y', strtotime($post['created_at'])) . '</span>';
          echo '<span>' . $post['reactions'] . ' <i class="fas fa-thumbs-up"></i></span>';
          echo '</div>';
          echo '<a href="../blog part/views/post_details.php?id=' . $post['id'] . '" class="blog-card-link">Lire Plus</a>';
          echo '</div></div>';
        }
      } else {
        echo '<p style="text-align: center; color: white;">Aucun article de blog disponible actuellement.</p>';
      }
      ?>
    </div>
    <div style="text-align: center; margin-top: 2rem;">
      <a href="../blog part/views/blog_frontend.php" class="blog-card-link" style="font-size: 1.2rem; padding: 0.7rem 1.5rem;">
        Voir Tous Les Articles <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  </section>

  <a href="#" class="about-us" onclick="handleClick('about')">Qui sommes nous ?</a>

  <main id="content">
    <!-- Le contenu changera ici plus tard -->
  </main>

  <!-- Add Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <script>
    function handleClick(section) {
      console.log(`Clicked on: ${section}`);
      document.getElementById('content').innerHTML = `<h2>Section "${section}" est en construction...</h2>`;
    }
  </script>
</body>
</html>













