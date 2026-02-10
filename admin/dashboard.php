<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Analytics.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Récupérer toutes les données avec valeurs par défaut
$stats = Analytics::getStatistiquesGlobales();
if (!$stats) {
    $stats = [
        'ca_total' => 0,
        'nb_commandes' => 0,
        'nb_clients' => 0,
        'panier_moyen' => 0,
        'commandes_mois' => 0,
        'ca_mois' => 0
    ];
}

$ca_par_mois = Analytics::getCAParMois() ?: [];
$top_produits = Analytics::getTopProduits(5) ?: [];
$ventes_categorie = Analytics::getVentesParCategorie() ?: [];
$evolution_clients = Analytics::getEvolutionClients() ?: [];
$commandes_statut = Analytics::getCommandesParStatut() ?: [];
$dernieres_commandes = Analytics::getDernieresCommandes(10) ?: [];
$stock_faible = Analytics::getProduitsStockFaible() ?: [];

$base_url = '/diamon_luxe';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytics | DIAMON Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .bg-gold { background-color: #C5A059; }
        .text-gold { color: #C5A059; }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-card-2 {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card-3 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-card-4 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <nav class="bg-black text-white p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-serif">DIAMON - Admin</h1>
            <div class="flex space-x-6 text-sm">
                <a href="<?= $base_url ?>/admin/dashboard.php" class="text-gold font-bold">Dashboard</a>
                <a href="<?= $base_url ?>/admin/codes_promo.php" class="hover:text-gold">Codes Promo</a>
                <a href="<?= $base_url ?>/admin/avis.php" class="hover:text-gold">Avis</a>
                <a href="<?= $base_url ?>/admin/produits.php" class="hover:text-gold">Produits</a>
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
            <h1 class="text-4xl font-bold mb-2">Dashboard Analytics</h1>
            <p class="text-gray-600">Vue d'ensemble de votre activité DIAMON</p>
        </div>

        <!-- Statistiques Globales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            
            <!-- CA Total -->
            <div class="stat-card text-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm opacity-90">Chiffre d'Affaires Total</span>
                    <span class="text-2xl">💰</span>
                </div>
                <div class="text-3xl font-bold mb-1"><?= number_format($stats['ca_total'], 0, ',', ' ') ?> €</div>
                <div class="text-xs opacity-80">Ce mois : <?= number_format($stats['ca_mois'], 0, ',', ' ') ?> €</div>
            </div>

            <!-- Commandes -->
            <div class="stat-card-2 text-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm opacity-90">Commandes</span>
                    <span class="text-2xl">📦</span>
                </div>
                <div class="text-3xl font-bold mb-1"><?= $stats['nb_commandes'] ?></div>
                <div class="text-xs opacity-80">Ce mois : <?= $stats['commandes_mois'] ?></div>
            </div>

            <!-- Clients -->
            <div class="stat-card-3 text-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm opacity-90">Clients</span>
                    <span class="text-2xl">👥</span>
                </div>
                <div class="text-3xl font-bold mb-1"><?= $stats['nb_clients'] ?></div>
                <div class="text-xs opacity-80">Clients fidèles</div>
            </div>

            <!-- Panier Moyen -->
            <div class="stat-card-4 text-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm opacity-90">Panier Moyen</span>
                    <span class="text-2xl">🛒</span>
                </div>
                <div class="text-3xl font-bold mb-1"><?= number_format($stats['panier_moyen'], 0, ',', ' ') ?> €</div>
                <div class="text-xs opacity-80">Par commande</div>
            </div>

        </div>

        <!-- Graphiques -->
        <!-- Graphiques -->
        <div class="grid lg:grid-cols-2 gap-8 mb-12">
            
            <!-- CA par Mois -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6 flex items-center">
                    <span class="mr-2">📈</span> Chiffre d'Affaires par Mois
                </h3>
                <div style="height: 250px;">
                    <canvas id="chartCAMois"></canvas>
                </div>
            </div>

            <!-- Ventes par Catégorie -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6 flex items-center">
                    <span class="mr-2">🎯</span> Ventes par Catégorie
                </h3>
                <div style="height: 250px;">
                    <canvas id="chartCategories"></canvas>
                </div>
            </div>

        </div>

        <div class="grid lg:grid-cols-2 gap-8 mb-12">
            
            <!-- Évolution Clients -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6 flex items-center">
                    <span class="mr-2">👥</span> Nouveaux Clients par Mois
                </h3>
                <div style="height: 250px;">
                    <canvas id="chartClients"></canvas>
                </div>
            </div>

            <!-- Commandes par Statut -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-6 flex items-center">
                    <span class="mr-2">📊</span> Répartition des Commandes
                </h3>
                <div style="height: 250px;">
                    <canvas id="chartStatuts"></canvas>
                </div>
            </div>

        </div>

        <!-- Top Produits & Dernières Commandes -->
        <div class="grid lg:grid-cols-2 gap-8 mb-12">
            
            <!-- Top 5 Produits -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="text-xl font-bold flex items-center">
                        <span class="mr-2">🏆</span> Top 5 Produits
                    </h3>
                </div>
                <div class="divide-y">
                    <?php foreach ($top_produits as $produit): ?>
                        <div class="p-4 hover:bg-gray-50 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                    <?php if ($produit['image_principale']): ?>
                                        <img src="<?= $base_url ?>/public/images/produits/<?= $produit['image_principale'] ?>" 
                                             class="w-full h-full object-cover"
                                             alt="<?= htmlspecialchars($produit['nom']) ?>">
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm"><?= htmlspecialchars($produit['marque']) ?></p>
                                    <p class="text-xs text-gray-600"><?= htmlspecialchars($produit['nom']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <span class="font-semibold text-blue-600"><?= $produit['total_vendu'] ?></span> vendus
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gold"><?= number_format($produit['ca_genere'], 0, ',', ' ') ?> €</p>
                                <p class="text-xs text-gray-500">CA généré</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Dernières Commandes -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="text-xl font-bold flex items-center">
                        <span class="mr-2">📋</span> Dernières Commandes
                    </h3>
                </div>
                <div class="divide-y max-h-96 overflow-y-auto">
                    <?php foreach ($dernieres_commandes as $cmd): ?>
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-semibold text-sm"><?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?></p>
                                    <p class="text-xs text-gray-600"><?= htmlspecialchars($cmd['numero_commande']) ?></p>
                                </div>
                                <span class="text-sm font-bold text-gold"><?= number_format($cmd['montant_total'], 0, ',', ' ') ?> €</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></span>
                                <?php
                                $statut_colors = [
                                    'en attente' => 'bg-yellow-100 text-yellow-800',
                                    'payée' => 'bg-green-100 text-green-800',
                                    'expédiée' => 'bg-blue-100 text-blue-800',
                                    'livrée' => 'bg-gray-100 text-gray-800',
                                    'annulée' => 'bg-red-100 text-red-800'
                                ];
                                $color = $statut_colors[$cmd['statut']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="<?= $color ?> px-2 py-1 text-xs font-semibold rounded">
                                    <?= htmlspecialchars($cmd['statut']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Alertes Stock Faible -->
        <?php if (!empty($stock_faible)): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <span class="text-2xl mr-3">⚠️</span>
                    <h3 class="text-xl font-bold text-yellow-800">Alertes Stock Faible</h3>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($stock_faible as $produit): ?>
                        <div class="bg-white rounded p-4 flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                <?php if ($produit['image_principale']): ?>
                                    <img src="<?= $base_url ?>/public/images/produits/<?= $produit['image_principale'] ?>" 
                                         class="w-full h-full object-cover"
                                         alt="<?= htmlspecialchars($produit['nom']) ?>">
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm"><?= htmlspecialchars($produit['marque']) ?></p>
                                <p class="text-xs text-gray-600"><?= htmlspecialchars($produit['nom']) ?></p>
                                <p class="text-xs font-bold <?= $produit['stock'] == 0 ? 'text-red-600' : 'text-yellow-600' ?> mt-1">
                                    Stock : <?= $produit['stock'] ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <!-- Export Rapide -->
        <div class="mt-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Export Rapide des Données</h2>
                    <p class="opacity-90">Exportez vos données en un clic pour analyse</p>
                </div>
                <a href="<?= $base_url ?>/admin/exports.php" 
                   class="px-8 py-4 bg-white text-blue-600 rounded-lg font-bold hover:bg-gray-100 transition">
                    📥 Accéder aux Exports
                </a>
            </div>
        </div>

    </div>

    <script>
        // Données PHP vers JavaScript
        const caParMois = <?= json_encode($ca_par_mois) ?>;
        const ventesCategorie = <?= json_encode($ventes_categorie) ?>;
        const evolutionClients = <?= json_encode($evolution_clients) ?>;
        const commandesStatut = <?= json_encode($commandes_statut) ?>;

        // Configuration Chart.js globale
        Chart.defaults.font.family = 'Montserrat';
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
        
        // Graphique CA par Mois
        new Chart(document.getElementById('chartCAMois'), {
            type: 'line',
            data: {
                labels: caParMois.map(d => d.nom_mois || 'N/A'),
                datasets: [{
                    label: 'Chiffre d\'Affaires (€)',
                    data: caParMois.map(d => parseFloat(d.ca) || 0),
                    borderColor: '#C5A059',
                    backgroundColor: 'rgba(197, 160, 89, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                plugins: {
                    legend: { 
                        display: false 
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y.toLocaleString('fr-FR') + ' €';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('fr-FR') + ' €';
                            }
                        }
                    }
                }
            }
        });

        // Graphique Ventes par Catégorie
        if (ventesCategorie.length > 0) {
            new Chart(document.getElementById('chartCategories'), {
                type: 'doughnut',
                data: {
                    labels: ventesCategorie.map(d => d.categorie),
                    datasets: [{
                        data: ventesCategorie.map(d => parseFloat(d.ca) || 0),
                        backgroundColor: [
                            '#667eea',
                            '#f093fb',
                            '#4facfe',
                            '#43e97b',
                            '#C5A059',
                            '#fa709a'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed.toLocaleString('fr-FR') + ' €';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('chartCategories').parentElement.innerHTML = '<p class="text-gray-400 text-center py-20">Aucune donnée disponible</p>';
        }

        // Graphique Évolution Clients
        new Chart(document.getElementById('chartClients'), {
            type: 'bar',
            data: {
                labels: evolutionClients.map(d => d.nom_mois || 'N/A'),
                datasets: [{
                    label: 'Nouveaux Clients',
                    data: evolutionClients.map(d => parseInt(d.nb_nouveaux_clients) || 0),
                    backgroundColor: 'rgba(79, 172, 254, 0.8)',
                    borderColor: '#4facfe',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                plugins: {
                    legend: { 
                        display: false 
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Graphique Commandes par Statut
        if (commandesStatut.length > 0) {
            new Chart(document.getElementById('chartStatuts'), {
                type: 'pie',
                data: {
                    labels: commandesStatut.map(d => d.statut),
                    datasets: [{
                        data: commandesStatut.map(d => parseInt(d.nb_commandes) || 0),
                        backgroundColor: [
                            '#ffc107',
                            '#4caf50',
                            '#2196f3',
                            '#9e9e9e',
                            '#f44336'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('chartStatuts').parentElement.innerHTML = '<p class="text-gray-400 text-center py-20">Aucune donnée disponible</p>';
        }
    </script>

</body>
</html>