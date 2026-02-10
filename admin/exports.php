<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Export.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$base_url = '/diamon_luxe';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export de Données | DIAMON Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .bg-gold { background-color: #C5A059; }
        .text-gold { color: #C5A059; }
    </style>
</head>
<body class="bg-gray-100">
    
    <nav class="bg-black text-white p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-serif">DIAMON - Admin</h1>
            <div class="flex space-x-6 text-sm">
                <a href="<?= $base_url ?>/admin/dashboard.php" class="hover:text-gold">Dashboard</a>
                <a href="<?= $base_url ?>/admin/produits.php" class="hover:text-gold">Produits</a>
                <a href="<?= $base_url ?>/admin/codes_promo.php" class="hover:text-gold">Codes Promo</a>
                <a href="<?= $base_url ?>/admin/avis.php" class="hover:text-gold">Avis</a>
                <a href="<?= $base_url ?>/admin/exports.php" class="text-gold font-bold"> Exports</a>
                <a href="<?= $base_url ?>/admin/commandes.php" class="hover:text-gold">Commandes</a>
                <a href="<?= $base_url ?>/admin/demandes_vente.php" class="hover:text-gold">Ventes</a>
                <a href="<?= $base_url ?>/admin/demandes_echange.php" class="hover:text-gold">Échanges</a>
                <a href="<?= $base_url ?>/index.php" class="hover:text-gold">Site</a>
                <a href="<?= $base_url ?>/admin/logout.php" class="hover:text-red-400">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-12">
        
        <div class="mb-12">
            <h1 class="text-4xl font-bold mb-2">Export de Données</h1>
            <p class="text-gray-600">Exportez vos données en CSV pour analyse et reporting</p>
        </div>

        <?php if (isset($_SESSION['export_error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded mb-8">
                ✗ <?= htmlspecialchars($_SESSION['export_error']) ?>
            </div>
            <?php unset($_SESSION['export_error']); ?>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-8">
            
            <!-- Export Produits -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-1">Produits</h2>
                            <p class="text-sm opacity-90">Catalogue complet</p>
                        </div>
                        <span class="text-5xl"></span>
                    </div>
                </div>
                <form action="export_process.php" method="post" class="p-6 space-y-4">
                    <input type="hidden" name="type" value="produits">
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Catégorie</label>
                        <select name="categorie" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            <option value="">Toutes les catégories</option>
                            <?php
                            $categories = $pdo->query("SELECT * FROM categorie ORDER BY nom")->fetchAll();
                            foreach ($categories as $cat):
                            ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Disponibilité</label>
                        <select name="disponible" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            <option value="">Tous</option>
                            <option value="1">Disponibles uniquement</option>
                            <option value="0">Indisponibles uniquement</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Date début</label>
                            <input type="date" name="date_debut" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Date fin</label>
                            <input type="date" name="date_fin" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-black text-white py-3 rounded font-bold uppercase tracking-widest hover:bg-gold transition">
                        Exporter les Produits
                    </button>
                </form>
            </div>

            <!-- Export Commandes -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-500 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-1">Commandes</h2>
                            <p class="text-sm opacity-90">Historique des ventes</p>
                        </div>
                        <span class="text-5xl"></span>
                    </div>
                </div>
                <form action="export_process.php" method="post" class="p-6 space-y-4">
                    <input type="hidden" name="type" value="commandes">
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Statut</label>
                        <select name="statut" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            <option value="">Tous les statuts</option>
                            <option value="en attente">En attente</option>
                            <option value="payée">Payée</option>
                            <option value="expédiée">Expédiée</option>
                            <option value="livrée">Livrée</option>
                            <option value="annulée">Annulée</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Date début</label>
                            <input type="date" name="date_debut" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Date fin</label>
                            <input type="date" name="date_fin" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>
                    </div><br><br><br>

                    
                    <button type="submit" class="w-full bg-black text-white py-3 rounded font-bold uppercase tracking-widest hover:bg-gold transition">
                        Exporter les Commandes
                    </button>
                </form>
            </div>

            <!-- Export Clients -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-teal-500 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-1">Clients</h2>
                            <p class="text-sm opacity-90">Base de données clients</p>
                        </div>
                        <span class="text-5xl"></span>
                    </div>
                </div>
                <form action="export_process.php" method="post" class="p-6 space-y-4">
                    <input type="hidden" name="type" value="clients">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Inscrit après le</label>
                            <input type="date" name="date_debut" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Inscrit avant le</label>
                            <input type="date" name="date_fin" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 text-sm text-yellow-800">
                        <strong>⚠️ Attention :</strong> Ces données sont sensibles (RGPD). Utilisez-les de manière responsable.
                    </div>
                    <br><br><br>
                    <button type="submit" class="w-full bg-black text-white py-3 rounded font-bold uppercase tracking-widest hover:bg-gold transition">
                        Exporter les Clients
                    </button>
                </form>
            </div>

            <!-- Export Avis -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-1">Avis Clients</h2>
                            <p class="text-sm opacity-90">Feedback et notes</p>
                        </div>
                        <span class="text-5xl"></span>
                    </div>
                </div>
                <form action="export_process.php" method="post" class="p-6 space-y-4">
                    <input type="hidden" name="type" value="avis">
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Statut</label>
                        <select name="statut" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            <option value="">Tous</option>
                            <option value="approuve">Approuvés uniquement</option>
                            <option value="en_attente">En attente</option>
                            <option value="rejete">Rejetés</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Note minimum</label>
                        <select name="note_min" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            <option value="">Toutes les notes</option>
                            <option value="5">5 étoiles</option>
                            <option value="4">4 étoiles et plus</option>
                            <option value="3">3 étoiles et plus</option>
                            <option value="2">2 étoiles et plus</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Date début</label>
                            <input type="date" name="date_debut" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2">Date fin</label>
                            <input type="date" name="date_fin" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-black text-white py-3 rounded font-bold uppercase tracking-widest hover:bg-gold transition">
                        Exporter les Avis
                    </button>
                </form>
            </div>

        </div>

        <!-- Exports Avancés -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6">Exports Avancés</h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                
                <!-- Statistiques Ventes -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold mb-1">Statistiques Ventes</h3>
                                <p class="text-sm opacity-90">Rapport détaillé par jour</p>
                            </div>
                            <span class="text-5xl"></span>
                        </div>
                    </div>
                    <form action="export_stats.php" method="post" class="p-6 space-y-4">
                        <input type="hidden" name="type" value="stats_ventes">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold mb-2">Date début *</label>
                                <input type="date" 
                                       name="date_debut" 
                                       required
                                       value="<?= date('Y-m-01') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Date fin *</label>
                                <input type="date" 
                                       name="date_fin" 
                                       required
                                       value="<?= date('Y-m-d') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 text-sm text-blue-800">
                            <strong>Inclut :</strong> CA journalier, nb commandes, panier moyen, clients uniques
                        </div>
                        
                        <button type="submit" class="w-full bg-black text-white py-3 rounded font-bold uppercase tracking-widest hover:bg-gold transition">
                            Exporter les Statistiques
                        </button>
                    </form>
                </div>

                <!-- Top Produits -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold mb-1">Top Produits</h3>
                                <p class="text-sm opacity-90">Meilleures ventes</p>
                            </div>
                            <span class="text-5xl"></span>
                        </div>
                    </div>
                    <form action="export_stats.php" method="post" class="p-6 space-y-4">
                        <input type="hidden" name="type" value="top_produits">
                        
                        <div>
                            <label class="block text-sm font-semibold mb-2">Nombre de produits</label>
                            <select name="limit" class="w-full px-4 py-2 border border-gray-300 rounded focus:border-gold focus:outline-none">
                                <option value="10">Top 10</option>
                                <option value="20" selected>Top 20</option>
                                <option value="50">Top 50</option>
                                <option value="100">Top 100</option>
                            </select>
                        </div>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 text-sm text-blue-800">
                            <strong>Inclut :</strong> Quantité vendue, CA généré, nb de commandes
                        </div>
                        
                        <button type="submit" class="w-full bg-black text-white py-3 rounded font-bold uppercase tracking-widest hover:bg-gold transition">
                             Exporter le Top Produits
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <!-- Info -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded mt-8">
            <h3 class="font-bold mb-2 flex items-center">
                <span class="mr-2"></span> À propos des exports
            </h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Les fichiers sont générés au format CSV (compatible Excel)</li>
                <li>• Les exports respectent les filtres sélectionnés</li>
                <li>• Le séparateur utilisé est le point-virgule (;)</li>
                <li>• L'encodage est UTF-8 avec BOM pour Excel</li>
            </ul>
        </div>

    </div>

</body>
</html>