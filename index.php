<?php 

require 'Entita.php';
require 'Articolo.php';

$articoloOBJ = new Articolo;
$articolo = $articoloOBJ->trova(' WHERE id = 1');

echo "<h1>Ho trovato l'articolo $articolo->id contente il titolo '$articolo->titolo'</h1>";
die;

