<?php

namespace App\Contracts;

interface ProcedurePermissionsInterface
{
    /**
     * Возвращает массив вида ['methodName' => ['role1', 'role2']].
     */
    public function getMethodsPermissions(): array;
}
