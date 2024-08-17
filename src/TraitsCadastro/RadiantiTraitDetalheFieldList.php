<?php

namespace Axdron\Radianti\TraitsCadastro;

use Adianti\Widget\Form\TFieldList;

trait RadiantiTraitDetalheFieldList
{

    use RadiantiTraitFormatacaoDetalhes;

    abstract protected static function getNomeTelaPrincipal(): string;
    abstract protected static function getNomeForm(): string;

    protected static function getSnPermiteExcluir(): bool
    {
        return true;
    }

    protected static function getNomeFieldList(): string
    {
        return 'fieldlist_' . self::formatarNomeDetalhe(true);
    }

    /**
     * Cria a FieldList
     * @param $form
     */
    protected function criarFieldList(&$form)
    {
        $this->fieldlist = new TFieldList;
        $this->fieldlist->generateAria();
        $this->fieldlist->width = '100%';
        $this->fieldlist->name = self::getNomeFieldList();


        $this->criarCamposFieldList($form);

        if (!self::getSnPermiteExcluir())
            $this->fieldlist->disableRemoveButton();

        $this->fieldlist->addHeader();

        $form->addContent([$this->fieldlist]);
    }

    /**
     * Cria os campos do FieldList
     * 
     * Exemplo:
     * protected function criarCamposFieldList(&$form){
     *     $produto_id = new THidden('produto_id[]');
     *     $this->fieldlist->addField('Produto', $produto_id, ['100%']);
     *     $form->addField($produto_id);
     * 
     *     $referencia = new TEntry('referencia[]'); 
     *     $referencia->setProperty('readonly', true);
     *     $this->fieldlist->addField('Referência', $referencia, ['100%']);
     *     $form->addField($referencia);
     * 
     *     $quantidade_perda = new TEntry('quantidade_perda[]');
     *     $quantidade_perda->setMask('9!', true);
     *     $this->fieldlist->addField('Quantidade Perda', $quantidade_perda, ['100%']);
     *     $form->addField($quantidade_perda);
     * 
     *     ...Demais campos fieldlist...
     * }
     */
    abstract protected function criarCamposFieldList(&$form);


    /**
     * Carrega o detalhe do BD
     * @param $mestreId
     * 
     * Exemplo:
     * static function carregar($mestreId)
     * {
     *     $model = get_called_class()::getModel();
     * 
     *     $itens = $model::where(CAMPO_MESTRE, '=', $mestreId)->get();
     * 
     *     foreach ($itens as $item) {
     *         $itemFieldList->produto_id = $item->produto_id;
     *         $itemFieldList->referencia_id = $item->produto->referencia;
     *         ...
     * 
     *         $this->fieldlist->addDetail($itemFieldList);
     *     }
     * }
     * 
     */
    abstract function carregar($mestreId);
}
