<?php


namespace Axdron\Radianti\Services\RadiantiQuestionService;

use Adianti\Control\TAction;
use Adianti\Widget\Dialog\TInputDialog;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;
use Axdron\Radianti\Componentes\RadiantiElementoLabelExplicativa;

class RadiantiQuestionService
{
    public static function confirmar(
        string $mensagem,
        TAction $acao,
        callable $callback,
        $param = []
    ) {
        if (empty($param['sn_confirmado'])) {
            $param['sn_confirmado'] = 1;
            $acao->setParameters($param);
            new TQuestion($mensagem, $acao);
        } else {
            return call_user_func($callback, $param);
        }
    }

    /**
     * Pergunta campos de formulário
     * @param string $titulo Título da pergunta
     * @param string $explicao Explicação adicional para o usuário
     * @param TAction $acao Ação a ser executada ao confirmar
     * @param callable $callback Função de callback a ser chamada após confirmação
     * @param array $param Parâmetros adicionais para a ação e callback
     * @return mixed Retorna o resultado do callback após confirmação
     * @throws \Exception
     */
    public static function perguntarCampos(string $titulo, string $explicao, array $campos, TAction $acao, callable $callback, $param = [])
    {
        if (empty($param['sn_confirmado'])) {
            $form = new BootstrapFormBuilder('radianti_question_form');

            if (!empty($explicao)) {
                $explicacao = new RadiantiElementoLabelExplicativa($explicao);
                $form->add($explicacao);
            }

            foreach ($campos as $campo) {
                $form->addFields([new TLabel($campo->label)], [$campo->campo]);
            }

            $campoConfirmado = new THidden('sn_confirmado');
            $campoConfirmado->setValue(1);

            $form->addField($campoConfirmado);

            $form->addAction('Confirmar', $acao, 'fa:check green');
            $form->setData($param);
            new TInputDialog($titulo, $form);
        } else {
            return call_user_func($callback, $param);
        }
    }
}
