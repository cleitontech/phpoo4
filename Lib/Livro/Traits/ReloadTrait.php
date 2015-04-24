<?php
namespace Livro\Traits;

use Livro\Database\Transaction;
use Livro\Database\Repository;
use Livro\Database\Criteria;
use Livro\Database\Filter;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Dialog\Question;

trait ReloadTrait
{
    /**
     * Carrega a DataGrid com os objetos
     */
    function onReload()
    {
        try
        {
            $class = $this->activeRecord;
            $repository = new Repository( $class );
            
            Transaction::open( $this->connection );
            // cria um critério de seleção de dados
            $criteria = new Criteria;
            $criteria->setProperty('order', 'id');
            
            if (isset($this->filter))
            {
                $criteria->add($this->filter);
            }
            
            // carreta os objetos que satisfazem o critério
            $objects = $repository->load($criteria);
            $this->datagrid->clear();
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    // adiciona o objeto na DataGrid
                    $this->datagrid->addItem($object);
                }
            }
            Transaction::close();
        }
        catch (Exception $e)
        {
            new Message($e->getMessage());
        }
    }
}
