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

<script>
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
</script>