<?php

namespace Axdron\Radianti\Services;

use Adianti\Registry\TSession;

abstract class RadiantiSessaoService
{

    protected static $instanciaSingleton;
    protected static $usuarioLogin;
    protected static $usuarioId;

    protected function __construct() {}

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
