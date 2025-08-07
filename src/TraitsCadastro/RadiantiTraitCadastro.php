<?php

namespace Axdron\Radianti\TraitsCadastro;

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TRecord;
use Adianti\Widget\Container\TNotebook;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Wrapper\BootstrapNotebookWrapper;
use Axdron\Radianti\Services\RadiantiNavegacao;
use Axdron\Radianti\Services\RadiantiQuestionService\RadiantiQuestionService;
use Axdron\Radianti\Services\RadiantiTransaction;
use Exception;

trait RadiantiTraitCadastro
{
    protected BootstrapFormBuilder $formCadastro;
    protected TRecord $objetoEdicao;

    static function getNomeForm(): string
    {
        $className = get_called_class();
        $snakeCaseName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        return $snakeCaseName . '_form';
    }

    protected static function getArquivoMenu(): string
    {
        return 'menu.xml';
    }

    abstract protected static function getTitulo(): string;
    abstract protected static function getNomeTelaListagem(): string;
    abstract protected static function getModel(): string;

    public function __construct($param)
    {
        parent::__construct();

        if ($this instanceof TWindow) {
            $this->setTitle(get_called_class()::getTitulo());
            parent::setSize(0.9, null);
        }

        parent::add($this->criarTela($param));
    }

    public function criarTela($param)
    {
        if (!empty($param['id'])) {
            $this->carregarObjetoEdicao($param['id']);
        }

        $this->criarFormularioMestre();

        $campoOcultoOrigem = new THidden('snOrigemListagem');
        $campoOcultoOrigem->setValue($param['snOrigemListagem'] ?? true);
        $this->formCadastro->addFields([$campoOcultoOrigem]);

        $this->criarDetalhes($param);

        $this->adicionarAcoesPadraoFormularioMestre();
        $this->adicionarAcoesExtrasFormularioMestre();

        $notebook = new BootstrapNotebookWrapper(new TNotebook);
        $notebook->appendPage('Cadastro', $this->formCadastro);

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb(get_called_class()::getArquivoMenu(), get_called_class()::getNomeTelaListagem()));
        $vbox->add($notebook);

        return $vbox;
    }

    private function carregarObjetoEdicao($id)
    {
        $this->objetoEdicao = RadiantiTransaction::encapsularTransacao(function () use ($id) {
            $model = get_called_class()::getModel();

            $objeto = new $model($id);

            if (!$objeto) {
                AdiantiCoreApplication::loadPage(get_called_class()::getNomeTelaListagem(), 'carregar');
                throw new Exception('Registro não encontrado!');
            }

            $this->tratarObjetoCarregado($objeto);

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

    /**
     * Cria os detalhes do formulário.
     * 
     * Este método deve ser implementado para definir os detalhes do formulário.
     * 
     * @see carregarDetalhes
     */
    protected function criarDetalhes($param) {}

    /**
     * Carrega os detalhes do formulário.
     * 
     * Este método deve ser implementado para carregar os detalhes do formulário.
     * 
     * @see criarDetalhes
     */
    protected function carregarDetalhes($idMestre) {}

    /**
     * Adiciona ações extras ao formulário mestre.
     * 
     * Exemplo:
     * 
     * $this->formCadastro->addAction('fazerAlgo', new TAction([$this, 'fazerAlgo']), 'fa:save green');
     */
    protected function adicionarAcoesExtrasFormularioMestre() {}

    protected function adicionarAcoesPadraoFormularioMestre()
    {
        $this->formCadastro->addAction('Salvar', new TAction([$this, 'salvar']), 'fa:save green');
        $this->formCadastro->addAction('Lista', new TAction([$this, 'confirmarVoltaListagem']), 'fa:table blue');
    }

    protected function confirmarVoltaListagem($param)
    {
        if (empty($param['sn_confirmado'])) {
            $this->formCadastro->setData($this->formCadastro->getData());
            $this->recarregarDatagridsDetalhes($param);
        }

        RadiantiQuestionService::confirmar(
            'Deseja voltar para a listagem sem salvar?',
            new TAction([get_called_class(), 'confirmarVoltaListagem']),
            function () {
                RadiantiNavegacao::carregarPagina(get_called_class()::getNomeTelaListagem(), 'carregar');
            },
            $param
        );
    }

    private function criarFormularioMestre()
    {
        $this->formCadastro = new BootstrapFormBuilder(get_called_class()::getNomeForm());

        if (!($this instanceof TWindow)) {
            $this->formCadastro->setFormTitle(get_called_class()::getTitulo());
        }

        $this->criarCamposFormularioMestre();
    }

    function abrirEdicao($param)
    {
        if (empty($param['id'])) {
            return null;
        }

        RadiantiTransaction::encapsularTransacao(function () use ($param) {
            $this->formCadastro->setData($this->objetoEdicao);
            $this->carregarDetalhes($param['id']);
        }, snAbrirTransacao: false);
    }

    public function salvar($param, $snEmiteMensagemSalvou = true, $snRedirecionaListagem = true)
    {
        try {
            return RadiantiTransaction::encapsularTransacao(
                function () use ($param, $snEmiteMensagemSalvou, $snRedirecionaListagem) {
                    $dadosFormulario = $this->formCadastro->getData();
                    $this->formCadastro->validate();

                    $model = get_called_class()::getModel();

                    $objeto = new $model($dadosFormulario->id ?? null);
                    $objetoOriginal = clone $objeto;
                    if (!($objeto instanceof TRecord)) {
                        throw new Exception('Objeto não é uma instância de TRecord!');
                    }

                    if (!$this->tratarDadosFormulario($dadosFormulario)) {
                        return false;
                    }

                    $objeto->fromArray((array) $dadosFormulario);
                    $objeto->store();

                    $this->salvarDetalhes($objeto, $param);

                    $this->executarAposSalvar($objetoOriginal, $objeto, $param);

                    $this->formCadastro->setData($objeto);

                    if ($snEmiteMensagemSalvou)
                        new TMessage('info', 'Salvou ' . get_called_class()::getTitulo() . ' com sucesso!');

                    if ($snRedirecionaListagem && ($param['snOrigemListagem'] ?? true))
                        AdiantiCoreApplication::loadPage(get_called_class()::getNomeTelaListagem(), 'carregar');

                    if ($this instanceof TWindow) {
                        parent::closeWindow();
                    }

                    return $objeto;
                },
                false
            );
        } catch (\Throwable $th) {
            $this->formCadastro->setData($this->formCadastro->getData());
            $this->recarregarDatagridsDetalhes($param);
            new TMessage('error', "Não foi possível salvar " . get_called_class()::getTitulo() . ":<br><br>" . $th->getMessage());
            return false;
        }
    }

    protected function executarAposSalvar($objetoAntesSalvar, $objetoAposSalvamento, $param) {}

    protected function salvarDetalhes(TRecord &$objetoMestre, $param) {}

    protected function recarregarDatagridsDetalhes($param) {}

    /**
     * Trata os dados do formulário antes de salvar.
     * @param object $dadosFormulario Referência aos dados do formulário.
     * @return bool Retorna true para continuar o salvamento, false para abortar.
     */
    protected function tratarDadosFormulario(object &$dadosFormulario): bool
    {
        return true;
    }

    protected function tratarObjetoCarregado(&$objeto) {}

    public static function abrirJanelaAvulsa($param)
    {
        $campoId = $param['campoId'];

        if (empty($campoId)) {
            throw new Exception('Campo do ID não informado!');
        }

        if ($campoId != 'somenteCadastro')
            RadiantiNavegacao::carregarPagina(get_called_class(), 'abrirEdicao', ['id' => $param[$campoId], 'snOrigemListagem' => false]);
        else
            RadiantiNavegacao::carregarPagina(get_called_class(), 'abrirEdicao', ['snOrigemListagem' => false]);
    }
}
