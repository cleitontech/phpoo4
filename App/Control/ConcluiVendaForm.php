<?php
use Livro\Control\Page;
use Livro\Control\Action;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Container\Table;
use Livro\Widgets\Container\VBox;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Form\Label;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Text;
use Livro\Widgets\Form\Button;
use Livro\Database\Transaction;
use Livro\Database\Repository;
use Livro\Database\Criteria;
use Livro\Session\Session;
use Livro\Validation\RequiredValidator;

use Bootstrap\Wrapper\FormWrapper;
use Bootstrap\Widgets\Panel;

/**
 * Formulário de conclusão de venda
 */
class ConcluiVendaForm extends Page
{
    private $form;
    
    /**
     * método construtor
     */
    function __construct()
    {
        parent::__construct();
        
        // instancia nova seção
        new Session;
        
        $this->form = new FormWrapper(new Form('form_conclui_venda'));
        
        // cria os campos do formulário
        $cliente      = new Entry('id_cliente');
        $valor_venda  = new Entry('valor_venda');
        $desconto     = new Entry('desconto');
        $acrescimos   = new Entry('acrescimos');
        $valor_final  = new Entry('valor_final');
        $parcelas     = new Combo('parcelas');
        $obs          = new Text('obs');
        
        $parcelas->addItems(array(1=>'Uma', 2=>'Duas', 3=>'Três'));
        $parcelas->setValue(1);

        $cliente->addValidation('Cliente', new RequiredValidator);
        // define alguns atributos para os campos do formulário
        $desconto->onBlur = "$('[name=valor_final]').val( Number($('[name=valor_venda]').val()) + Number($('[name=acrescimos]').val()) - Number($('[name=desconto]').val()) );";
        $acrescimos->onBlur = $desconto->onBlur;
        
        $valor_venda->setEditable(FALSE);
        $valor_final->setEditable(FALSE);
        
        $this->form->addField('Cliente', $cliente,   200);
        $this->form->addField('Valor', $valor_venda, 200);
        $this->form->addField('Desconto', $desconto, 200);
        $this->form->addField('Acréscimos', $acrescimos, 200);
        $this->form->addField('Final', $valor_final, 200);
        $this->form->addField('Parcelas', $parcelas, 200);
        $this->form->addField('Obs', $obs, 200);
        $this->form->addAction('Salvar', new Action(array($this, 'onGravaVenda')));
        
        $panel = new Panel('Conclui venda');
        $panel->add($this->form);
        
        parent::add($panel);
    }
    
    /**
     * Carrega formulário de conclusão
     */
    function onLoad($param)
    {
        $total = 0;
        $itens = Session::getValue('list');
        if ($itens)
        {
            Transaction::open('livro');
            // percorre os itens
            foreach ($itens as $item)
            {
                $total += $item->produto->preco_venda * $item->quantidade;
            }
            Transaction::close();
        }
        
        $data = new StdClass;
        $data->valor_venda = $total;
        $data->valor_final = $total;
        $this->form->setData($data);
    }
    
    /**
     * Grava venda
     */
    function onGravaVenda()
    {
        try 
        {
            // inicia transação com o banco 'livro'
            Transaction::open('livro');
            
            $this->form->validate();
            $dados = $this->form->getData();
            
            $cliente = new Pessoa($dados->id_cliente);
            if ($cliente->totalDebitos() > 0)
            {
                throw new Exception('Débitos impedem esta operação');
            }
            
            $venda = new Venda;
            $venda->cliente     = $cliente;
            $venda->data_venda  = date('Y-m-d');
            $venda->valor_venda = $dados->valor_venda;
            $venda->desconto    = $dados->desconto;
            $venda->acrescimos  = $dados->acrescimos;
            $venda->valor_final = $dados->valor_final;
            $venda->obs         = $dados->obs;
    
            // lê a variável $list da seção
            $itens = Session::getValue('list');
            if ($itens)
            {
                // percorre os itens
                foreach ($itens as $item)
                {
                    // adiciona o item na venda
                    $venda->addItem($item);
                }
            }
            
            $venda->store(); // armazena venda no banco de dados
            
            // gera o financeiro
            Conta::geraParcelas($dados->id_cliente, 2, $dados->valor_final, $dados->parcelas);
            
            Transaction::close(); // finaliza a transação
            Session::setValue('list', array()); // limpa lista de itens da seção
    
            // exibe mensagem de sucesso
            new Message('info', 'Venda registrada com sucesso');
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }
}
