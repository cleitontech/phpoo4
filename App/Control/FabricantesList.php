<?php
use Livro\Control\Page;
use Livro\Control\Action;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Label;
use Livro\Widgets\Form\Button;
use Livro\Widgets\Container\Table;
use Livro\Widgets\Container\VBox;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Datagrid\DatagridAction;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Dialog\Question;
use Livro\Database\Transaction;
use Livro\Database\Repository;
use Livro\Database\Criteria;

use Livro\Traits\DeleteTrait;
use Livro\Traits\ReloadTrait;
use Livro\Traits\SaveTrait;

use Bootstrap\Wrapper\DatagridWrapper;
use Bootstrap\Wrapper\FormWrapper;
use Bootstrap\Widgets\Panel;

/*
 * classe FabricantesList
 * Cadastro de Fabricantes
 * Contém o formuláro e a listagem
 */
class FabricantesList extends Page
{
    private $form;      // formulário de cadastro
    private $datagrid;  // listagem
    private $loaded;
    
    use DeleteTrait;
    use ReloadTrait {
        onReload as onReloadTrait;
    }
    use SaveTrait {
        onSave as onSaveTrait;
    }
    
    
    /*
     * método construtor
     * Cria a página, o formulário e a listagem
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->activeRecord = 'Fabricante';
        $this->connection   = 'livro';
        
        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_fabricantes'));
        
        // cria os campos do formulário
        $codigo = new Entry('id');
        $nome   = new Entry('nome');
        $site   = new Entry('site');
        $codigo->setEditable(FALSE);
        
        $this->form->addField('Código', $codigo, 200);
        $this->form->addField('Nome',   $nome, 200);
        $this->form->addField('Site',   $site, 200);
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        $this->form->addAction('Limpar', new Action(array($this, 'onEdit')));
        
        // define os tamanhos
        $codigo->setSize(40);
        $site->setSize(200);
        
        // instancia objeto DataGrid
        $this->datagrid = new DatagridWrapper(new DataGrid);
        
        // instancia as colunas da DataGrid
        $codigo   = new DataGridColumn('id',       'Código',  'right',  50);
        $nome     = new DataGridColumn('nome',     'Nome',    'left',  180);
        $site     = new DataGridColumn('site',     'Site',    'left',  180);
        
        // adiciona as colunas à DataGrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($site);
        
        // instancia duas ações da DataGrid
        $action1 = new DataGridAction(array($this, 'onEdit'));
        $action1->setLabel('Editar');
        $action1->setImage('ico_edit.png');
        $action1->setField('id');
        
        $action2 = new DataGridAction(array($this, 'onDelete'));
        $action2->setLabel('Deletar');
        $action2->setImage('ico_delete.png');
        $action2->setField('id');
        
        // adiciona as ações à DataGrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
        // cria o modelo da DataGrid, montando sua estrutura
        $this->datagrid->createModel();
        
        $panel = new Panel('Fabricantes');
        $panel->add($this->form);
        
        $panel2 = new Panel();
        $panel2->add($this->datagrid);
        
        // monta a página através de uma caixa
        $box = new VBox;
        $box->style = 'display:block';
        $box->add($panel);
        $box->add($panel2);
        
        parent::add($box);
    }
    
    /**
     * Salva os dados
     */
    public function onSave()
    {
        $this->onSaveTrait();
        $this->onReload();
    }
    
    /**
     * Carrega os dados
     */
    public function onReload()
    {
        $this->onReloadTrait();   
        $this->loaded = true;
    }
    
    /**
     * Carrega registro para edição
     */
    function onEdit($param)
    {
        if (isset($param['key']))
        {
            $key = $param['key']; // obtém a chave
            Transaction::open('livro'); // inicia transação com o BD
            $fabricante = new Fabricante($key); // instancia o Active Record
            $this->form->setData($fabricante); // lança os dados no formulário
            Transaction::close(); // finaliza a transação
            $this->onReload();
        }
    }
    
    /**
     * Exibe a página
     */
    function show()
    {
         // se a listagem ainda não foi carregada
         if (!$this->loaded)
         {
	        $this->onReload();
         }
         parent::show();
    }
}
