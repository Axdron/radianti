<?php

namespace Axdron\Radianti\TelasModelo;

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TRepository;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;
use Axdron\Radianti\Services\RadiantiTransaction;

abstract class RadiantiListagemModelo extends TPage
{
    protected $formularioBusca;
    protected $datagrid;
    protected $pageNavigation;
    protected $limitDatagrid = 10;
    protected $ordenacao = 'id desc';
    protected $snMostraOpcaoExcluirDatagrid = true;
    protected $snMostraOpcaoEditarDatagrid = true;
    protected $snMostraBreadCrumb = true;

    /**
     * Retorna o nome da tela de cadastro ou false caso não possua
     * @return string|false
     */
    abstract protected static function getNomeFormularioBusca(): string|false;
    abstract protected static function getTitulo(): string;
    abstract protected static function getModel(): string;

    /**
     * Retorna o nome da tela de cadastro ou false caso não possua
     * @return string|false
     */
    abstract protected static function getNomeTelaCadastro(): string | false;

    public function __construct($param)
    {
        parent::__construct();

        $this->criarFormBusca();
        $this->criarDatagrid();

        $container = new TVBox;
        $container->style = 'width: 100%';

        if ($this->snMostraBreadCrumb)
            $container->add(new TXMLBreadCrumb('menu.xml', get_called_class()));
        $container->add($this->formularioBusca);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x: auto';

        parent::add($container);

        $snFazendoBusca = isset($param['method']) && $param['method'] == 'buscar';
        $snCarregandoAbertura = !$snFazendoBusca;

        if ($snCarregandoAbertura)
            $this->carregar($param);
    }

    public function abrir()
    {
    }

    /**
     * Cria os campos do formulário de busca
     * @return void
     * 
     * Exemplo:
     * 
     * $id = new TEntry('id');
     * $fornecedor = Fornecedor::criarCampoBusca();
     * 
     * $this->formularioBusca->addFields([new TLabel('ID')], [$id], [new TLabel('Fornecedor')], [$fornecedor]);
     */
    abstract protected function criarCamposBusca();

    /**
     * Cria as colunas da datagrid
     * @return void
     * 
     * Exemplo:
     * 
     * $this->datagrid->addColumn($colunaValorTotal = new TDataGridColumn('valor_total', 'Valor Total', 'right'));
     * 
     * $colunaDataEntrada->setTransformer(function ($dataEntrada) {
     *    return TDate::date2br($dataEntrada);
     * });
     * 
     */
    abstract protected function criarColunasDatagrid();

    /**
     * Adiciona os filtros na critéria de busca
     * @param array $dadosFormulario
     * @param TCriteria $criteria
     * @return TCriteria
     * 
     * Exemplo:
     * 
     * $criteria = new TCriteria;
     * 
     */
    abstract protected function adicionarFiltrosCriteria(array $dadosFormulario, TCriteria $criteria): TCriteria;

    /**
     * Cria a critéria padrão para a busca
     * @return TCriteria
     */
    protected function criarCriteriaPadrao()
    {
        $criteria = new TCriteria;
        $criteria->setProperty('limit', $this->limitDatagrid);
        $criteria->setProperty('order', $this->ordenacao);
        return $criteria;
    }

    private function criarFormBusca()
    {
        $this->formularioBusca = new BootstrapFormBuilder(get_called_class()::getNomeFormularioBusca());
        $this->formularioBusca->setFormTitle(get_called_class()::getTitulo());

        $this->criarCamposBusca();

        $this->formularioBusca->addAction('Buscar', new TAction([$this, 'buscar']), 'fa:search blue');
        if ($nomeTelaCadastro = $this->getNomeTelaCadastro())
            $this->formularioBusca->addActionLink('Novo', new TAction([$nomeTelaCadastro, 'abrirEdicao']), 'fa:plus green');

        $this->criarBotoesExtras();
    }

    /**
     * Cria os botões extras do formulário de busca
     * @return void
     */
    protected function criarBotoesExtras()
    {
    }

    private function criarDatagrid()
    {
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid());
        $this->datagrid->style = 'width: 100%';

        $this->criarColunasDatagrid();

        if ($this->snMostraOpcaoEditarDatagrid && $nomeTelaCadastro = get_called_class()::getNomeTelaCadastro()) {
            $acaoEdicao = new TDataGridAction([$nomeTelaCadastro, 'abrirEdicao']);
            $acaoEdicao->setLabel('Editar');
            $acaoEdicao->setImage('far:edit blue fa-md');
            $acaoEdicao->setField('id');
            $this->datagrid->addAction($acaoEdicao);
        }

        if ($this->snMostraOpcaoExcluirDatagrid) {
            $acaoExclusao = new TDataGridAction([$this, 'excluir']);
            $acaoExclusao->setLabel('Excluir');
            $acaoExclusao->setImage('far:trash-alt red fa-lg');
            $acaoExclusao->setField('id');
            $this->datagrid->addAction($acaoExclusao);
        }

        $this->criarAcoesDatagrid();

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'carregar']));
        $this->pageNavigation->setLimit($this->limitDatagrid);
    }

    protected function criarAcoesDatagrid()
    {
    }


    public function buscar()
    {
        $dadosFormulario = (array) $this->formularioBusca->getData();
        $this->formularioBusca->setData($dadosFormulario);

        $criteria = new TCriteria;
        $criteria->setProperty('limit', $this->limitDatagrid);
        $criteria->setProperty('order', $this->ordenacao);
        $criteria = $this->adicionarFiltrosCriteria($dadosFormulario, $criteria);

        TSession::setValue(get_called_class() . '_dados_formulario', (object)$dadosFormulario);
        TSession::setValue(get_called_class() . '_criteria', $criteria);

        $this->carregar(['first_page' => 1]);
    }

    public function carregar($param = null)
    {
        RadiantiTransaction::encapsularTransacao(callback: function () use ($param) {
            $this->formularioBusca->setData(TSession::getValue(get_called_class() . '_dados_formulario'));

            if (empty($criteria = TSession::getValue(get_called_class() . '_criteria'))) {
                $criteria = $this->criarCriteriaPadrao();
            }

            $criteria->setProperties($param);
            $criteria->setProperty('limit', $this->limitDatagrid);
            $criteria->setProperty('order', $this->ordenacao);

            $repository = new TRepository(get_called_class()::getModel());
            $objects = $repository->load($criteria, false);

            $this->datagrid->clear();

            if ($objects) {
                $this->datagrid->addItems($objects);
            }

            $criteria->resetProperties();
            $count = $repository->count($criteria);

            $this->pageNavigation->setCount($count);
            $this->pageNavigation->setProperties($param);
            $this->pageNavigation->setLimit($this->limitDatagrid);
        }, snAbrirTransacao: false);
    }

    function excluir($param)
    {
        if (empty($param['sn_confirmada_exclusao'])) {
            $param['sn_confirmada_exclusao'] = 1;
            $acaoExclusao = new TAction([$this, 'excluir'], $param);
            new TQuestion('Deseja realmente excluir o registro?', $acaoExclusao);
        } else {
            RadiantiTransaction::encapsularTransacao(callback: function () use ($param) {
                $model = $this->getModel();
                $objeto = new $model($param['id']);
                $objeto->delete();
                new TMessage('info', 'Registro excluído com sucesso!');
                $this->carregar();
            });
        }
    }
}
