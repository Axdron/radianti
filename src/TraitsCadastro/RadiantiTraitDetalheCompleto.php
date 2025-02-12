<?php

namespace Axdron\Radianti\TraitsCadastro;

use Adianti\Control\TAction;
use Adianti\Database\TRecord;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TFormSeparator;
use Adianti\Widget\Form\THidden;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;
use Exception;

trait RadiantiTraitDetalheCompleto
{

    use RadiantiTraitDetalheDatagrid;

    protected static function getSnDetalheObrigatorio(): bool
    {
        return false;
    }

    protected static function getSnCriaBotaoAdicionar(): bool
    {
        return true;
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

        TScript::create("document.getElementsByName('{$nomeCampoPrincipal}')[0].focus();");
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



        if (get_called_class()::getSnCriaIdUniqid()) {
            $campoUniqid = new THidden(self::getNomeCampo('uniqid'));
            $form->addFields([$campoUniqid]);

            $campoId = new THidden(self::getNomeCampo('id'));
            $form->addFields([$campoId]);
        }

        get_called_class()::criarCampos($form);

        if (get_called_class()::getSnCriaBotaoAdicionar()) {
            $botaoAdicionar = new TButton('adicionar' . self::formatarNomeDetalhe());
            $botaoAdicionar->setLabel('Adicionar');
            $botaoAdicionar->setImage('fa:plus-circle green');
            $botaoAdicionar->setAction(new TAction([get_called_class()::getNomeTelaPrincipal(), 'adicionar' . self::formatarNomeDetalhe()], ['static' => 1]), 'Adicionar');
            $form->addFields([], [$botaoAdicionar]);
        }

        $datagrid =  get_called_class()::criarDatagrid($datagrid);
        $form->addFields([$datagrid]);

        return ['form' => $form, 'datagrid' => $datagrid];
    }

    /**
     * Valida se há duplicidade de valor na datagrid, retornando true caso for válido (sem duplicidade), e false caso contrário. 
     * @param array $param
     * @param String $campo
     * @param $valor
     * @return bool 
     */
    protected static function validarDuplicidadeDeValorDatagrid(object $itemAdicionado, String $campo)
    {
        if (strpos($campo, get_called_class()::getPrefixoDetalhe()) === 0) {
            $campo = substr($campo, strlen(get_called_class()::getPrefixoDetalhe()));
        }

        if (strpos($campo, get_called_class()::getNomeDatagrid()) === 0) {
            $campo = substr($campo, strlen(get_called_class()::getNomeDatagrid()));
        }

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


    protected static function formatarCamposCarregar(TRecord &$item) {}
}
