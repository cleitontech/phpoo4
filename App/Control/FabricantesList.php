<?php
use Livro\Control\Page;
use Livro\Control\Action;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Label;
use Livro\Widgets\Form\Button;
use Livro\Widgets\Container\Table;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Datagrid\DatagridAction;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Dialog\Question;
use Livro\Database\Transaction;
use Livro\Database\Repository;
use Livro\Database\Criteria;

use Bootstrap\Wrapper\DatagridWrapper;
use Bootstrap\Wrapper\FormWrapper;

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
    
    /*
     * método construtor
     * Cria a página, o formulário e a listagem
     */
    public function __construct()
    {
        parent::__construct();
        
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
        
        // monta a página através de uma tabela
        $table = new Table;
        // cria uma linha para o formulário
        $row = $table->addRow();
        $row->addCell($this->form);
        // cria uma linha para a datagrid
        $row = $table->addRow();
        $row->addCell($this->datagrid);
        // adiciona a tabela à página
        parent::add($table);
    }
    
    /*
     * função onReload()
     * Carrega a DataGrid com os objetos do banco de dados
     */
    function onReload()
    {
        // inicia transação com o banco 'livro'
        Transaction::open('livro');
        
        // instancia um repositório para Fabricante
        $repository = new Repository('Fabricante');
        
        // cria um critério de seleção, ordenado pelo id
        $criteria = new Criteria;
        $criteria->setProperty('order', 'id');
        // carrega os objetos de acordo com o criterio
        $fabricantes = $repository->load($criteria);
        $this->datagrid->clear();
        if ($fabricantes)
        {
            // percorre os objetos retornados
            foreach ($fabricantes as $fabricante)
            {
                // adiciona o objeto na DataGrid
                $this->datagrid->addItem($fabricante);
            }
        }
        // finaliza a transação
        Transaction::close();
        $this->loaded = true;
    }
    
    /*
     * função onSave()
     * Executada quando o usuário clicar no botão salvar do formulário
     */
    function onSave()
    {
        // inicia transação com o banco 'livro'
        Transaction::open('livro');
        // obtém os dados no formulário em um objeto Fabricante
        $fabricante = $this->form->getData('Fabricante');
        // armazena o objeto
        $fabricante->store();
        
        // finaliza a transação
        Transaction::close();
        // exibe mensagem de sucesso
        new Message('info', 'Dados armazenados com sucesso');
        // re-carrega listagem
        $this->onReload();
    }
    
    /*
     * método onDelete()
     * Executada quando o usuário clicar no botão excluir da datagrid
     * Pergunta ao usuário se deseja realmente excluir um registro
     */
    function onDelete($param)
    {
        // obtém o parâmetro $key
        $key=$param['key'];
        
        // define duas ações
        $action1 = new Action(array($this, 'Delete'));
        
        // define os parâmetros de cada ação
        $action1->setParameter('key', $key);
        
        // exibe um diálogo ao usuário
        new Question('Deseja realmente excluir o registro ?', $action1);
    }
    
    /*
     * método Delete()
     * Exclui um registro
     */
    function Delete($param)
    {
        // obtém o parâmetro $key
        $key=$param['key'];
        
        // inicia transação com o banco 'livro'
        Transaction::open('livro');
        
        // instanicia objeto Fabricante
        $fabricante = new Fabricante($key);
        // deleta objeto do banco de dados
        $fabricante->delete();
        
        // finaliza a transação
        Transaction::close();
        
        // re-carrega a datagrid
        $this->onReload();
        // exibe mensagem de sucesso
        new Message('info', "Registro Excluído com sucesso");
    }
    
    /*
     * método onEdit()
     * Executada quando o usuário clicar no botão visualizar
     */
    function onEdit($param)
    {
        if (isset($param['key']))
        {
            // obtém o parâmetro e exibe mensagem
            $key=$param['key'];
            // inicia transação com o banco 'livro'
            Transaction::open('livro');
            
            // instanicia objeto Fabricante
            $fabricante = new Fabricante($key);
            // lança os dados do fabricante no formulário
            $this->form->setData($fabricante);
            
            // finaliza a transação
            Transaction::close();
            $this->onReload();
        }
    }
    
    /*
     * método show()
     * Executada quando o usuário clicar no botão excluir
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
