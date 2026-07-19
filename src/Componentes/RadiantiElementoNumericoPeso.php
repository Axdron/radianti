<?php

namespace Axdron\Radianti\Componentes;

/**
 * Elemento Numérico para Peso
 * 
 * 3 casas decimais com separador brasileiro (vírgula)
 */
class RadiantiElementoNumericoPeso extends RadiantiElementoNumerico
{
    public function __construct(string $nome, $snAtualizaAoEfetuarPost = true, $snPreenchimentoEsquerdaParaDireita = false, $snPermiteNegativo = false)
    {
        parent::__construct(
            $nome,
            casasDecimais: 3,
            separadorDecimais: ',',
            separadorMilhares: '.',
            snAtualizaAoEfetuarPost: $snAtualizaAoEfetuarPost,
            snPreenchimentoEsquerdaParaDireita: $snPreenchimentoEsquerdaParaDireita,
            snPermiteNegativo: $snPermiteNegativo
        );
    }
}
