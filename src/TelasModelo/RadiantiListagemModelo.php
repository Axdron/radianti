<?php

namespace Axdron\Radianti\TelasModelo;

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TRepository;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TScript;
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
use Axdron\Radianti\Services\RadiantiArrayService;
use Axdron\Radianti\Services\RadiantiPDFService;
use Axdron\Radianti\Services\RadiantiPlanilhaService;

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
    protected static function getNomeFormularioBusca(): string|false
    {
        return get_called_class() . 'FormBusca';
    }

    abstract protected static function getTitulo(): string;
    abstract protected static function getModel(): string;

    /**
     * Retorna se deve mostrar a opção de exportação na listagem
     * @return bool
     */
    protected static function getSnMostraExportacao(): bool
    {
        return (bool) getenv('RADIANTI_SN_MOSTRA_EXPORTACAO_LISTAGEM') ?? false;
    }


    /**
     * Retorna o nome da tela de cadastro ou false caso não possua
     * @return string|false
     */
    abstract protected static function getNomeTelaCadastro(): string | false;

    protected $snPermiteCadastrarNovo = true;

    protected static function getArquivoMenu(): string
    {
        return 'menu.xml';
    }

    public function __construct($param)
    {
        parent::__construct();

        $this->criarFormBusca();
        $this->criarDatagrid();

        $container = new TVBox;
        $container->style = 'width: 100%';

        if ($this->snMostraBreadCrumb)
            $container->add(new TXMLBreadCrumb(
                get_called_class()::getArquivoMenu(),
                get_called_class()
            ));
        $container->add($this->formularioBusca);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x: auto';

        parent::add($container);

        $snFazendoBusca = isset($param['method']) && $param['method'] == 'buscar';
        $snCarregandoAbertura = !$snFazendoBusca;

        if ($snCarregandoAbertura)
            $this->carregar($param);
    }

    public function abrir() {}

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
        if (($nomeTelaCadastro = $this->getNomeTelaCadastro()) && $this->snPermiteCadastrarNovo)
            $this->formularioBusca->addActionLink('Novo', new TAction([$nomeTelaCadastro, 'abrirEdicao']), 'fa:plus green');

        if (get_called_class()::getSnMostraExportacao()) {
            $this->formularioBusca->addAction('Gerar PDF', new TAction([$this, 'buscar'], ['snGerarPDF' => 1]), 'far:file-pdf red');
            $this->formularioBusca->addAction('Gerar XLSX', new TAction([$this, 'buscar'], ['snGerarXLSX' => 1]), 'fa:table blue');
        }

        $this->criarBotoesExtras();
    }

    /**
     * Cria os botões extras do formulário de busca
     * @return void
     */
    protected function criarBotoesExtras() {}

    private function gerarPDF($objects)
    {
        try {
            $datagridClone = $this->clonarDatagrid($objects);
            $dadosFormulario = (array) $this->formularioBusca->getData();
            $conteudoDatagrid = file_get_contents('app/resources/styles-print.html') . $datagridClone->getContents();
            $conteudoDatagrid .= "<br><br>Filtros:<br>" . (RadiantiArrayService::converterEmTexto((array) $dadosFormulario));

            $arquivo = RadiantiPDFService::gerarPDFHTML(
                get_called_class()::getTitulo(),
                $conteudoDatagrid
            );
            if ($arquivo)
                TPage::openFile($arquivo);
        } catch (\Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    private function gerarXlsx($objects)
    {
        try {
            $datagridClone = $this->clonarDatagrid($objects);

            $arquivo = RadiantiPlanilhaService::gerarXLSXDatagrid(
                get_called_class()::getTitulo(),
                $datagridClone
            );
            if ($arquivo)
                TPage::openFile($arquivo);
        } catch (\Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    private function clonarDatagrid($itens)
    {
        $datagridClone = clone $this->datagrid;
        $datagridClone->prepareForPrinting();
        $datagridClone->clear();
        $datagridClone->addItems($itens);
        return $datagridClone;
    }

    private function criarDatagrid()
    {
        $this->datagrid = new BootstrapDatagridWrapper(new RTDataGrid());
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

    protected function criarAcoesDatagrid() {}


    public function buscar($param = null)
    {
        try {
            if (!empty($param['snBuscaExterna'])) {
                $dadosFormulario = $param;
            } else {
                $dadosFormulario = (array) $this->formularioBusca->getData();
            }

            $this->formularioBusca->setData($dadosFormulario);
            $this->formularioBusca->validate();

            $criteria = new TCriteria;
            $criteria->setProperty('limit', $this->limitDatagrid);
            $criteria->setProperty('order', $this->ordenacao);
            $criteria = $this->adicionarFiltrosCriteria($dadosFormulario, $criteria);

            TSession::setValue(get_called_class() . '_dados_formulario', (object)$dadosFormulario);
            TSession::setValue(get_called_class() . '_criteria', $criteria);

            $this->carregar(['first_page' => 1, ...($param ?? [])]);
        } catch (\Throwable $th) {
            new TMessage('error', $th->getMessage());
        }
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

            if (!empty($param['snGerarPDF'])) {
                $criteria->setProperty('limit', null);
                $objects = $repository->load($criteria, false);
                $this->gerarPDF($objects);
                return;
            }

            if (!empty($param['snGerarXLSX'])) {
                $criteria->setProperty('limit', null);
                $objects = $repository->load($criteria, false);
                $this->gerarXlsx($objects);
                return;
            }
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
            });
            $this->carregar();
        }
    }

    /**
     * Clica no botão buscar de forma estática
     * Usado para atualizar a lista após ações como confirmar ou cancelar
     * @return void
     */
    static function clicarNoBotaoBuscarEstaticamente()
    {
        TScript::create("document.querySelector('#tbutton_btn_buscar').click();");
    }
}

/**
 * Reimplementação necessária para corrigir o método clear do TDataGrid
 * que não estava limpando o outputData, causando problemas na exportação
 * de dados para PDF e XLSX.
 */
class RTDataGrid extends TDataGrid
{

    public function clear($preserveHeader = TRUE, $rows = 0)
    {
        $this->outputData = [];
        parent::clear($preserveHeader, $rows);
    }
}
