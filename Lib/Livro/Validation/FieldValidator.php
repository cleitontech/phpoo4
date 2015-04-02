<?php
/**
 * Classe abstrata para valida��o
 */
abstract class FieldValidator
{
    /**
     * Valida um valor
     * @param $label Nome do campo
     * @param $value Valor a ser validato
     * @param $parameters Par�metros adicionais de valida��o
     */
    abstract public function validate($label, $value, $parameters = NULL);
}
?>