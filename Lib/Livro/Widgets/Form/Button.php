<?php
Namespace Livro\Widgets\Form;

use Livro\Control\Action;

/* classe Button
 * respons�vel por exibir um bot�o
 */
class Button extends Field implements WidgetInterface
{
    private $action;
    private $label;
    private $formName;
    
    /**
     * Create a button with icon and action
     */
    public static function create($name, $callback, $label, $image)
    {
        $button = new Button( $name );
        $button->setAction(new Action( $callback ), $label);
        $button->setImage( $image );
        return $button;
    }
    
    /**
     * m�todo setAction
     * define a a��o do bot�o (fun��o a ser executada)
     * @param $action = a��o do bot�o
     * @param $label    = r�tulo do bot�o
     */
    public function setAction(ActionInterface $action, $label)
    {
        $this->action = $action;
        $this->label = $label;
    }
    
    /**
     * m�todo setFormName
     * define o nome do formul�rio para a a��o bot�o
     * @param $name = nome do formul�rio
     */
    public function setFormName($name)
    {
        $this->formName = $name;
        
    }
    
    /**
    * m�todo show()
    * exibe o bot�o
    */
    public function show()
    {
        $url = $this->action->serialize();
        // define as propriedades do bot�o
        $this->tag->name    = $this->name;    // nome da TAG
        $this->tag->type    = 'button';       // tipo de input
        $this->tag->value   = $this->label;   // r�tulo do bot�o
        // se o campo n�o � edit�vel
        if (!parent::getEditable())
        {
            $this->tag->disabled = "1";
            $this->tag->class = 'tfield_disabled'; // classe CSS
        }
        // define a a��o do bot�o
        $this->tag->onclick =	"document.{$this->formName}.action='{$url}'; ".
                                "document.{$this->formName}.submit()";
        // exibe o bot�o
        $this->tag->show();
    }
}
