<?php
if (!isset($_SESSION)) {
    session_start();
}

$base_url = '/diamon_luxe';

// Compter les articles du panier si connecté
$nb_panier = 0;
if (isset($_SESSION['client_id'])) {
    require_once __DIR__ . '/../models/Panier.php';
    $nb_panier = Panier::compter($_SESSION['client_id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'DIAMON - Luxe d\'Occasion' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Dark Mode CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/darkmode.css">
    
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        .font-serif {
            font-family: 'Playfair Display', serif;
        }
        .bg-gold {
            background-color: #C5A059;
        }
        .text-gold {
            color: #C5A059;
        }
        .border-gold {
            border-color: #C5A059;
        }
        
        /* Animation pour la barre de recherche */
        .search-bar-container {
            transition: all 0.3s ease;
        }
        
        .search-suggestions {
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Animation du panier */
        .cart-badge {
            animation: pulse 0.5s ease;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
    </style>
</head>
<body class="bg-white">

    <!-- Barre supérieure -->
    <div class="bg-black text-white py-2 text-xs">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center space-x-6">
                <span>📧 contact@diamon.fr</span>
                <span>📞 01 23 45 67 89</span>
            </div>
            <div class="flex items-center space-x-4">
                <span>Livraison offerte dès 100€</span>
            </div>
        </div>
    </div>

    <!-- Navigation principale -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo -->
                <a href="<?= $base_url ?>/index.php" class="text-3xl font-serif italic tracking-wider">
                    DIAMON
                </a>

                <!-- Barre de recherche centrale -->
                <div class="flex-1 max-w-2xl mx-8 relative" id="searchContainer">
                    <div class="relative">
                        <input type="text" 
                               id="globalSearchInput"
                               placeholder="Rechercher un produit, une marque... (Ctrl+K)"
                               autocomplete="off"
                               class="w-full px-5 py-3 border border-gray-300 rounded-full focus:border-gold focus:outline-none pr-12 text-sm">
                        <button onclick="redirectToSearch()" 
                                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gold transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Suggestions de recherche -->
                    <div id="searchSuggestions" 
                         class="hidden absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-2 z-50">
                        <div class="search-suggestions p-4">
                            <!-- Les suggestions seront injectées ici -->
                        </div>
                    </div>
                </div>

                <!-- Menu navigation -->
                <div class="flex items-center space-x-6">
                    <a href="<?= $base_url ?>/catalogue.php" 
                       class="text-xs uppercase tracking-widest font-semibold hover:text-gold transition">
                        Acheter
                    </a>
                    <a href="<?= $base_url ?>/vendre.php" 
                       class="text-xs uppercase tracking-widest font-semibold hover:text-gold transition">
                        Vendre
                    </a>
                    <a href="<?= $base_url ?>/echanger.php" 
                       class="text-xs uppercase tracking-widest font-semibold hover:text-gold transition">
                        Échanger
                    </a>
                    
                    <?php if (isset($_SESSION['client_id'])): ?>
                        <a href="<?= $base_url ?>/compte.php" 
                           class="text-xs uppercase tracking-widest font-semibold hover:text-gold transition">
                            Mon Compte
                        </a>
                        <a href="<?= $base_url ?>/deconnexion.php" 
                           class="text-xs uppercase tracking-widest font-semibold hover:text-gold transition">
                            Déconnexion
                        </a>
                    <?php else: ?>
                        <a href="<?= $base_url ?>/connexion.php" 
                           class="text-xs uppercase tracking-widest font-semibold hover:text-gold transition">
                            Connexion
                        </a>
                    <?php endif; ?>
                    
                    <!-- Toggle Dark Mode -->
                    <button id="darkModeToggle" 
                            class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition"
                            title="Changer le thème"
                            aria-label="Toggle dark mode">
                        <!-- Icône Soleil (mode clair) -->
                        <svg id="sunIcon" class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                        
                        <!-- Icône Lune (mode sombre) -->
                        <svg id="moonIcon" class="hidden w-5 h-5 text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>
                    
                    <!-- Wishlist -->
                    <?php
                    $nb_wishlist = 0;
                    if (isset($_SESSION['client_id'])) {
                        require_once __DIR__ . '/../models/Wishlist.php';
                        $nb_wishlist = Wishlist::compter($_SESSION['client_id']);
                    }
                    ?>
                    <a href="<?= $base_url ?>/wishlist.php" 
                       class="relative hover:text-gold transition"
                       title="Ma liste de souhaits">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <?php if ($nb_wishlist > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                <?= $nb_wishlist ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Panier -->
                    <a href="<?= $base_url ?>/views/panier.php" 
                       class="relative hover:text-gold transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <?php if ($nb_panier > 0): ?>
                            <span class="cart-badge absolute -top-2 -right-2 bg-gold text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                <?= $nb_panier ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        const baseUrl = '<?= $base_url ?>';
        let searchTimeout;
        let currentFocus = -1;

        const searchInput = document.getElementById('globalSearchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');

        // Recherche en temps réel
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchSuggestions.classList.add('hidden');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                fetchSuggestions(query);
            }, 300);
        });

        // Touche Entrée
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                redirectToSearch();
            }
        });

        // Clic en dehors ferme les suggestions
        document.addEventListener('click', function(e) {
            if (!document.getElementById('searchContainer').contains(e.target)) {
                searchSuggestions.classList.add('hidden');
            }
        });

        // Récupérer les suggestions
        async function fetchSuggestions(query) {
            try {
                const response = await fetch(`${baseUrl}/recherche_suggestions.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success && data.produits.length > 0) {
                    displaySuggestions(data.produits);
                } else {
                    searchSuggestions.classList.add('hidden');
                }
            } catch (error) {
                console.error('Erreur suggestions:', error);
            }
        }

        // Afficher les suggestions
        function displaySuggestions(produits) {
            const container = searchSuggestions.querySelector('.search-suggestions');
            
            let html = '<div class="mb-3 text-xs uppercase tracking-wider text-gray-500 font-semibold">Suggestions</div>';
            
            html += produits.map(p => `
                <a href="${baseUrl}/views/produit_detail.php?id=${p.id}" 
                   class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg transition group">
                    <div class="w-16 h-16 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                        ${p.image_principale ? 
                            `<img src="${baseUrl}/public/images/produits/${p.image_principale}" 
                                 class="w-full h-full object-cover"
                                 alt="${p.nom}">` :
                            `<div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>`
                        }
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">
                            ${p.marque}
                        </p>
                        <p class="text-sm font-medium text-gray-900 truncate group-hover:text-gold transition">
                            ${p.nom}
                        </p>
                        <p class="text-sm font-serif text-gold mt-1">
                            ${new Intl.NumberFormat('fr-FR').format(p.prix)} €
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gold transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            `).join('');
            
            html += `
                <button onclick="redirectToSearch()" 
                        class="w-full mt-3 px-4 py-3 bg-black text-white text-sm font-semibold rounded-lg hover:bg-gold transition">
                    Voir tous les résultats
                </button>
            `;
            
            container.innerHTML = html;
            searchSuggestions.classList.remove('hidden');
        }

        // Rediriger vers la page de recherche
        function redirectToSearch() {
            const query = searchInput.value.trim();
            if (query) {
                window.location.href = `${baseUrl}/recherche.php?q=${encodeURIComponent(query)}`;
            } else {
                window.location.href = `${baseUrl}/recherche.php`;
            }
        }
        
        // ========== DARK MODE ==========
        
        // Récupérer le thème sauvegardé ou utiliser le thème système
        function getInitialTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                return savedTheme;
            }
            
            // Détecter la préférence système
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                return 'dark';
            }
            
            return 'light';
        }

        // Appliquer le thème
        function applyTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.getElementById('sunIcon').classList.add('hidden');
                document.getElementById('moonIcon').classList.remove('hidden');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                document.getElementById('sunIcon').classList.remove('hidden');
                document.getElementById('moonIcon').classList.add('hidden');
            }
            
            // Sauvegarder la préférence
            localStorage.setItem('theme', theme);
        }

        // Toggle du thème
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            
            // Animation du bouton
            const button = document.getElementById('darkModeToggle');
            button.style.transform = 'rotate(360deg)';
            setTimeout(() => {
                button.style.transform = 'rotate(0deg)';
            }, 300);
        }

        // Appliquer le thème au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const initialTheme = getInitialTheme();
            applyTheme(initialTheme);
            
            // Ajouter l'écouteur d'événements
            document.getElementById('darkModeToggle').addEventListener('click', toggleTheme);
            
            // Transition fluide pour le bouton
            document.getElementById('darkModeToggle').style.transition = 'transform 0.3s ease';
        });

        // Écouter les changements de préférence système
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                if (!localStorage.getItem('theme')) {
                    applyTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
        
        // Raccourci clavier pour la recherche (Ctrl+K ou Cmd+K)
        document.addEventListener('keydown', function(e) {
            // Ctrl+K ou Cmd+K pour focus sur la recherche
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
            
            // Echap pour fermer les suggestions
            if (e.key === 'Escape') {
                searchSuggestions.classList.add('hidden');
                searchInput.blur();
            }
        });
    </script>