<?php
Namespace Livro\Widgets\Form;

use Livro\Control\Action;
use Livro\Control\ActionInterface;

/**
 * Representa um bot�o
 * @author Pablo Dall'Oglio
 */
class Button extends Field implements FormElementInterface
{
    private $action;
    private $label;
    private $formName;
    
    /**
     * Cria o bot�o com �cone e a��o
     */
    public static function create($name, $callback, $label, $image)
    {
        $button = new Button( $name );
        $button->setAction(new Action( $callback ), $label);
        $button->setImage( $image );
        return $button;
    }
    
    /**
     * Define a a��o do bot�o (fun��o a ser executada)
     * @param $action = a��o do bot�o
     * @param $label    = r�tulo do bot�o
     */
    public function setAction(ActionInterface $action, $label)
    {
        $this->action = $action;
        $this->label = $label;
    }
    
    /**
     * Define o nome do formul�rio para a a��o bot�o
     * @param $name = nome do formul�rio
     */
    public function setFormName($name)
    {
        $this->formName = $name;
    }
    
    /**
     * exibe o bot�o
     */
    public function show()
    {
        $url = $this->action->serialize();
        // define as propriedades do bot�o
        $this->tag->name    = $this->name;    // nome da TAG
        $this->tag->type    = 'button';       // tipo de input
        $this->tag->value   = $this->label;   // r�tulo do bot�o
        
        // define a a��o do bot�o
        $this->tag->onclick =	"document.{$this->formName}.action='{$url}'; ".
                                "document.{$this->formName}.submit()";
        // exibe o bot�o
        $this->tag->show();
    }
}
