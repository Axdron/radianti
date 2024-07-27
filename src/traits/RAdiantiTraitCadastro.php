<?php

use Adianti\Control\TAction;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TRecord;
use Adianti\Widget\Container\TNotebook;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Wrapper\BootstrapNotebookWrapper;

trait RAdiantiTraitCadastro
{
    protected BootstrapFormBuilder $formCadastro;
    protected TRecord $objetoEdicao;

    abstract static function getNomeForm(): string;
    abstract protected static function getTitulo(): string;
    abstract protected static function getNomeTelaListagem(): string;
    abstract protected static function getModel(): string;

    public function __construct($param)
    {
        parent::__construct();

        parent::add($this->criarTela($param));
    }

    public function criarTela($param)
    {
        if (!empty($param['id'])) {
            $this->carregarObjetoEdicao($param['id']);
        }

        $this->criarFormularioMestre();

        $this->criarDetalhes();

        $this->adicionarAcoesPadraoFormularioMestre();
        $this->adicionarAcoesExtrasFormularioMestre();

        $notebook = new BootstrapNotebookWrapper(new TNotebook);
        $notebook->appendPage('Cadastro', $this->formCadastro);

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', get_called_class()::getNomeTelaListagem()));
        $vbox->add($notebook);

        return $vbox;
    }

    private function carregarObjetoEdicao($id)
    {
        $this->objetoEdicao = RAdiantiTransaction::encapsularTransacao(function () use ($id) {
            $model = get_called_class()::getModel();

            $objeto = new $model($id);

            if (!$objeto) {
                AdiantiCoreApplication::loadPage(get_called_class()::getNomeTelaListagem(), 'carregar');
                throw new Exception('Registro não encontrado!');
            }

            return $objeto;
        });

        return $this->objetoEdicao;
    }


    /**
     * Cria os campos do formulário mestre
     * @return void
     * 
     * Exemplo:
     * 
     * $id = new TEntry('id');
     * $id->setEditable(false);
     * $fornecedor = Fornecedor::criarCampoBusca();
     * $fornecedor->addValidation('Fornecedor', new TRequiredValidator());
     * 
     * $this->formCadastro->addFields([new TLabel('ID')], [$id], [new TLabel('Fornecedor')], [$fornecedor]);
     */
    abstract protected function criarCamposFormularioMestre();

    protected function criarDetalhes()
    {
    }

    protected function carregarDetalhes($idMestre)
    {
    }


    protected function adicionarAcoesExtrasFormularioMestre()
    {
    }

    protected function adicionarAcoesPadraoFormularioMestre()
    {
        $this->formCadastro->addAction('Salvar', new TAction([$this, 'salvar']), 'fa:save green');
        $this->formCadastro->addActionLink('Lista', new TAction([get_called_class()::getNomeTelaListagem(), 'carregar']), 'fa:table blue');
    }

    private function criarFormularioMestre()
    {
        $this->formCadastro = new BootstrapFormBuilder(get_called_class()::getNomeForm());
        $this->formCadastro->setFormTitle(get_called_class()::getTitulo());

        $this->criarCamposFormularioMestre();
    }

    function abrirEdicao($param)
    {
        if (empty($param['id'])) {
            return null;
        }


        RAdiantiTransaction::encapsularTransacao(function () use ($param) {
            $this->formCadastro->setData($this->objetoEdicao);
            $this->carregarDetalhes($param['id']);
        }, snAbrirTransacao: false);
    }

    public function salvar($param, $snEmiteMensagemSalvou = true, $snRedirecionaListagem = true)
    {
        try {
            return RAdiantiTransaction::encapsularTransacao(
                function () use ($param, $snEmiteMensagemSalvou, $snRedirecionaListagem) {
                    $dadosFormulario = $this->formCadastro->getData();
                    $this->formCadastro->validate();

                    $model = get_called_class()::getModel();

                    $objeto = new $model($dadosFormulario->id);
                    $objetoOriginal = clone $objeto;
                    if (!($objeto instanceof TRecord)) {
                        throw new Exception('Objeto não é uma instância de TRecord!');
                    }

                    $objeto->fromArray((array) $dadosFormulario);
                    $objeto->store();

                    $this->salvarDetalhes($objeto, $param);

                    $this->executarAposSalvar($objetoOriginal, $objeto);

                    $this->formCadastro->setData($objeto);

                    if ($snEmiteMensagemSalvou)
                        new TMessage('info', 'Salvou ' . get_called_class()::getTitulo() . ' com sucesso!');

                    if ($snRedirecionaListagem)
                        AdiantiCoreApplication::loadPage(get_called_class()::getNomeTelaListagem(), 'carregar');

                    return $objeto;
                },
                false
            );
        } catch (\Throwable $th) {
            $this->formCadastro->setData($this->formCadastro->getData());
            $this->recarregarDatagridsDetalhes($param);
            new TMessage('error', "Não foi possível salvar " . get_called_class()::getTitulo() . ": " . $th->getMessage());
            return false;
        }
    }

    protected function executarAposSalvar($objetoAntesSalvar, $objetoAposSalvamento)
    {
    }



    protected function salvarDetalhes(TRecord $objetoMestre, $param)
    {
    }

    protected function recarregarDatagridsDetalhes($param)
    {
    }
}
