<?php
/**
 * UsuarioController
 * Maneja CRUD de usuarios
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../models/Sucursal.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/ValidationHelper.php';

class UsuarioController {
    
    /**
     * Listar usuarios
     */
    public function index() {
        AuthHelper::requireRole('Administrador');
        
        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->getAll();
        
        require_once __DIR__ . '/../views/usuarios/index.php';
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        AuthHelper::requireRole('Administrador');
        
        $rolModel = new Rol();
        $sucursalModel = new Sucursal();
        
        $roles = $rolModel->getAll();
        $sucursales = $sucursalModel->getAllActive();
        
        require_once __DIR__ . '/../views/usuarios/crear.php';
    }

    /**
     * Guardar usuario
     */
    public function guardar() {
        AuthHelper::requireRole('Administrador');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=usuarios');
            exit();
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $rolId = $_POST['rol_id'] ?? '';
        $sucursalId = $_POST['sucursal_id'] ?? null;

        // Validaciones
        $errores = [];

        if (!ValidationHelper::required($username)) {
            $errores[] = 'El nombre de usuario es obligatorio';
        }

        if (!ValidationHelper::minLength($username, 4)) {
            $errores[] = 'El nombre de usuario debe tener al menos 4 caracteres';
        }

        if (!ValidationHelper::required($password)) {
            $errores[] = 'La contraseña es obligatoria';
        }

        if (!ValidationHelper::minLength($password, 6)) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if ($password !== $passwordConfirm) {
            $errores[] = 'Las contraseñas no coinciden';
        }

        if (!ValidationHelper::positiveInteger($rolId)) {
            $errores[] = 'Debe seleccionar un rol válido';
        }

        // Verificar si username ya existe
        $usuarioModel = new Usuario();
        if ($usuarioModel->usernameExists($username)) {
            $errores[] = 'El nombre de usuario ya existe';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=usuarios/crear');
            exit();
        }

        $usuarioModel->Username = $username;
        $usuarioModel->Password = $password;
        $usuarioModel->RolId = $rolId;
        $usuarioModel->SucursalId = $sucursalId;

        if ($usuarioModel->create()) {
            SessionHelper::setFlash('success', 'Usuario creado exitosamente');
            header('Location: /vetalmacen/public/index.php?url=usuarios');
        } else {
            SessionHelper::setFlash('danger', 'Error al crear el usuario');
            header('Location: /vetalmacen/public/index.php?url=usuarios/crear');
        }
        exit();
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id) {
        AuthHelper::requireRole('Administrador');
        
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->getById($id);
        
        if (!$usuario) {
            SessionHelper::setFlash('danger', 'Usuario no encontrado');
            header('Location: /vetalmacen/public/index.php?url=usuarios');
            exit();
        }

        $rolModel = new Rol();
        $sucursalModel = new Sucursal();
        
        $roles = $rolModel->getAll();
        $sucursales = $sucursalModel->getAllActive();
        
        require_once __DIR__ . '/../views/usuarios/editar.php';
    }

    /**
     * Actualizar usuario
     */
    public function actualizar() {
        AuthHelper::requireRole('Administrador');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=usuarios');
            exit();
        }

        $id = $_POST['id'] ?? '';
        $username = $_POST['username'] ?? '';
        $rolId = $_POST['rol_id'] ?? '';
        $sucursalId = $_POST['sucursal_id'] ?? null;

        // Validaciones
        $errores = [];

        if (!ValidationHelper::positiveInteger($id)) {
            $errores[] = 'ID inválido';
        }

        if (!ValidationHelper::required($username)) {
            $errores[] = 'El nombre de usuario es obligatorio';
        }

        if (!ValidationHelper::minLength($username, 4)) {
            $errores[] = 'El nombre de usuario debe tener al menos 4 caracteres';
        }

        if (!ValidationHelper::positiveInteger($rolId)) {
            $errores[] = 'Debe seleccionar un rol válido';
        }

        // Verificar si username ya existe (excluyendo el actual)
        $usuarioModel = new Usuario();
        if ($usuarioModel->usernameExists($username, $id)) {
            $errores[] = 'El nombre de usuario ya existe';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=usuarios/editar/' . $id);
            exit();
        }

        $usuarioModel->Id = $id;
        $usuarioModel->Username = $username;
        $usuarioModel->RolId = $rolId;
        $usuarioModel->SucursalId = $sucursalId;

        if ($usuarioModel->update()) {
            SessionHelper::setFlash('success', 'Usuario actualizado exitosamente');
            header('Location: /vetalmacen/public/index.php?url=usuarios');
        } else {
            SessionHelper::setFlash('danger', 'Error al actualizar el usuario');
            header('Location: /vetalmacen/public/index.php?url=usuarios/editar/' . $id);
        }
        exit();
    }

    /**
     * Ver perfil del usuario actual
     */
    public function perfil() {
        AuthHelper::requireAuth();
        
        $user = SessionHelper::getUser();
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->getById($user['id']);
        
        require_once __DIR__ . '/../views/usuarios/perfil.php';
    }

    /**
     * Eliminar usuario
     */
    public function eliminar($id) {
        AuthHelper::requireRole('Administrador');
        
        // No permitir eliminar el usuario actual
        $currentUser = SessionHelper::getUser();
        if ($currentUser['id'] == $id) {
            SessionHelper::setFlash('danger', 'No puedes eliminar tu propio usuario');
            header('Location: /vetalmacen/public/index.php?url=usuarios');
            exit();
        }

        $usuarioModel = new Usuario();
        $usuarioModel->Id = $id;
        
        if ($usuarioModel->delete()) {
            SessionHelper::setFlash('success', 'Usuario eliminado exitosamente');
        } else {
            SessionHelper::setFlash('danger', 'Error al eliminar el usuario');
        }
        
        header('Location: /vetalmacen/public/index.php?url=usuarios');
        exit();
    }
}