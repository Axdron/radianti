<?php

namespace Axdron\Radianti\Componentes;

use Adianti\Widget\Form\TDateTime;

/**
 * Class RadiantiElementoDataHora
 *
 * Componente TDateTime com máscara padrão (dd/mm/yyyy hh:ii) e métodos utilitários
 * para definir valores comuns (data/hora atual e primeiro dia do mês).
 *
 * @package Axdron\Radianti\Componentes
 */
class RadiantiElementoDataHora extends TDateTime
{
    /**
     * RadiantiElementoDataHora constructor.
     *
     * @param string $nome Nome do campo
     */
    public function __construct(string $nome)
    {
        parent::__construct($nome);
        $this->setMask('dd/mm/yyyy hh:ii');
        $this->setDatabaseMask('yyyy-mm-dd hh:ii');
        $this->setSize('100%');
    }

    /**
     * Define o valor do campo como a data atual (meia-noite).
     *
     * @return string Valor atribuído (formato Y-m-d 00:00)
     */
    public function definirValorComoHoje(): string
    {
        $valor = date('Y-m-d 00:00');
        $this->setValue($valor);
        return $valor;
    }

    /**
     * Define o valor do campo como a data e hora atuais (precisão em minutos).
     *
     * @return string Valor atribuído (formato Y-m-d H:i)
     */
    public function definirValorComoAgora(): string
    {
        $valor = date('Y-m-d H:i');
        $this->setValue($valor);
        return $valor;
    }

    /**
     * Define o valor do campo como o primeiro dia do mês atual, com hora zero.
     *
     * @return string Valor atribuído (formato Y-m-01 00:00)
     */
    public function definirValorComoPrimeiroDiaMes(): string
    {
        $valor = date('Y-m-01 00:00');
        $this->setValue($valor);
        return $valor;
    }
}
