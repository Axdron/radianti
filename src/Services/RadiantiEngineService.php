<?php

namespace Axdron\Radianti\Services;

/**
 * RadiantiEngineService
 *
 * Essa classe deve ser aplicada dentro do arquivo engine.php de modo a fazer validações de segurança importantes.
 * 
 * Validações:
 * - Verifica se o número de variáveis recebidas não excede o limite definido em `max_input_vars`. A propriedade `snMaxInputVarsValido` será `false` se o número de variáveis exceder o limite.
 */
class RadiantiEngineService
{
    public $maxInputVars;
    public $totalRequest;
    public $snMaxInputVarsValido = true;

    public function __construct()
    {
        $this->maxInputVars = ini_get('max_input_vars');
        $this->totalRequest = self::contarInputVars($_REQUEST);
        $this->validarMaxInputVars();
    }

    /**
     * Valida se o total de variáveis está dentro do limite configurado em `max_input_vars`.
     * Atualiza a propriedade `snMaxInputVarsValido` e retorna o resultado.
     *
     * @return bool
     */
    public function validarMaxInputVars(): bool
    {
        $this->snMaxInputVarsValido = true;

        if ($this->maxInputVars && $this->totalRequest > (int) $this->maxInputVars) {
            $this->snMaxInputVarsValido = false;
        }

        return $this->snMaxInputVarsValido;
    }

    /**
     * Conta recursivamente o total de variáveis recebidas em um array, considerando todos os elementos internos.
     * Útil para validação de max_input_vars e cenários com arrays aninhados.
     * @param array $input
     * @return int
     */
    private static function contarInputVars($input): int
    {
        $count = 0;
        foreach ($input as $item) {
            if (is_array($item)) {
                $count += self::contarInputVars($item);
            } else {
                $count++;
            }
        }

        return $count;
    }
}
