<?php 
$page_title = "Recherche | DIAMON";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/models/Recherche.php';

// Récupérer les données pour les filtres
$categories = $pdo->query("SELECT * FROM categorie ORDER BY nom")->fetchAll();
$grades = $pdo->query("SELECT * FROM grade ORDER BY code")->fetchAll();
$marques = Recherche::getMarquesDisponibles();
$plage_prix = Recherche::getPlagePrix();

// Récupérer les recherches récentes si connecté
$recherches_recentes = [];
if (isset($_SESSION['client_id'])) {
    $recherches_recentes = Recherche::getRecherchesRecentes($_SESSION['client_id']);
}

// Paramètres initiaux depuis l'URL
$params_initiaux = [
    'q' => $_GET['q'] ?? '',
    'categorie' => $_GET['categorie'] ?? null,
    'grade' => $_GET['grade'] ?? null,
    'marque' => $_GET['marque'] ?? null,
    'prix_min' => $_GET['prix_min'] ?? '',
    'prix_max' => $_GET['prix_max'] ?? '',
    'sort' => $_GET['sort'] ?? 'nouveautes'
];
?>

<main class="pt-32 pb-24">
    <div class="max-w-7xl mx-auto px-6">
        
        <!-- Barre de recherche principale -->
        <div class="mb-12">
            <div class="max-w-3xl mx-auto">
                <div class="relative">
                    <input type="text" 
                           id="searchInput"
                           value="<?= htmlspecialchars($params_initiaux['q']) ?>"
                           placeholder="Rechercher un produit, une marque..."
                           class="w-full px-6 py-5 text-lg border-2 border-gray-300 rounded-lg focus:border-gold focus:outline-none pr-12">
                    <button onclick="lancerRecherche()" 
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gold hover:text-black transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Recherches récentes -->
                <?php if (count($recherches_recentes) > 0): ?>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="text-sm text-gray-500">Recherches récentes :</span>
                        <?php foreach ($recherches_recentes as $recherche): ?>
                            <button onclick="document.getElementById('searchInput').value='<?= htmlspecialchars($recherche['query']) ?>'; lancerRecherche();"
                                    class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full transition">
                                <?= htmlspecialchars($recherche['query']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex gap-8">
            
            <!-- Sidebar Filtres -->
            <aside class="w-80 flex-shrink-0">
                <div class="bg-white rounded-lg shadow p-6 sticky top-32">
                    
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Filtres</h2>
                        <button onclick="resetFiltres()" 
                                class="text-sm text-gold hover:underline">
                            Réinitialiser
                        </button>
                    </div>

                    <!-- Catégories -->
                    <div class="mb-6 pb-6 border-b">
                        <h3 class="font-semibold mb-3 text-sm uppercase tracking-wider">Catégories</h3>
                        <div class="space-y-2">
                            <?php foreach ($categories as $cat): ?>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded transition">
                                    <input type="checkbox" 
                                           name="categorie[]" 
                                           value="<?= $cat['id'] ?>"
                                           class="w-4 h-4 text-gold border-gray-300 rounded focus:ring-gold filtre-checkbox">
                                    <span class="ml-3 text-sm"><?= htmlspecialchars($cat['nom']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Grades -->
                    <div class="mb-6 pb-6 border-b">
                        <h3 class="font-semibold mb-3 text-sm uppercase tracking-wider">État</h3>
                        <div class="space-y-2">
                            <?php foreach ($grades as $grade): ?>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded transition">
                                    <input type="checkbox" 
                                           name="grade[]" 
                                           value="<?= $grade['id'] ?>"
                                           class="w-4 h-4 text-gold border-gray-300 rounded focus:ring-gold filtre-checkbox">
                                    <span class="ml-3 text-sm">
                                        Grade <?= htmlspecialchars($grade['code']) ?> - <?= htmlspecialchars($grade['nom']) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Marques -->
                    <div class="mb-6 pb-6 border-b">
                        <h3 class="font-semibold mb-3 text-sm uppercase tracking-wider">Marques</h3>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <?php foreach ($marques as $marque): ?>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded transition">
                                    <input type="checkbox" 
                                           name="marque[]" 
                                           value="<?= htmlspecialchars($marque['marque']) ?>"
                                           class="w-4 h-4 text-gold border-gray-300 rounded focus:ring-gold filtre-checkbox">
                                    <span class="ml-3 text-sm">
                                        <?= htmlspecialchars($marque['marque']) ?>
                                        <span class="text-gray-400">(<?= $marque['nb_produits'] ?>)</span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Prix -->
                    <div class="mb-6 pb-6 border-b">
                        <h3 class="font-semibold mb-3 text-sm uppercase tracking-wider">Prix</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs text-gray-500">Minimum</label>
                                <input type="number" 
                                       id="prixMin"
                                       placeholder="<?= number_format($plage_prix['prix_min'], 0, ',', ' ') ?> €"
                                       min="0"
                                       step="100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Maximum</label>
                                <input type="number" 
                                       id="prixMax"
                                       placeholder="<?= number_format($plage_prix['prix_max'], 0, ',', ' ') ?> €"
                                       min="0"
                                       step="100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            </div>
                            <button onclick="appliquerFiltrePrix()" 
                                    class="w-full bg-black text-white py-2 rounded text-sm font-semibold hover:bg-gold transition">
                                Appliquer
                            </button>
                        </div>
                    </div>

                    <!-- Autres filtres -->
                    <div class="mb-6">
                        <h3 class="font-semibold mb-3 text-sm uppercase tracking-wider">Autres</h3>
                        <div class="space-y-2">
                            <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded transition">
                                <input type="checkbox" 
                                       id="enStock"
                                       class="w-4 h-4 text-gold border-gray-300 rounded focus:ring-gold filtre-checkbox">
                                <span class="ml-3 text-sm">En stock uniquement</span>
                            </label>
                            
                            <div class="pt-2">
                                <label class="text-xs text-gray-500 block mb-2">Note minimum</label>
                                <select id="noteMin" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none text-sm filtre-select">
                                    <option value="">Toutes les notes</option>
                                    <option value="4">⭐⭐⭐⭐ et plus</option>
                                    <option value="3">⭐⭐⭐ et plus</option>
                                    <option value="2">⭐⭐ et plus</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </aside>

            <!-- Résultats -->
            <div class="flex-1">
                
                <!-- Barre de tri et compteur -->
                <div class="flex items-center justify-between mb-8">
                    <div id="compteurResultats" class="text-gray-600">
                        <span class="font-semibold" id="nbResultats">0</span> produit(s) trouvé(s)
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <label class="text-sm text-gray-600">Trier par :</label>
                        <select id="triSelect" 
                                class="px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none text-sm">
                            <option value="nouveautes">Nouveautés</option>
                            <option value="prix_asc">Prix croissant</option>
                            <option value="prix_desc">Prix décroissant</option>
                            <option value="nom_asc">Nom A-Z</option>
                            <option value="nom_desc">Nom Z-A</option>
                            <option value="populaire">Plus populaires</option>
                            <option value="note">Meilleures notes</option>
                        </select>
                    </div>
                </div>

                <!-- Loader -->
                <div id="loader" class="hidden text-center py-20">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-gold"></div>
                    <p class="mt-4 text-gray-600">Recherche en cours...</p>
                </div>

                <!-- Grille de produits -->
                <div id="resultatsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Les produits seront injectés ici via JavaScript -->
                </div>

                <!-- Message aucun résultat -->
                <div id="aucunResultat" class="hidden text-center py-20">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-2xl font-bold mb-2">Aucun produit trouvé</h3>
                    <p class="text-gray-600 mb-6">Essayez de modifier vos critères de recherche</p>
                    <button onclick="resetFiltres()" 
                            class="inline-block bg-black text-white px-8 py-3 rounded font-semibold hover:bg-gold transition">
                        Réinitialiser les filtres
                    </button>
                </div>

                <!-- Pagination -->
                <div id="pagination" class="mt-12 flex justify-center">
                    <!-- La pagination sera injectée ici -->
                </div>

            </div>

        </div>

    </div>
</main>

<script>
    const baseUrl = '<?= $base_url ?>';
    let currentPage = 1;
    let isLoading = false;

    // Fonction principale de recherche
    async function lancerRecherche(page = 1) {
        if (isLoading) return;
        
        isLoading = true;
        currentPage = page;
        
        // Afficher le loader
        document.getElementById('loader').classList.remove('hidden');
        document.getElementById('resultatsGrid').classList.add('opacity-50');
        document.getElementById('aucunResultat').classList.add('hidden');
        
        // Construire les paramètres
        const params = new URLSearchParams();
        
        // Mot-clé
        const query = document.getElementById('searchInput').value.trim();
        if (query) params.append('q', query);
        
        // Catégories
        document.querySelectorAll('input[name="categorie[]"]:checked').forEach(checkbox => {
            params.append('categorie[]', checkbox.value);
        });
        
        // Grades
        document.querySelectorAll('input[name="grade[]"]:checked').forEach(checkbox => {
            params.append('grade[]', checkbox.value);
        });
        
        // Marques
        document.querySelectorAll('input[name="marque[]"]:checked').forEach(checkbox => {
            params.append('marque[]', checkbox.value);
        });
        
        // Prix
        const prixMin = document.getElementById('prixMin').value;
        const prixMax = document.getElementById('prixMax').value;
        if (prixMin) params.append('prix_min', prixMin);
        if (prixMax) params.append('prix_max', prixMax);
        
        // En stock
        if (document.getElementById('enStock').checked) {
            params.append('en_stock', '1');
        }
        
        // Note minimum
        const noteMin = document.getElementById('noteMin').value;
        if (noteMin) params.append('note_min', noteMin);
        
        // Tri
        params.append('sort', document.getElementById('triSelect').value);
        
        // Pagination
        params.append('limit', '12');
        params.append('offset', (page - 1) * 12);
        
        try {
            const response = await fetch(`${baseUrl}/recherche_ajax.php?${params.toString()}`);
            const data = await response.json();
            
            if (data.success) {
                afficherResultats(data);
                
                // Mettre à jour l'URL sans recharger la page
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
            }
            
        } catch (error) {
            console.error('Erreur de recherche:', error);
            alert('Une erreur est survenue lors de la recherche');
        } finally {
            isLoading = false;
            document.getElementById('loader').classList.add('hidden');
            document.getElementById('resultatsGrid').classList.remove('opacity-50');
        }
    }

    // Afficher les résultats
    function afficherResultats(data) {
        const grid = document.getElementById('resultatsGrid');
        const compteur = document.getElementById('nbResultats');
        const aucunResultat = document.getElementById('aucunResultat');
        const pagination = document.getElementById('pagination');
        
        // Mettre à jour le compteur
        compteur.textContent = data.total;
        
        if (data.produits.length === 0) {
            grid.innerHTML = '';
            aucunResultat.classList.remove('hidden');
            pagination.innerHTML = '';
            return;
        }
        
        aucunResultat.classList.add('hidden');
        
        // Afficher les produits
        grid.innerHTML = data.produits.map(produit => `
            <div class="group relative">
                <!-- Bouton Wishlist -->
                <div class="absolute top-4 right-4 z-10">
                    <button onclick="toggleWishlistInline(${produit.id}, this)" 
                            class="w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-red-50 transition"
                            title="Ajouter aux favoris">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </button>
                </div>
                
                <a href="${baseUrl}/views/produit_detail.php?id=${produit.id}">
                    <div class="relative overflow-hidden aspect-[4/5] bg-gray-100 mb-4 rounded-lg">
                        ${produit.image_principale ? 
                            `<img src="${baseUrl}/public/images/produits/${produit.image_principale}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                                 alt="${produit.nom}">` :
                            `<div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>`
                        }
                        <div class="absolute top-4 left-4">
                            <span class="bg-black text-white px-3 py-1 text-[9px] font-bold tracking-widest uppercase rounded">
                                Grade ${produit.grade_code}
                            </span>
                        </div>
                        ${produit.stock <= 0 ? 
                            `<div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                <span class="bg-red-500 text-white px-4 py-2 rounded font-bold">
                                    RUPTURE DE STOCK
                                </span>
                            </div>` : ''
                        }
                    </div>
                    <div>
                        <p class="font-bold text-xs uppercase tracking-widest mb-1">
                            ${produit.marque}
                        </p>
                        <p class="text-gray-600 text-sm mb-2 line-clamp-2">
                            ${produit.nom}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="font-serif text-xl text-gold">
                                ${new Intl.NumberFormat('fr-FR').format(produit.prix)} €
                            </span>
                            ${produit.note_moyenne ? 
                                `<div class="flex items-center text-xs">
                                    <span class="text-gold mr-1">⭐</span>
                                    <span class="font-semibold">${parseFloat(produit.note_moyenne).toFixed(1)}</span>
                                    <span class="text-gray-400 ml-1">(${produit.nb_avis})</span>
                                </div>` : ''
                            }
                        </div>
                    </div>
                </a>
            </div>
        `).join('');
        
        // Afficher la pagination
        if (data.total_pages > 1) {
            let paginationHTML = '<div class="flex items-center space-x-2">';
            
            // Bouton précédent
            if (currentPage > 1) {
                paginationHTML += `
                    <button onclick="lancerRecherche(${currentPage - 1})" 
                            class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 transition">
                        Précédent
                    </button>
                `;
            }
            
            // Numéros de page
            for (let i = 1; i <= data.total_pages; i++) {
                if (i === 1 || i === data.total_pages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    paginationHTML += `
                        <button onclick="lancerRecherche(${i})" 
                                class="w-10 h-10 rounded ${i === currentPage ? 'bg-black text-white' : 'border border-gray-300 hover:bg-gray-50'} transition">
                            ${i}
                        </button>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHTML += '<span class="px-2">...</span>';
                }
            }
            
            // Bouton suivant
            if (currentPage < data.total_pages) {
                paginationHTML += `
                    <button onclick="lancerRecherche(${currentPage + 1})" 
                            class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 transition">
                        Suivant
                    </button>
                `;
            }
            
            paginationHTML += '</div>';
            pagination.innerHTML = paginationHTML;
        } else {
            pagination.innerHTML = '';
        }
        
        // Scroll vers le haut
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Toggle wishlist inline dans la recherche
    async function toggleWishlistInline(idProduit, button) {
        <?php if (isset($_SESSION['client_id'])): ?>
            try {
                button.disabled = true;
                const response = await fetch(`${baseUrl}/wishlist_toggle.php?id=${idProduit}`);
                const data = await response.json();
                
                if (data.success) {
                    const svg = button.querySelector('svg');
                    if (data.in_wishlist) {
                        svg.setAttribute('fill', 'currentColor');
                        svg.classList.add('text-red-500');
                        button.classList.add('bg-red-50');
                    } else {
                        svg.setAttribute('fill', 'none');
                        svg.classList.remove('text-red-500');
                        button.classList.remove('bg-red-50');
                    }
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Une erreur est survenue', 'error');
            } finally {
                button.disabled = false;
            }
        <?php else: ?>
            window.location.href = `${baseUrl}/connexion.php?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
        <?php endif; ?>
    }
    
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-24 right-6 z-50 px-6 py-4 rounded-lg shadow-lg transition-all ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white font-semibold`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Appliquer le filtre prix
    function appliquerFiltrePrix() {
        lancerRecherche(1);
    }

    // Réinitialiser les filtres
    function resetFiltres() {
        document.getElementById('searchInput').value = '';
        document.getElementById('prixMin').value = '';
        document.getElementById('prixMax').value = '';
        document.getElementById('noteMin').value = '';
        document.getElementById('enStock').checked = false;
        document.getElementById('triSelect').value = 'nouveautes';
        
        document.querySelectorAll('.filtre-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        lancerRecherche(1);
    }

    // Event listeners
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            lancerRecherche(1);
        }
    });

    document.getElementById('triSelect').addEventListener('change', function() {
        lancerRecherche(1);
    });

    document.getElementById('noteMin').addEventListener('change', function() {
        lancerRecherche(1);
    });

    document.getElementById('enStock').addEventListener('change', function() {
        lancerRecherche(1);
    });

    document.querySelectorAll('.filtre-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            lancerRecherche(1);
        });
    });

    // Lancer la recherche au chargement si paramètres dans l'URL
    window.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Restaurer les filtres depuis l'URL
        if (urlParams.has('categorie')) {
            urlParams.getAll('categorie[]').forEach(val => {
                const checkbox = document.querySelector(`input[name="categorie[]"][value="${val}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
        
        if (urlParams.has('sort')) {
            document.getElementById('triSelect').value = urlParams.get('sort');
        }
        
        // Lancer la recherche
        if (urlParams.toString()) {
            lancerRecherche(1);
        } else {
            // Afficher tous les produits par défaut
            lancerRecherche(1);
        }
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>