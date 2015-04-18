<?php
use Livro\Control\Page;
use Livro\Control\Action;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Container\VBox;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Datagrid\DatagridAction;
use Livro\Widgets\Form\Label;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Button;
use Livro\Database\Transaction;
use Livro\Database\Repository;
use Livro\Database\Criteria;
use Livro\Session\Session;

use Bootstrap\Wrapper\DatagridWrapper;
use Bootstrap\Wrapper\FormWrapper;
use Bootstrap\Widgets\Panel;

/**
 * Página de vendas
 */
class VendasForm extends Page
{
    private $form;
    private $datagrid;
    private $loaded;

    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        // instancia nova seção
        new Session;

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_vendas'));

        // cria os campos do formulário
        $codigo      = new Entry('id_produto');
        $quantidade  = new Entry('quantidade');
        
        $this->form->addField('Código', $codigo, 100);
        $this->form->addField('Quantidade', $quantidade, 200);
        $this->form->addAction('Adicionar', new Action(array($this, 'onAdiciona')));
        $this->form->addAction('Terminar', new Action(array(new ConcluiVendaForm, 'onLoad')));
        
        // instancia objeto DataGrid
        $this->datagrid = new DatagridWrapper(new DataGrid);

        // instancia as colunas da DataGrid
        $codigo    = new DataGridColumn('id_produto', 'Código', 'right', 50);
        $descricao = new DataGridColumn('descricao',   'Descrição','left', 200);
        $quantidade= new DataGridColumn('quantidade',  'Qtde',      'right', 40);
        $preco     = new DataGridColumn('preco',       'Preço',    'right', 70);

        // define um transformador para a coluna preço
        $preco->setTransformer(array($this, 'formata_money'));

        // adiciona as colunas à DataGrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($descricao);
        $this->datagrid->addColumn($quantidade);
        $this->datagrid->addColumn($preco);

        // cria uma ação para a datagrid
        $action = new DataGridAction(array($this, 'onDelete'));
        $action->setLabel('Deletar');
        $action->setImage('ico_delete.png');
        $action->setField('id_produto');

        // adiciona a ação à DataGrid
        $this->datagrid->addAction($action);

        // cria o modelo da DataGrid, montando sua estrutura
        $this->datagrid->createModel();
        
        $panel1 = new Panel('Vendas');
        $panel1->add($this->form);
        
        $panel2 = new Panel();
        $panel2->add($this->datagrid);
        
        // monta a página através de uma caixa
        $box = new VBox;
        $box->style = 'display:block';
        $box->add($panel1);
        $box->add($panel2);
        
        parent::add($box);
    }
    
    /**
     * Adiciona item
     */
    function onAdiciona()
    {
        try {
            // obtém os dados do formulário
            $item = $this->form->getData('ItemVenda');
            Transaction::open('livro');
            $item->preco = $item->get_produto()->preco_venda;
            Transaction::close('livro');
            $list = Session::getValue('list'); // lê variável $list da seção
            $list[$item->id_produto]= $item;   // acrescenta produto na variável $list
            Session::setValue('list', $list);  // grava variável $list de volta à seção
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
        
        // recarrega a listagem
        $this->onReload();
    }

    /**
     * Exclui item
     */
    function onDelete($param)
    {
        // lê variável $list da seção
        $list = Session::getValue('list');

        // exclui a posição que armazena o produto de código $key
        unset($list[$param['key']]);

        // grava variável $list de volta à seção
        Session::setValue('list', $list);

        // recarrega a listagem
        $this->onReload();
    }

    /**
     * Carrega datagrid com objetos
     */
    function onReload()
    {
        // obtém a variável de seção $list
        $list = Session::getValue('list');

        // limpa a datagrid
        $this->datagrid->clear();
        if ($list)
        {
            // inicia transação com o BD
            Transaction::open('livro');
            
            foreach ($list as $item)
            {
                // adiciona cada objeto $item na datagrid
                $this->datagrid->addItem($item);
            }
            // fecha transação
            Transaction::close();
        }
        $this->loaded = true;
    }
    
    /**
     * Formata valor monetário
     */
    function formata_money($valor)
    {
        return number_format($valor, 2, ',', '.');
    }
    
    /**
     * Exibe a página
     */
    function show()
    {
        if (!$this->loaded)
        {
            $this->onReload();
        }
        parent::show();
    }
}
