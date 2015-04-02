<?php
Namespace Livro\Widgets\Container;

/**
 * classe TableCell
 * repons�vel pela exibi��o de uma c�lula de uma tabela
 */
class TableCell extends Element
{
    /**
     * m�todo construtor
     * instancia uma nova c�lula
     * @param $value = conte�do da c�lula
     */
    public function __construct($value)
    {
        parent::__construct('td');
        parent::add($value);
    }
}
