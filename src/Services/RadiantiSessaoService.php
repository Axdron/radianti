<?php

namespace Axdron\Radianti\Services;

use Adianti\Registry\TSession;

/**
 * Classe abstrata para gerenciamento de variáveis de sessão do usuário.
 * Utiliza o padrão singleton para garantir uma única instância.
 * Ideal para ser utilizada em conjunto com o RadiantiGerenciadorSessoes, reduzindo a necessidade de múltiplas consultas ao banco de dados.
 * Exemplo de uso:
 * ```
 * RadiantiSessaoService::salvarUsuarioId(1);
 * $usuarioId = RadiantiSessaoService::buscarUsuarioId();
 * RadiantiSessaoService::salvarUsuarioLogin('admin');
 * $usuarioLogin = RadiantiSessaoService::buscarUsuarioLogin();
 * ```
 */
abstract class RadiantiSessaoService
{

    protected static $instanciaSingleton;
    protected $usuarioLogin;
    protected $usuarioId;

    protected function __construct() {}

    /**
     * Retorna a instância singleton da classe.
     * @return static A instância singleton da classe.
     */
    public static function buscarInstanciaSingleton()
    {
        if (empty(self::$instanciaSingleton)) {
            $classe = get_called_class();
            self::$instanciaSingleton = new $classe();
        }
        return self::$instanciaSingleton;
    }

    static function salvarUsuarioId($id)
    {
        TSession::setValue('userid', $id);
        self::buscarInstanciaSingleton()->usuarioId = $id;
    }

    static function buscarUsuarioId()
    {
        $instancia = self::buscarInstanciaSingleton();
        if (empty($instancia->usuarioId))
            $instancia->usuarioId = TSession::getValue('userid');
        return $instancia->usuarioId;
    }

    static function salvarUsuarioLogin($login)
    {
        TSession::setValue('login', $login);
        self::buscarInstanciaSingleton()->usuarioLogin = $login;
    }

    static function buscarUsuarioLogin()
    {
        $instancia = self::buscarInstanciaSingleton();
        if (empty($instancia->usuarioLogin))
            $instancia->usuarioLogin = TSession::getValue('login');
        return $instancia->usuarioLogin;
    }
}
