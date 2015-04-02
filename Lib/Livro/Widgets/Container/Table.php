<?php
Namespace Livro\Widgets\Container;

/**
 * classe Table
 * respons�vel pela exibi��o de tabelas
 */
class Table extends Element
{
    /**
     * m�todo construtor
     * instancia uma nova tabela
     */
    public function __construct()
    {
        parent::__construct('table');
    }
    
    /**
     * m�todo addRow
     * agrega um novo objeto linha (TableRow) na tabela
     */
    public function addRow()
    {
        // instancia objeto linha
        $row = new TableRow;
        
        // armazena no array de linhas
        parent::add($row);
        return $row;
    }
}
