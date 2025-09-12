<?php

namespace Axdron\Radianti\TelasModelo;

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Wrapper\TQuickForm;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormWrapper;
use Axdron\Radianti\Componentes\RadiantiElementoLabelExplicativa;
use Axdron\Radianti\Services\RadiantiArrayService;
use Axdron\Radianti\Services\RadiantiNavegacao;
use Axdron\Radianti\Services\RadiantiPDFService;
use Axdron\Radianti\Services\RadiantiPlanilhaService;
use Exception;

abstract class RadiantiRelatorioModelo extends TPage
{

    abstract protected static function getNomeRelatorio(): string;

    abstract protected static function getExplicacao(): string;

    protected static function getOrientacaoPDF(): string
    {
        return 'retrato';
    }

    protected static function getNomeForm(): string
    {
        return get_called_class() . 'Form';
    }

    protected $form;
    protected $datagrid;
    protected $itensDatagrid;
    protected $campos;


    public function __construct($param)
    {
        parent::__construct();

        $this->criarFormBusca($param);
        $this->criarDataGridResultados($param);
        $panelDataGrid = $this->criarPainelDatagrid();

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', get_class($this)));
        $container->add(new RadiantiElementoLabelExplicativa(get_called_class()::getExplicacao()));
        $container->add(TPanelGroup::pack(get_called_class()::getNomeRelatorio(), $this->form));
        $container->add($panelDataGrid);

        parent::add($container);
    }

    public function validarFormulario($param)
    {
        try {
            $dadosFormulario = $this->form->getData();
            $this->form->validate();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            return;
        } finally {
            $this->form->setData($dadosFormulario);
        }

        $this->itensDatagrid =  $this->efetuarBusca($dadosFormulario);

        if (!empty($param['gerarPDF'])) {
            $this->gerarPDFDatagrid($dadosFormulario);
            return;
        }
        if (!empty($param['gerarXLSX'])) {
            $this->gerarXLSXDatagrid($dadosFormulario);
            return;
        };
    }

    /**
     * Monta a query para efetuar a busca no banco de dados
     * @param object $dadosFormulario
     * @return array retorna a query para a consulta
     */
    abstract protected function executarConsulta(object $dadosFormulario): array;

    /**
     * Cria os campos do formulário
     * @return array ['label' => '', 'campo' => '', 'tamanho' => '']
     * 
     * Exemplo: 
     * protected function criarCampos() {
     * $data_inicial = Campo::PeriodoInicial("data_inicial");
     * $data_final = Campo::PeriodoFinal("data_final");
     * 
     * return [
     * ["label" => 'Data Inicial', "campo" => $data_inicial, "tamanho" => "50%"],
     * ["label" => 'Data Final', "campo" => $data_final, "tamanho" => "50%"],
     * ];
     * }
     */
    abstract protected function criarCampos(): array;

    /**
     * Cria as colunas do datagrid
     * @return array 
     * 
     * Exemplo: 
     * protected function criarColunasDatagrid() {
     * $vendedor = new TDataGridColumn('vendedor', 'Vendedor', 'left');
     * $base = new TDataGridColumn('base', 'Base', 'center');
     * 
     * return [$vendedor, $base];
     * }
     */
    abstract protected function criarColunasDatagrid($param): array;

    /**
     * Efetua a busca no banco de dados
     * @param object $dadosFormulario
     * @return array
     */
    private function efetuarBusca($dadosFormulario)
    {
        $this->datagrid->clear();

        $resultado = $this->executarConsulta($dadosFormulario);

        $this->datagrid->addItems($resultado);

        return $resultado;
    }

    /**
     * Cria o formulário de busca
     * @param array $param Parâmetros para preencher os campos do formulário
     * @return TQuickForm
     */
    private function criarFormBusca($param = [])
    {
        $this->form = new TQuickForm(get_called_class()::getNomeForm());
        $this->form->class = 'tform';
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%';
        $this->form->setFieldsByRow($this->numeroColunaRelatorio ?? 2);

        $this->campos = $this->criarCampos();

        foreach ($this->campos as $campo) {
            if (!empty($campo['label'])) {
                if (isset($param[$campo['campo']->getName()])) {
                    $campo['campo']->setValue($param[$campo['campo']->getName()]);
                }
                $this->form->addQuickField($campo['label'], $campo['campo'], $campo['tamanho'] ?? '100%');
            }
        }

        $this->form->addQuickAction("Buscar", new TAction(array($this, 'validarFormulario')), 'fa:search');
        $this->form->addQuickAction("Gerar PDF", new TAction(array($this, 'validarFormulario'), ["gerarPDF" => 1]), 'far:file-pdf red');
        $this->form->addQuickAction("Gerar XLSX", new TAction(array($this, 'validarFormulario'), ["gerarXLSX" => 1]), 'fa:table blue');

        $this->criarBotoesExtras();

        return $this->form;
    }

    /**
     * Cria botões extras no formulário
     */
    protected function criarBotoesExtras() {}

    private function criarDataGridResultados($param)
    {
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);


        $colunas = $this->criarColunasDatagrid($param);

        foreach ($colunas as $coluna) {
            $this->datagrid->addColumn($coluna);
        }

        $this->datagrid->createModel();

        return $this->datagrid;
    }

    private function criarPainelDatagrid()
    {
        $panelDataGrid = new TPanelGroup();
        $panelDataGrid->add($this->datagrid);
        return $panelDataGrid;
    }

    private function gerarPDFDatagrid($dadosFormulario)
    {
        $conteudoDatagrid = file_get_contents('app/resources/styles-print.html') . $this->datagrid->getContents();
        $conteudoDatagrid .= "<br><br>Filtros:<br>" . RadiantiArrayService::converterEmTexto((array) $dadosFormulario);

        $arquivo = RadiantiPDFService::gerarPDFHTML(get_called_class()::getNomeRelatorio(), $conteudoDatagrid, orientacao: get_called_class()::getOrientacaoPDF());
        if ($arquivo)
            TPage::openFile($arquivo);
    }

    private function gerarXLSXDatagrid()
    {
        $conteudoDatagrid = $this->datagrid->getOutputData();

        $colunasParaDesformatar = $this->desformatarColunasParaXLSX();
        if (!empty($colunasParaDesformatar)) {
            $conteudoDatagrid = $this->aplicarDesformatacao($conteudoDatagrid, $colunasParaDesformatar);
        }

        $arquivo = RadiantiPlanilhaService::gerarXLSX(get_called_class()::getNomeRelatorio(), $conteudoDatagrid);
        if ($arquivo)
            TPage::openFile($arquivo);
    }

    /**
     * Aplica desformatação nas colunas especificadas
     * @param array $conteudoDatagrid Array com dados do datagrid
     * @param array $colunasParaDesformatar Array com nome_coluna => funcao_desformatacao
     * @return array Array com dados desformatados
     */
    private function aplicarDesformatacao(array $conteudoDatagrid, array $colunasParaDesformatar): array
    {
        if (empty($conteudoDatagrid)) {
            return $conteudoDatagrid;
        }

        $cabecalho = $conteudoDatagrid[0];
        $indicesParaDesformatar = [];

        foreach ($colunasParaDesformatar as $nomeColuna => $funcaoDesformatacao) {
            $indice = array_search($nomeColuna, $cabecalho);
            if ($indice !== false) {
                $indicesParaDesformatar[$indice] = $funcaoDesformatacao;
            }
        }

        for ($linha = 1; $linha < count($conteudoDatagrid); $linha++) {
            foreach ($indicesParaDesformatar as $indice => $funcaoDesformatacao) {
                if (isset($conteudoDatagrid[$linha][$indice])) {
                    $valor = $conteudoDatagrid[$linha][$indice];
                    $conteudoDatagrid[$linha][$indice] = call_user_func($funcaoDesformatacao, $valor);
                }
            }
        }

        return $conteudoDatagrid;
    }

    /**
     * Desformata as colunas para exportação em XLSX
     * @return array O array deve conter os nomes das colunas e as funções de desformatação. Exemplo:
     * [
     *     'Estoque Mínimo' => fn($value) => str_replace('.', '', $value)
     * ];
     */
    protected function desformatarColunasParaXLSX()
    {
        return [];
    }

    /**
     * Abre o relatório em uma nova guia do navegador
     * @param array $param Parâmetros para filtros iniciais
     * Exemplo: ['filtros' => ['data_inicial' => '2023-01-01', 'data_final' => '2023-12-31']]
     */
    static function abrir($param = [])
    {
        $stringClasse = get_called_class();
        if (isset($param['filtros'])) {
            $stringParametros = http_build_query($param['filtros']);
            $stringClasse .= "&{$stringParametros}";
        }
        RadiantiNavegacao::abrirNovaGuia($stringClasse);
    }
}
