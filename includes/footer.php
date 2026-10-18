<?php
/**
 * Pied de page commun. Scripts propres à la page : $scripts_page = ['assets/js/connexion.js'];
 */
?>
</main>
<footer class="pied">
    <svg class="pied-circuit pied-circuit-gauche" aria-hidden="true"><use href="#circuit"/></svg>
    <svg class="pied-circuit pied-circuit-droite" aria-hidden="true"><use href="#circuit"/></svg>
    <svg class="pied-logo" aria-hidden="true"><use href="#logo"/></svg>
    <div class="pied-titre"><b>SITE SIGNATURE <span>2027</span></b></div>
    <p class="pied-slogan">« Un lien aujourd’hui, une force demain ! »</p>
    <div class="pied-meta">ESP Dakar · Département Informatique</div>
</footer>
<script src="<?= e(url('assets/js/main.js')) ?>"></script>
<?php foreach ($scripts_page ?? [] as $script): ?>
<script src="<?= e(url($script)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
