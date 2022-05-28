<?php 
class Articolo extends Entita {

    protected $tabella = 'articolo';

    public $id;
    public $titolo;
    public $corpo = 'Corpo di prova';
    public $cod_autore = 1;
    public $data = '';
    public $visualizzazioni = 0;
    public $finito = 0;
}

