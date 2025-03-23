<?php

namespace Axdron\Radianti\TraitsCadastro;

use Adianti\Database\TRecord;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Form\TFormSeparator;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Exception;

trait RadiantiTraitDetalheDatagrid
{

    use RadiantiTraitFormatacaoDetalhes;

    abstract protected static function getNomeTelaPrincipal(): string;
    abstract protected static function getNomeDetalhe(): string;
    abstract protected static function getModel(): string;


    /** Informa o nome dos campos presentes no detalhe. O primeiro deve ser o campo principal, o segundo deve ser o campo de vinculo com mestre.  
     *  ID e Uniqid não precisam ser declarados.
     *  Exemplo, vinculado a uma tabela de preços (principal) x produtos (detalhe), o campo principal seria o produto_id e o campo mestre seria o id da tabela_precos_id
     * @return array ['campo1', 'campo2', ...]
     */
    abstract protected static function getCampos(): array;

    protected static function getNomeCampoPrincipal(): string
    {
        return self::getCampos()[0];
    }

    protected static function getNomeCampoVinculo(): string
    {
        return self::getCampos()[1];
    }

    protected static function getNomeForm(): string
    {
        return self::getNomeTelaPrincipal()::getNomeForm();
    }

    protected static function getNomeDatagrid(): string
    {
        return 'datagrid_' . self::formatarNomeDetalhe(true);
    }

    protected static function getSnCriaIdUniqid(): bool
    {
        return true;
    }

    protected static function getSnMostraAcaoEditar(): bool
    {
        return true;
    }

    protected static function getSnMostraAcaoExcluir(): bool
    {
        return true;
    }

    /**
     * Retorna o modo de exibição do detalhe.
     * Pode ser 'horizontal' ou em abas.
     */
    protected static function getModoExibicao(): string
    {
        return 'horizontal';
    }

    /**Cria a datagrid
     * @param $param
     * @return BootstrapDatagridWrapper
     * 
     * Exemplo:
     * protected static function criarDatagrid($param): BootstrapDatagridWrapper{
     *     
     * 
     */
    protected static function criarDatagrid($param = null): BootstrapDatagridWrapper
    {

        $datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $datagrid->style = 'width: 100%';
        $datagrid->setHeight(250);
        $datagrid->setId(self::getNomeDatagrid());
        $datagrid->generateHiddenFields();

        if (get_called_class()::getSnCriaIdUniqid()) {
            $colunaUniqid = new TDataGridColumn(get_called_class()::getNomeCampo('uniqid'), 'Uniqid', 'center');
            $colunaUniqid->setVisibility(false);
            $datagrid->addColumn($colunaUniqid);

            $colunaId = new TDataGridColumn(get_called_class()::getNomeCampo('id'), 'Id', 'center');
            $colunaId->setVisibility(false);
            $datagrid->addColumn($colunaId);
        }

        self::criarColunasDatagrid($datagrid, $param);

        self::criarAcoesDatagrid($datagrid, $param);

        $datagrid->createModel();

        return $datagrid;
    }

    /**
     * Cria as colunas da datagrid
     * @param $datagrid 
     * 
     * Exemplo:
     * protected static function criarColunasDatagrid(&$datagrid, $param){
     *      $colunaUniqid = new TDataGridColumn(self::getNomeCampo('uniqid'), 'Uniqid', 'center', '0%');
     *      $colunaUniqid->setVisibility(false);
     *      $datagrid->addColumn($colunaUniqid);
     * 
     *      $colunaId = new TDataGridColumn(self::getNomeCampo('id'), 'Id', 'center', '0%');
     *      $colunaId->setVisibility(false);
     *      $datagrid->addColumn($colunaId);
     * 
     *      ...Demais campos datagrid...
     * }
     */
    abstract protected static function criarColunasDatagrid(&$datagrid, $param);

    protected static function criarAcoesDatagrid(&$datagrid, $param = null)
    {
        if (get_called_class()::getSnMostraAcaoEditar()) {
            $acaoEdicao = new TDataGridAction([self::getNomeTelaPrincipal(), 'editar' . self::formatarNomeDetalhe()], ['static' => 1]);
            $acaoEdicao->setFields([self::getNomeCampo('uniqid'), '*']);
            $acaoEdicao->setImage('fa:edit blue fa-lg');
            $datagrid->addAction($acaoEdicao);
        }

        if (get_called_class()::getSnMostraAcaoExcluir()) {
            $acaoExclusao = new TDataGridAction([self::getNomeTelaPrincipal(), 'excluir' . self::formatarNomeDetalhe()], ['static' => 1]);
            $acaoExclusao->setField(self::getNomeCampo('uniqid'));
            $acaoExclusao->setImage('fa:trash red fa-lg');
            $datagrid->addAction($acaoExclusao);
        }
    }

    static function carregar($mestreId, &$datagrid)
    {
        $model = get_called_class()::getModel();
        $campoMestre = get_called_class()::getCampos()[1];
        $itens = $model::where($campoMestre, '=', $mestreId)->get();

        foreach ($itens as $item) {
            get_called_class()::formatarCamposCarregar($item);
            $itemDatagrid = get_called_class()::converterCamposDBParaItemDatagrid($item);
            get_called_class()::adicionarItemDatagrid($itemDatagrid, $datagrid);
        }
    }

    protected static function converterCamposDBParaItemDatagrid($itemDB)
    {
        $itemDatagrid = [
            self::getNomeCampo('id') => $itemDB->id,
        ];

        foreach (get_called_class()::getCampos() as $campo) {
            $itemDatagrid[get_called_class()::getNomeCampo($campo)] = $itemDB->{$campo};
        }

        return (object) $itemDatagrid;
    }

    static function recarregarDatagrid($param, &$datagrid)
    {
        if (!empty($param[self::getNomeCampoDatagrid(self::getCampos()[0])])) {
            foreach ($param[self::getNomeCampoDatagrid(self::getCampos()[0])] as $key => $campoPrincipal) {
                $item = [
                    self::getNomeCampo('uniqid') => $param[self::getNomeCampoDatagrid('uniqid')][$key] ?? null,
                    self::getNomeCampo('id') => $param[self::getNomeCampoDatagrid('id')][$key]  ?? null,
                ];

                foreach (self::getCampos() as $campo) {
                    $item[self::getNomeCampo($campo)] = $param[self::getNomeCampoDatagrid($campo)][$key] ?? null;
                }

                self::adicionarItemDatagrid((object) $item, $datagrid);
            }
        }
    }

    /**
     * Cria os campos e a datagrid para serem consumidos pelo mestre
     * @param BootstrapFormBuilder $form
     * @param BootstrapDatagridWrapper $datagrid
     * @return array ['form' => $form, 'datagrid' => $datagrid]
     */
    static function criar(&$form, &$datagrid)
    {
        switch (get_called_class()::getModoExibicao()) {
            case 'horizontal':
                $form->addFields([new TFormSeparator(get_called_class()::getNomeDetalhe())]);

                break;

            case 'abas':
                $form->appendPage(get_called_class()::getNomeDetalhe());

                break;

            default:
                throw new Exception('Modo de exibição inválido');
        }

        self::adicionarElementosAntesDatagrid($form);

        $datagrid =  get_called_class()::criarDatagrid($datagrid);
        $form->addFields([$datagrid]);

        self::adicionarElementosDepoisDatagrid($form);

        return ['form' => $form, 'datagrid' => $datagrid];
    }

    protected static function getNomeCampoDatagrid(string $nomeCampo)
    {
        return get_called_class()::getNomeDatagrid() . '_' . get_called_class()::getNomeCampo($nomeCampo);
    }

    /**
     * Adiciona elementos no formulário antes da datagrid
     */
    protected static function adicionarElementosAntesDatagrid(&$form)
    {
        return $form;
    }

    /**
     * Adiciona elementos no formulário após a datagrid
     */
    protected static function adicionarElementosDepoisDatagrid(&$form)
    {
        return $form;
    }

    /**
     * Adiciona um item a datagrid. Atenção: precisa ser reimplementado quando possuir item de tabela vinculado
     * @param array $itemAdicionado
     * @param BootstrapDatagridWrapper $datagrid
     * @return object
     */
    protected static function adicionarItemDatagrid(Object $itemAdicionado, BootstrapDatagridWrapper &$datagrid)
    {
        $itemAdicionado = (object)$itemAdicionado;

        $itemDatagrid = [
            self::getNomeCampo('id') => $itemAdicionado->{self::getNomeCampo('id')} ?? null,
            self::getNomeCampo('uniqid') => empty($uniqid = $itemAdicionado->{self::getNomeCampo('uniqid')} ?? null) ? uniqid() : $uniqid,
        ];

        self::prepararItemAdicionarDatagrid($itemAdicionado);

        foreach (self::getCampos() as $campo) {
            if (isset($itemAdicionado->{self::getNomeCampo($campo)}) && ($itemAdicionado->{self::getNomeCampo($campo)} !== ''))
                $itemDatagrid[self::getNomeCampo($campo)] = $itemAdicionado->{self::getNomeCampo($campo)};
            else
                $itemDatagrid[self::getNomeCampo($campo)] = null;
        }

        if (empty($itemDatagrid))
            return;

        if (empty($datagrid))
            $datagrid = self::criarDatagrid();

        $linhaDatagrid = $datagrid->addItem((object) $itemDatagrid);
        $linhaDatagrid->id = $itemDatagrid[self::getNomeCampo('uniqid')];

        return $linhaDatagrid;
    }


    /**
     * Prepara o item para ser adicionado na datagrid
     * @param object $itemAdicionando
     * @return void
     * 
     * Exemplo:
     * 
     * protected static function prepararItemAdicionarDatagrid(object &$itemAdicionando)
     * {
     *       
     */
    protected static function prepararItemAdicionarDatagrid(object &$itemAdicionando) {}

    /**
     * Exclui um item da datagrid
     * @param array $param
     */
    public static function excluir($param)
    {
        TDataGrid::removeRowById(self::getNomeDatagrid(), $param[self::getNomeCampo('uniqid')]);
    }

    protected static function formatarCamposCarregar(TRecord &$item) {}
}
