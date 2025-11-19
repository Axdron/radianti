<?php

declare(strict_types=1);

namespace Axdron\Radianti\Interfaces;

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Widget\Base\TElement;
use Adianti\Wrapper\BootstrapFormBuilder;

/**
 * RadiantiJanelaPergunta
 * 
 * Cria uma janela de pergunta com suporte completo a close action (botão X)
 * Diferente do TQuestion, gerencia adequadamente o comportamento ao fechar a janela
 * 
 * @package Axdron\Radianti\Interfaces
 */
class RadiantiJanelaPergunta
{
    /**
     * Construtor
     * 
     * @param string $message Mensagem da pergunta
     * @param TAction $action_yes Ação ao clicar em "Sim" ou botão principal
     * @param TAction $action_no Ação ao clicar em "Não" ou ao fechar a janela (X)
     * @param string $title_msg Título da janela
     * @param string $label_yes Label do botão "Sim" (principal)
     * @param string $label_no Label do botão "Não" (secundário)
     */
    public function __construct(
        string $message,
        TAction $action_yes,
        TAction $action_no,
        string $title_msg = '',
        string $label_yes = '',
        string $label_no = ''
    ) {
        $title = !empty($title_msg) ? $title_msg : AdiantiCoreTranslator::translate('Question');
        $label_yes = !empty($label_yes) ? $label_yes : AdiantiCoreTranslator::translate('Yes');
        $label_no = !empty($label_no) ? $label_no : AdiantiCoreTranslator::translate('No');

        $window = TWindow::create($title, 0.6, null);

        $form = new BootstrapFormBuilder('form_question_window');

        $content = new TElement('div');
        $content->style = 'margin-bottom: 15px; padding: 10px; border-radius: 4px;';
        $content->add($message);

        $form->addContent([$content]);

        $btnYes = $form->addAction(
            $label_yes,
            $action_yes,
            'fas:check'
        );
        $btnYes->class = 'btn btn-primary';

        $btnNo = $form->addAction(
            $label_no,
            $action_no,
            'fas:times'
        );
        $btnNo->class = 'btn btn-secondary';

        $window->add($form);
        $window->setCloseAction($action_no);

        $window->show();
    }
}
