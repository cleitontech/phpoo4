<?php
Namespace Livro\Widgets\Form;

use Livro\Widgets\Base\Element;

/**
 * Representa um formulário
 * @author Pablo Dall'Oglio
 */
class Form
{
    protected $fields;      // array de objetos contidos pelo form
    private   $name;        // nome do formulário
    
    /**
     * Instancia o formulário
     * @param $name = nome do formulário
     */
    public function __construct($name = 'my_form')
    {
        $this->setName($name);
    }
    
    /**
     * Define o nome do formulário
     * @param $name      = nome do formulário
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Define se o formulário poderá ser editado
     * @param $bool = TRUE ou FALSE
     */
    public function setEditable($bool)
    {
        if ($this->fields)
        {
            foreach ($this->fields as $object)
            {
                $object->setEditable($bool);
            }
        }
    }
    
    /**
     * Define quais são os campos do formulário
     * @param $fields = array de objetos TField
     */
    public function setFields($fields)
    {
        foreach ($fields as $field)
        {
            if ($field instanceof Field)
            {
                $name = $field->getName();
                $this->fields[$name] = $field;
                if ($field instanceof Button)
                {
                    $field->setFormName($this->name);
                }
            }
        }
    }
    
    /**
     * Retorna um campo do formulário por seu nome
     * @param $name      = nome do campo
     */
    public function getField($name)
    {
        return $this->fields[$name];
    }
    
    /**
     * Atribui dados aos campos do formulário
     * @param $object = objeto com dados
     */
    public function setData($object)
    {
        foreach ($this->fields as $name => $field)
        {
            if ($name) // labels não possuem nome
            {
                $field->setValue($object->$name);
            }
        }
    }
    
    /**
     * Retorna os dados do formulário em forma de objeto
     */
    public function getData($class = 'StdClass')
    {
        $object = new $class;
        foreach ($this->fields as $key => $fieldObject)
        {
            $val = isset($_POST[$key])? $_POST[$key] : '';
            if (get_class($this->fields[$key]) == 'TCombo')
            {
                if ($val !== '0')
                {
                    $object->$key = $val;
                }
            }
            else if (!$fieldObject instanceof Button)
            {
                $object->$key = $val;
            }
        }
        // percorre os arquivos de upload
        foreach ($_FILES as $key => $content)
        {
            $object->$key = $content['tmp_name'];
        }
        return $object;
    }
    
    /**
     * Adiciona um objeto no formulário
     * @param $object = objeto a ser adicionado
     */
    public function add($object)
    {
        $this->child = $object;
    }
    
    /**
     * Valida o formulário
     */
    public function validate()
    {
        $this->setData($this->getData());
        foreach ($this->fields as $fieldObject)
        {
            $fieldObject->validate();
        }
    }
    
    /**
     * Exibe o formulário na tela
     */
    public function show()
    {
        // instancia TAG de formulário
        $tag = new Element('form');
        $tag->enctype = "multipart/form-data";
        $tag->name = $this->name; // nome do formulário
        $tag->method = 'post';    // método de transferência
        
        // adiciona o objeto filho ao formulário
        $tag->add($this->child);
        
        // exibe o formulário
        $tag->show();
    }
}
