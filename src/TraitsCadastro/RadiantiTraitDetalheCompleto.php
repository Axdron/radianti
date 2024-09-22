<?php

namespace Axdron\Radianti\TraitsCadastro;

use Adianti\Database\TRecord;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TFormSeparator;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;
use Exception;

trait RadiantiTraitDetalheCompleto
{

    use RadiantiTraitDetalheDatagrid;

    abstract protected static function getNomeTelaPrincipal(): string;
    abstract protected static function getNomeDetalhe(): string;
    abstract protected static function getModel(): string;

    /** Informa o nome dos campos presentes no detalhe. O primeiro deve ser o campo principal, o segundo deve ser o campo de vinculo com mestre.  
     *  ID e Uniqid não precisam ser declarados.
     *  Exemplo, vinculado a uma tabela de preços (principal) x produtos (detalhe), o campo principal seria o produto_id e o campo mestre seria o id da tabela_precos_id
     * @return array ['campo1', 'campo2', ...]
     */
    abstract protected static function getCampos(): array;

    protected static function getNomeForm(): string
    {
        return self::getNomeTelaPrincipal()::getNomeForm();
    }

    protected static function getNomeCampoPrincipal(): string
    {
        return self::getCampos()[0];
    }

    protected static function getNomeCampoVinculo(): string
    {
        return self::getCampos()[1];
    }

    protected static function getSnDetalheObrigatorio(): bool
    {
        return false;
    }

    /**
     * Cria os campos do formulário do detalhe
     * @param BootstrapFormBuilder $form
     * @return BootstrapFormBuilder
     * 
     * Exemplo:
     * protected function criarCamposFormularioDetalhe(BootstrapFormBuilder &$form): BootstrapFormBuilder
     * {
     *    $campoUniqid = new THidden(self::getNomeCampo('uniqid'));
     * 
     *    $campoId = new THidden(self::getNomeCampo('id'));
     * 
     *    $campoMestreId = new THidden(self::getNomeCampo('mestre_id'));
     * 
     *    $form->addFields([
     *      $campoUniqid,
     *      $campoId,
     *      $campoMestreId
     *      ]);
     * 
     *      ... Demais Campos ...
     * 
     *    $botaoAdicionar = new TButton('adicionar' . self::formatarNomeDetalhe());
     *    $botaoAdicionar->setLabel('Adicionar');
     *    $botaoAdicionar->setImage('fa:plus-circle green');
     *    $botaoAdicionar->setAction(new TAction([self::getNomeTelaPrincipal(), 'adicionar' . self::formatarNomeDetalhe()], ['static' => 1]), 'Adicionar');
     *    $form->addFields([],[$botaoAdicionar]);
     * 
     *     ... Demais Botões ...
     *    }
     */
    abstract protected static function criarCampos(BootstrapFormBuilder &$form);

    /**
     * Salva o detalhe
     * @param int $mestreId
     * @param array $param
     * @return void
     * }
     */
    public static function salvar($mestreId, $param)
    {

        $itensDatagrid = $param[self::getNomeCampoDatagrid(
            get_called_class()::getCampos()[0]
        )] ?? null;

        if (empty($itensDatagrid) && self::getSnDetalheObrigatorio())
            throw new Exception('É obrigatório informar ao menos um item em ' . self::getNomeDetalhe());

        $model = get_called_class()::getModel();
        $itensSalvosAnteriormente = $model::where(
            get_called_class()::getCampos()[1],
            '=',
            $mestreId
        )->load();

        $itensASeremMantidos = [];

        if (!empty($itensDatagrid)) {
            foreach ($itensDatagrid as $indice => $valorInicial) {
                $item = $model::firstOrNew(['id' => $param[self::getNomeCampoDatagrid('id')][$indice] ?? null]);
                foreach (get_called_class()::getCampos() as $campo) {
                    $item->{$campo} = $param[self::getNomeCampoDatagrid($campo)][$indice] ?? null;
                }
                $item->{get_called_class()::getCampos()[1]} = $mestreId;
                self::tratarItemSalvamento($item);
                $item->store();

                $itensASeremMantidos[] = $item->id;
            }
        }

        self::removerItensBD($itensSalvosAnteriormente, $itensASeremMantidos);
    }

    /**
     * Aplica algum tratamento especial em valores do item
     */
    protected static function tratarItemSalvamento(&$item) {}

    /**
     * Adiciona um item a datagrid
     * @param array $param
     * @param BootstrapDatagridWrapper $datagrid
     * 
     * Exemplo:
     * public static function adicionar($param, BootstrapDatagridWrapper &$datagrid)
     * {
     *      $itemAdicionado = (object) $param;
     *    
     *      ...Validar campos...
     * 
     *      $snEstavaEditando = !empty($itemAdicionado->{self::getNomeCampo('uniqid')});
     * 
     *     $linhaDatagrid = self::adicionarItemDatagrid($itemAdicionado, $datagrid);
     *     TDataGrid::replaceRowById(self::getNomeDatagrid(), $linhaDatagrid->id, $linhaDatagrid);
     * 
     *     if ($snEstavaEditando)
     *          TScript::create("$('#{$linhaDatagrid->id}')[0].scrollIntoView();");
     * 
     *      $camposFormulario = ['id', 'uniqid, ...demais campos...];
     * 
     *      $camposZerados = [];
     *      foreach ($camposFormulario as $campo) 
     *         $camposZerados[self::getNomeCampo($campo)] = '';
     * 
     *      TForm::sendData(self::getNomeForm(), $camposZerados, false, false);
     * 
     *      return $itemAdicionado;
     * }
     */
    public static function adicionar($param, BootstrapDatagridWrapper &$datagrid)
    {
        $itemAdicionado = (object) $param;

        if (!get_called_class()::validarCampos($itemAdicionado))
            return;

        $snEstavaEditando = !empty($itemAdicionado->{self::getNomeCampo('uniqid')});

        $linhaDatagrid = get_called_class()::adicionarItemDatagrid($itemAdicionado, $datagrid);
        TDataGrid::replaceRowById(self::getNomeDatagrid(), $linhaDatagrid->id, $linhaDatagrid);

        if ($snEstavaEditando)
            TScript::create("$('#{$linhaDatagrid->id}')[0].scrollIntoView();");

        get_called_class()::zerarCampos();

        TForm::sendData(self::getNomeForm(), (object) [
            self::getNomeCampo('id') => '',
            self::getNomeCampo('uniqid') => ''
        ], false, false);

        return $itemAdicionado;
    }

    /**
     * Valida os campos do item adicionado
     * @param object $itemAdicionado
     * @return bool
     */
    protected static function validarCampos(object $itemAdicionado): bool
    {
        return true;
    }

    protected static function zerarCampos()
    {
        foreach (get_called_class()::getCampos() as $campo)
            $camposZerados[self::getNomeCampo($campo)] = '';

        TForm::sendData(self::getNomeForm(), $camposZerados, false, false);
    }

    /**
     * Valida se um campo adicionado é válido, senão dispara uma exception
     * @param object $itemAdicionado
     * @param string $nomeCampo Nome do campo que será validado no objeto
     * @param string $descricaoCampo Descrição que saiá na mensagem de erro, caso o campo não seja preenchido
     */
    protected static function validaObrigatoriedadeCampo(object $itemAdicionado, string $nomeCampo, string $descricaoCampo)
    {
        if (empty($itemAdicionado->{self::getNomeCampo($nomeCampo)})) {
            throw new Exception("O campo $descricaoCampo é obrigatório!");
            return false;
        }

        return true;
    }

    /**
     * Carrega os campos do item para a tela
     * @param array $param
     */
    public static function editar($param)
    {
        $campos = [
            self::getNomeCampo('id') => $param[self::getNomeCampo('id')],
            self::getNomeCampo('uniqid') => $param[self::getNomeCampo('uniqid')]
        ];

        foreach (self::getCampos() as $campo) {
            $campos[self::getNomeCampo($campo)] = $param[self::getNomeCampo($campo)] ?? null;
        }


        TForm::sendData(self::getNomeForm(), $campos, false, false);

        $nomeCampoPrincipal = self::getNomeCampo(self::getCampos()[0]);

        TScript::create("document.getElementById('{$nomeCampoPrincipal}').focus();");
    }

    /**
     * Exclui um item da datagrid
     * @param array $param
     */
    public static function excluir($param)
    {
        TDataGrid::removeRowById(self::getNomeDatagrid(), $param[self::getNomeCampo('uniqid')]);
    }

    /**
     * Cria os campos e a datagrid para serem consumidos pelo mestre
     * @param BootstrapFormBuilder $form
     * @param BootstrapDatagridWrapper $datagrid
     * @return array ['form' => $form, 'datagrid' => $datagrid]
     */
    static function criar(&$form, &$datagrid)
    {
        $form->addFields([new TFormSeparator(get_called_class()::getNomeDetalhe())]);

        get_called_class()::criarCampos($form);

        $datagrid =  get_called_class()::criarDatagrid($datagrid);
        $form->addFields([$datagrid]);

        return ['form' => $form, 'datagrid' => $datagrid];
    }

    /**
     * Valida se há duplicidade de valor na datagrid, retornando true caso for válido (sem duplicidade)
     * @param array $param
     * @param String $campo
     * @param $valor
     * @return bool 
     */
    protected static function validarDuplicidadeDeValorDatagrid(object $itemAdicionado, String $campo)
    {

        $itemAdicionado = (array) $itemAdicionado;

        if (!empty($itemAdicionado[self::getNomeCampoDatagrid($campo)])) {
            foreach ($itemAdicionado[self::getNomeCampoDatagrid($campo)] as $key => $value) {
                if ($itemAdicionado[self::getNomeCampo($campo)] != $value)
                    continue;

                $snEditandoOMesmo = $itemAdicionado[self::getNomeCampoDatagrid('uniqid')][$key] == $itemAdicionado[self::getNomeCampo('uniqid')];
                if (!$snEditandoOMesmo)
                    return false;
            }
        }
        return true;
    }

    protected static function removerItensBD($itensSalvosAnteriormente = [], $itensASeremMantidos = [])
    {
        if (count($itensSalvosAnteriormente) > 0) {
            foreach ($itensSalvosAnteriormente as $item) {
                if (!in_array($item->id, $itensASeremMantidos)) {
                    $item->delete();
                }
            }
        }
    }

    static function carregar($mestreId, &$datagrid)
    {
        $model = get_called_class()::getModel();
        $campoMestre = get_called_class()::getCampos()[1];
        $itens = $model::where($campoMestre, '=', $mestreId)->get();

        foreach ($itens as $item) {

            get_called_class()::formatarCamposCarregar($item);

            $itemDatagrid = [
                self::getNomeCampo('id') => $item->id,
            ];

            foreach (get_called_class()::getCampos() as $campo) {
                $itemDatagrid[get_called_class()::getNomeCampo($campo)] = $item->{$campo};
            }
            get_called_class()::adicionarItemDatagrid((object) $itemDatagrid, $datagrid);
        }
    }

    protected static function formatarCamposCarregar(TRecord &$item) {}

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
            $itemDatagrid[self::getNomeCampo($campo)] = $itemAdicionado->{self::getNomeCampo($campo)};
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
}
