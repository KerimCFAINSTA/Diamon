<?php
// Bouton wishlist réutilisable
// Paramètres : $id_produit, $show_text (optionnel)

$is_connected = isset($_SESSION['client_id']);
$in_wishlist = false;

if ($is_connected) {
    require_once __DIR__ . '/../models/Wishlist.php';
    $in_wishlist = Wishlist::estDansWishlist($_SESSION['client_id'], $id_produit);
}

$show_text = $show_text ?? false;
?>

<?php if ($is_connected): ?>
    <!-- Utilisateur connecté : bouton fonctionnel -->
    <button onclick="toggleWishlist(<?= $id_produit ?>, this)" 
            data-in-wishlist="<?= $in_wishlist ? 'true' : 'false' ?>"
            class="wishlist-btn flex items-center justify-center space-x-2 px-4 py-2 rounded transition <?= $in_wishlist ? 'bg-red-50 text-red-500 hover:bg-red-100' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>"
            title="<?= $in_wishlist ? 'Retirer de la liste' : 'Ajouter à la liste' ?>">
        <svg class="w-5 h-5" fill="<?= $in_wishlist ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
        </svg>
        <?php if ($show_text): ?>
            <span class="text-sm font-semibold">
                <?= $in_wishlist ? 'Dans mes favoris' : 'Ajouter aux favoris' ?>
            </span>
        <?php endif; ?>
    </button>
<?php else: ?>
    <!-- Utilisateur non connecté : redirection vers connexion -->
    <a href="<?= $base_url ?>/connexion.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
       class="flex items-center justify-center space-x-2 px-4 py-2 rounded bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
        </svg>
        <?php if ($show_text): ?>
            <span class="text-sm font-semibold">Ajouter aux favoris</span>
        <?php endif; ?>
    </a>
<?php endif; ?>

<script>
    async function toggleWishlist(idProduit, button) {
        const inWishlist = button.getAttribute('data-in-wishlist') === 'true';
        
        try {
            // Désactiver le bouton pendant la requête
            button.disabled = true;
            
            const response = await fetch(`<?= $base_url ?>/wishlist_toggle.php?id=${idProduit}`);
            const data = await response.json();
            
            if (data.success) {
                // Mettre à jour le bouton
                const newInWishlist = !inWishlist;
                button.setAttribute('data-in-wishlist', newInWishlist ? 'true' : 'false');
                button.title = newInWishlist ? 'Retirer de la liste' : 'Ajouter à la liste';
                
                // Mettre à jour les classes
                if (newInWishlist) {
                    button.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    button.classList.add('bg-red-50', 'text-red-500', 'hover:bg-red-100');
                } else {
                    button.classList.remove('bg-red-50', 'text-red-500', 'hover:bg-red-100');
                    button.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                }
                
                // Mettre à jour le SVG
                const svg = button.querySelector('svg');
                svg.setAttribute('fill', newInWishlist ? 'currentColor' : 'none');
                
                // Mettre à jour le texte si présent
                const textSpan = button.querySelector('span');
                if (textSpan) {
                    textSpan.textContent = newInWishlist ? 'Dans mes favoris' : 'Ajouter aux favoris';
                }
                
                // Mettre à jour le compteur dans le header
                const wishlistBadge = document.querySelector('a[href*="wishlist"] span');
                if (wishlistBadge) {
                    const currentCount = parseInt(wishlistBadge.textContent);
                    const newCount = newInWishlist ? currentCount + 1 : currentCount - 1;
                    
                    if (newCount > 0) {
                        wishlistBadge.textContent = newCount;
                        wishlistBadge.style.display = 'flex';
                    } else {
                        wishlistBadge.style.display = 'none';
                    }
                }
                
                // Notification
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
            
        } catch (error) {
            console.error('Erreur wishlist:', error);
            showNotification('Une erreur est survenue', 'error');
        } finally {
            button.disabled = false;
        }
    }
    
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-24 right-6 z-50 px-6 py-4 rounded-lg shadow-lg transition-all transform ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white font-semibold`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
</script>