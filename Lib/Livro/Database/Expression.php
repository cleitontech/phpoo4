<?php
Namespace Livro\Database;

/*
 * classe Expression
 * classe abstrata para gerenciar express�es
 */
abstract class Expression
{
    // operadores l�gicos
    const AND_OPERATOR = 'AND ';
    const OR_OPERATOR = 'OR ';
    
    // marca m�todo dump como obrigat�rio
    abstract public function dump();
}
