<?php
use Livro\Database\Record;

class Pessoa extends Record
{
    const TABLENAME = 'pessoa';
    private $cidade;
    
    /**
     * Executado sempre se for acessada a propriedade "nome_cidade"
     */
    function get_nome_cidade()
    {
        if (empty($this->cidade))
            $this->cidade = new Cidade($this->id_cidade);
        
        return $this->cidade->nome;
    }
}