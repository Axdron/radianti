<?php

use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Form\TFormSeparator;
use Adianti\Wrapper\BootstrapDatagridWrapper;

trait TraitDetalheDatagrid
{
    abstract protected static function getNomeTelaPrincipal(): string;
    abstract protected static function getNomeForm(): string;
    abstract protected static function getNomeDetalhe(): string;

    protected static function getNomeDatagrid(): string
    {
        return 'datagrid_' . self::formatarNomeDetalhe(true);
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
        $acaoEdicao = new TDataGridAction([self::getNomeTelaPrincipal(), 'editar' . self::formatarNomeDetalhe()], ['static' => 1]);
        $acaoEdicao->setFields([self::getNomeCampo('uniqid'), '*']);
        $acaoEdicao->setImage('fa:edit blue fa-lg');
        $datagrid->addAction($acaoEdicao);

        $acaoExclusao = new TDataGridAction([self::getNomeTelaPrincipal(), 'excluir' . self::formatarNomeDetalhe()], ['static' => 1]);
        $acaoExclusao->setField(self::getNomeCampo('uniqid'));
        $acaoExclusao->setImage('fa:trash red fa-lg');
        $datagrid->addAction($acaoExclusao);
    }

    /**
     * Adiciona um item na datagrid
     * @param Object $itemAdicionado
     * @param BootstrapDatagridWrapper $datagrid
     * 
     * Exemplo:
     * protected static function adicionarItemDatagrid(Object $itemAdicionado, BootstrapDatagridWrapper &$datagrid)
     *  {
     *          $itemAdicionado = (object)$itemAdicionado;
     *
     *          $temUniqid = !empty($itemAdicionado->{self::getNomeCampo('uniqid')});
     *          $uniqid = $temUniqid ? $itemAdicionado->{self::getNomeCampo('uniqid')} : uniqid();
     * 
     *          $itemDatagrid = UtilsAdianti::encapsularTransacao(function () use ($itemAdicionado) {
     * 
     *              $DETALHE = MODEL_DETALHE::find($itemAdicionado[self::getNomeCampo(CAMPO_ID_DETALHE)]);
     * 
     *              if(empty($DETALHE))
     *                  throw new Exception('NOME_DETALHE não encontrado');
     * 
     *              return [
     *                  self::getNomeCampo('uniqid') => $uniqid,
     *                  self::getNomeCampo('id') => $itemAdicionado[self::getNomeCampo('id')],
     *                  self:getNomeCampo(CAMPO_ID_DETALHE) => $DETALHE->id,
     *                  self::getNomeCampo(CAMPO_DESCRITIVO_DETALHE) => $DETALHE->CAMPO DESCRITIVO_DETALHE,
     *                  ... demais campos ...
     *                  ];
     * 
     *          }, true, false);
     * 
     *          if(empty($itemDatagrid))
     *              return;
     * 
     *          if(empty($datagrid))
     *              $datagrid = self::criarDatagrid();
     * 
     *          $linhaDatagrid = $datagrid->addItem((object) $itemDatagrid);
     *          $linhaDatagrid->id = $itemAdicionado[$uniqid];
     * 
     *          return $linhaDatagrid;
     * }
     */
    abstract protected static function adicionarItemDatagrid(Object $itemAdicionado, BootstrapDatagridWrapper &$datagrid);

    /**
     * Carrega o detalhe do BD
     * @param $mestreId
     * @param $datagrid
     * 
     * Exemplo:
     * static function carregar($mestreId, &$datagrid)
     * {
     *      $model = get_called_class()::getModel
     * 
     *      $itens = $model::where(CAMPO_MESTRE, '=', $mestreId)->get();
     * 
     *      $datagrid->clear();
     * 
     *      foreach ($itens as $item) {
     *          $itemAdicionar = [
     *              self::getNomeCampo('uniqid') => uniqid(),
     *              self::getNomeCampo('id') => $item->id,
     *              self::getNomeCampo(CAMPO_ID_DETALHE) => $item->CAMPO_ID_DETALHE,
     *              ...demais campos...
     *              ];
     * 
     *       self::adicionarItemDatagrid((object) $itemAdicionar, $datagrid);
     *     }
     * 
     *      return $datagrid;
     * }
     * 
     */
    abstract static function carregar($mestreId, &$datagrid);

    /**
     * Recarrega a datagrid
     * @param $param
     * @param $datagrid
     * 
     * Exemplo:
     * static function recarregarDatagrid($param, $datagrid)
     * {
     *     if (!empty($param[self::getNomeCampoDatagrid(CAMPO_ID_DETALHE)])) {
     *        foreach ($param[self::getNomeCampoDatagridCAMPO_ID_DETALHE)] as $key => $CAMPO_ID_DETALHE) {
     *           $item = [
     *              self::getNomeCampo('uniqid') => $param[self::getNomeCampoDatagrid('uniqid')][$key],
     *              self::getNomeCampo('id') => $param[self::getNomeCampoDatagrid('id')][$key],
     *              self::getNomeCampo(CAMPO_ID_DETALHE) => $CAMPO_ID_DETALHE,
     *              ...demais campos...
     *              ];
     *            self::adicionarItemDatagrid((object) $item, $datagrid);
     *          }
     *      }
     * 
     *      return $datagrid;
     * } 
     */
    abstract static function recarregarDatagrid($param, &$datagrid);

    /**
     * Cria os campos e a datagrid para serem consumidos pelo mestre
     * @param BootstrapFormBuilder $form
     * @param BootstrapDatagridWrapper $datagrid
     * @return array ['form' => $form, 'datagrid' => $datagrid]
     */
    static function criar(&$form, &$datagrid)
    {
        $form->addFields([new TFormSeparator(get_called_class()::getNomeDetalhe())]);

        $datagrid =  get_called_class()::criarDatagrid($datagrid);
        $form->addFields([$datagrid]);

        return ['form' => $form, 'datagrid' => $datagrid];
    }

    protected static function getPrefixoDetalhe()
    {
        return 'detalhe_' . self::formatarNomeDetalhe(true) . '_';
    }

    protected static function getNomeCampo(string $nomeCampo)
    {
        return self::getPrefixoDetalhe() . $nomeCampo;
    }

    protected static function getNomeCampoDatagrid(string $nomeCampo)
    {
        return get_called_class()::getNomeDatagrid() . '_' . get_called_class()::getNomeCampo($nomeCampo);
    }

    protected static function formatarNomeDetalhe($snMinusculo = false)
    {
        $nomeDetalhe = get_called_class()::getNomeDetalhe();

        $nomeSemCarateresEspeciais = Format::desformataCaracteresEspeciais($nomeDetalhe);

        if ($snMinusculo) {
            $nomeMinusculo = strtolower($nomeSemCarateresEspeciais);
            $nomeComUnderline = str_replace(' ', '_', $nomeMinusculo);
            return $nomeComUnderline;
        }

        $nomeSemEspacos = str_replace(' ', '', $nomeSemCarateresEspeciais);

        return $nomeSemEspacos;
    }
}
