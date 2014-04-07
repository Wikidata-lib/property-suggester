<?php
// Seit PHP 4.1.0 verfügbar
echo "lalqlq";
echo $_POST['entity-choser'];
echo $_REQUEST['entity-choser'];

echo $p_entity-choser;

// Ab PHP 6.0.0 nicht mehr verfügbar. Ab PHP 5.0.0 können diese langen
// vordefinierten Variablen mit der Anweisung register_long_arrays
// deaktiviert werden.

echo $HTTP_POST_VARS['entity-choser'];
$out = $this->getContext()->getOutput();
$out->addHTML("Asuagbeseite");
// Verfügbar, falls die PHP-Anweisung register_globals = on. Ab
// PHP 4.2.0 ist der standardmäßige Wert von register_globals = off.
// Es ist nicht empfehlenswert, diese Methode zu verwenden, bzw. sich
// darauf zu verlassen.

echo $entity-choser;
?>