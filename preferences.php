<?php
$page_title = "Mes Préférences | DIAMON";
require_once __DIR__ . '/includes/header.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['client_id'])) {
    header('Location: ' . $base_url . '/connexion.php?redirect=preferences.php');
    exit();
}
?>

<main class="pt-32 pb-24">
    <div class="max-w-4xl mx-auto px-6">
        
        <div class="mb-12">
            <h1 class="text-4xl font-serif italic mb-2">Mes Préférences</h1>
            <p class="text-gray-600">Personnalisez votre expérience DIAMON</p>
        </div>

        <div class="bg-white rounded-lg shadow p-8">
            
            <!-- Apparence -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                    Apparence
                </h2>
                
                <div class="grid md:grid-cols-3 gap-4">
                    <!-- Mode Clair -->
                    <button onclick="setThemePreference('light')" 
                            id="lightThemeBtn"
                            class="theme-option p-6 border-2 rounded-lg hover:border-gold transition">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 mb-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                            </svg>
                            <h3 class="font-bold mb-1">Clair</h3>
                            <p class="text-sm text-gray-500">Thème lumineux</p>
                        </div>
                    </button>
                    
                    <!-- Mode Sombre -->
                    <button onclick="setThemePreference('dark')" 
                            id="darkThemeBtn"
                            class="theme-option p-6 border-2 rounded-lg hover:border-gold transition">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 mb-3 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <h3 class="font-bold mb-1">Sombre</h3>
                            <p class="text-sm text-gray-500">Thème sombre</p>
                        </div>
                    </button>
                    
                    <!-- Auto (Système) -->
                    <button onclick="setThemePreference('auto')" 
                            id="autoThemeBtn"
                            class="theme-option p-6 border-2 rounded-lg hover:border-gold transition">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="font-bold mb-1">Auto</h3>
                            <p class="text-sm text-gray-500">Selon le système</p>
                        </div>
                    </button>
                </div>
                
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>💡 Astuce :</strong> Le mode "Auto" s'adapte automatiquement aux préférences de votre système d'exploitation.
                    </p>
                </div>
            </div>

        </div>

    </div>
</main>

<script>
    function setThemePreference(preference) {
        // Sauvegarder la préférence
        if (preference === 'auto') {
            localStorage.removeItem('theme');
        } else {
            localStorage.setItem('theme', preference);
        }
        
        // Appliquer le thème
        if (preference === 'auto') {
            const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            applyTheme(systemPreference);
        } else {
            applyTheme(preference);
        }
        
        // Mettre à jour les boutons
        updateThemeButtons();
        
        // Animation
        document.querySelectorAll('.theme-option').forEach(btn => {
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                btn.style.transform = 'scale(1)';
            }, 150);
        });
    }
    
    function updateThemeButtons() {
        const savedTheme = localStorage.getItem('theme');
        
        document.querySelectorAll('.theme-option').forEach(btn => {
            btn.classList.remove('border-gold', 'bg-gold', 'bg-opacity-10');
        });
        
        if (!savedTheme) {
            document.getElementById('autoThemeBtn').classList.add('border-gold', 'bg-gold', 'bg-opacity-10');
        } else if (savedTheme === 'light') {
            document.getElementById('lightThemeBtn').classList.add('border-gold', 'bg-gold', 'bg-opacity-10');
        } else if (savedTheme === 'dark') {
            document.getElementById('darkThemeBtn').classList.add('border-gold', 'bg-gold', 'bg-opacity-10');
        }
    }
    
    // Mettre à jour les boutons au chargement
    document.addEventListener('DOMContentLoaded', updateThemeButtons);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>