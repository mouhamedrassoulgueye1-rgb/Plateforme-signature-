/* =====================================================
   Équipes 1-2 : pages espace/dut1.php et espace/parrain.php.
   Animation d'entrée du bandeau, une seule fois. Les liens sont écrits par le PHP avec url().
   ===================================================== */
document.documentElement.classList.add('js');
requestAnimationFrame(() => requestAnimationFrame(() => document.body.classList.add('pret')));
