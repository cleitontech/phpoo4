<?php
Namespace Livro\Widgets\Container;

/**
 * classe TableRow
 * repons�vel pela exibi��o de uma linha de uma tabela
 */
class TableRow extends Element
{
    /**
     * m�todo construtor
     * instancia uma nova linha
     */
    public function __construct()
    {
        parent::__construct('tr');
    }
    
    /**
     * m�todo addCell
     * agrega um novo objeto c�lula (TTableCell) � linha
     * @param $value = conte�do da c�lula
     */
    public function addCell($value)
    {
        // instancia objeto c�lula
        $cell = new TableCell($value);
        parent::add($cell);
        
        // retorna o objeto instanciado
        return $cell;
    }
}
