<footer class="bg-black text-white py-20">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12 border-b border-white/10 pb-16">
            <div class="col-span-1 md:col-span-1">
                <div class="text-2xl font-serif tracking-widest font-bold mb-6">DIAMON</div>
                <p class="text-gray-500 text-xs leading-relaxed tracking-wide">
                    La première plateforme éthique dédiée à l'échange et à la revente de mode de luxe. Certifiée par des experts.
                </p>
            </div>
            <div>
                <h5 class="text-[10px] uppercase font-bold tracking-[0.2em] mb-6 text-gold">Services</h5>
                <ul class="text-xs space-y-4 text-gray-400 font-light">
                    <li><a href="#" class="hover:text-white transition">Comment Vendre</a></li>
                    <li><a href="#" class="hover:text-white transition">Le Système de Grade</a></li>
                    <li><a href="#" class="hover:text-white transition">Conciergerie</a></li>
                    <li><a href="#" class="hover:text-white transition">Authentification</a></li>
                </ul>
            </div>
            <div>
                <h5 class="text-[10px] uppercase font-bold tracking-[0.2em] mb-6 text-gold">Informations</h5>
                <ul class="text-xs space-y-4 text-gray-400 font-light">
                    <li><a href="#" class="hover:text-white transition">Livraison Sécurisée</a></li>
                    <li><a href="#" class="hover:text-white transition">Conditions d'Échange</a></li>
                    <li><a href="#" class="hover:text-white transition">Mentions Légales</a></li>
                    <li><a href="<?= $base_url ?>/contact.php" class="hover:text-white transition">Contact</a></li>
                </ul>
            </div>
            <div>
                <h5 class="text-[10px] uppercase font-bold tracking-[0.2em] mb-6 text-gold">Newsletter</h5>
                <p class="text-[10px] text-gray-500 mb-4 tracking-widest uppercase">Soyez informé des nouvelles arrivées Grade A++</p>
                <form method="post" action="<?= $base_url ?>/newsletter.php" class="flex border-b border-white/30 pb-2">
                    <input type="email" name="email" placeholder="Email" class="bg-transparent text-xs w-full focus:outline-none" required>
                    <button type="submit" class="text-[10px] uppercase tracking-widest font-bold">OK</button>
                </form>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 mt-8 flex flex-col md:flex-row justify-between items-center text-[10px] text-gray-600 tracking-widest uppercase font-bold">
            <p>&copy; <?= date('Y') ?> DIAMON LUXE HOLDING</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="hover:text-gold transition">Instagram</a>
                <a href="#" class="hover:text-gold transition">LinkedIn</a>
                <a href="#" class="hover:text-gold transition">WeChat</a>
            </div>
        </div>
    </footer>

</body>
</html>