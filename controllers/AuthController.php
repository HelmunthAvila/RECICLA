<?php
/**
 * RECICLA+ | Autenticación: registro (RF-01), login (RF-02),
 * recuperación de contraseña (RF-03) y cierre de sesión.
 */
declare(strict_types=1);

final class AuthController
{
    /** Página de bienvenida (Mobile First). */
    public function inicio(): void
    {
        if (estaLogueado()) {
            redirigir(rolActual() === 'ciudadano' ? 'panel' : (rolActual() === 'empresa' ? 'e_panel' : 'a_panel'));
        }
        $categorias = Material::categorias(true);
        $materiales = Material::listar(['estado' => 'activo'], 1, 6)['filas'];
        vista('publico/inicio', [
            'titulo'     => 'RECICLA+ | Recicla desde tu celular',
            'categorias' => $categorias,
            'materiales' => $materiales,
        ]);
    }

    public function loginForm(): void
    {
        $bloqueo = $this->bloqueoActivo();
        vista('auth/login', ['titulo' => 'Iniciar sesión', 'bloqueo' => $bloqueo]);
    }

    /** RF-02: identifica automáticamente el rol del usuario. */
    public function login(): void
    {
        if ($this->bloqueoActivo()) {
            flash('danger', 'Demasiados intentos fallidos. Espere 5 minutos e intente de nuevo.');
            redirigir('login');
        }
        $email = post('email');
        $pass  = (string) ($_POST['password'] ?? '');

        $v = new Validador();
        $v->requerido($email, 'email')->email($email);
        $v->requerido($pass, 'password');
        if (!$v->ok()) {
            flash('warning', $v->primero());
            redirigir('login');
        }

        $u = Usuario::buscarPorEmail($email);
        if (!$u || !password_verify($pass, $u['password'])) {
            $this->registrarFallo();
            flash('danger', 'Correo o contraseña incorrectos.');
            redirigir('login');
        }
        if ($u['estado'] !== 'activo') {
            flash('warning', 'Su cuenta está inactiva. Comuníquese con el administrador del sistema.');
            redirigir('login');
        }

        unset($_SESSION['login_intentos'], $_SESSION['login_primer_fallo'], $_SESSION['login_bloqueo_hasta']);
        iniciarSesionUsuario($u);
        flash('success', '¡Bienvenido(a), ' . $u['nombres'] . '!');
        redirigir(match ($u['rol']) {
            'ciudadano'     => 'panel',
            'empresa'       => 'e_panel',
            default         => 'a_panel',
        });
    }

    public function registroForm(): void
    {
        vista('auth/registro', ['titulo' => 'Crear cuenta', 'ciudades' => Solicitud::ciudadesSugeridas()]);
    }

    /** RF-01: registro de ciudadano con validación de correo no registrado. */
    public function registro(): void
    {
        $d = [
            'nombres'   => post('nombres'),
            'apellidos' => post('apellidos'),
            'documento' => post('documento'),
            'telefono'  => post('telefono'),
            'email'     => post('email'),
            'password'  => (string) ($_POST['password'] ?? ''),
            'confirmar' => (string) ($_POST['confirmar'] ?? ''),
            'direccion' => post('direccion'),
            'barrio'    => post('barrio'),
            'ciudad'    => post('ciudad'),
        ];

        $v = new Validador();
        $v->requerido($d['nombres'], 'nombres', 3, 80)
          ->requerido($d['apellidos'], 'apellidos', 3, 80)
          ->documento($d['documento'])
          ->telefono($d['telefono'], 'telefono', true)
          ->requerido($d['email'], 'email')->email($d['email'])
          ->password($d['password'])
          ->requerido($d['direccion'], 'direccion', 5, 160)
          ->requerido($d['barrio'], 'barrio', 3, 60)
          ->requerido($d['ciudad'], 'ciudad', 3, 60);

        if ($d['password'] !== $d['confirmar']) {
            $v->error('confirmar', 'Las contraseñas no coinciden.');
        }
        if (Usuario::emailRegistrado($d['email'])) {
            $v->error('email', 'Este correo ya está registrado. Inicie sesión o recupere su contraseña.');
        }
        if (Usuario::documentoRegistrado($d['documento'])) {
            $v->error('documento', 'Este documento ya tiene una cuenta registrada.');
        }
        if (!$v->ok()) {
            flash('danger', $v->primero());
            $_SESSION['old'] = $d;
            redirigir('registro');
        }

        $id = Usuario::crearCiudadano($d);
        $u  = Usuario::buscarPorId($id);
        unset($_SESSION['old']);
        iniciarSesionUsuario($u);
        notificar($id, '¡Bienvenido a RECICLA+!',
            'Ya puedes consultar materiales y solicitar la recolección a domicilio.', 'panel');
        flash('success', 'Cuenta creada correctamente. ¡Ya puedes solicitar tu primera recolección!');
        redirigir('publicar');
    }

    public function recuperarForm(): void
    {
        vista('auth/recuperar', ['titulo' => 'Recuperar contraseña']);
    }

    /**
     * RF-03: genera el token de recuperación.
     * En este entorno (WAMP local) no hay servidor de correo configurado:
     * el enlace se registra en el log y se muestra para pruebas.
     */
    public function recuperarEnviar(): void
    {
        $email = post('email');
        $v = new Validador();
        $v->requerido($email, 'email')->email($email);
        if (!$v->ok()) {
            flash('warning', $v->primero());
            redirigir('recuperar');
        }
        $u = Usuario::buscarPorEmail($email);
        if ($u) {
            $token = bin2hex(random_bytes(32));
            Usuario::guardarToken((int) $u['id'], $token);
            $enlace = url('restablecer', ['token' => $token]);
            error_log('[RECICLA+] Enlace de recuperación para ' . $email . ' -> ' . $enlace);
            flash('success', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.');
            // Entorno local sin SMTP: se entrega el enlace en pantalla para poder probar el flujo.
            flash('info', 'Modo local: enlace de restablecimiento -> ' . $enlace);
        } else {
            flash('success', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.');
        }
        redirigir('login');
    }

    public function restablecer(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        $u = $token !== '' ? Usuario::buscarPorToken($token) : null;
        if (!$u) {
            flash('danger', 'El enlace de recuperación no es válido o ya expiró.');
            redirigir('recuperar');
        }
        vista('auth/restablecer', ['titulo' => 'Nueva contraseña', 'token' => $token]);
    }

    public function guardarPassword(): void
    {
        $token = post('token');
        $u = Usuario::buscarPorToken($token);
        if (!$u) {
            flash('danger', 'El enlace de recuperación no es válido o ya expiró.');
            redirigir('recuperar');
        }
        $v = new Validador();
        $v->password(post('password'));
        if (post('password') !== post('confirmar')) {
            $v->error('confirmar', 'Las contraseñas no coinciden.');
        }
        if (!$v->ok()) {
            flash('warning', $v->primero());
            redirigir('restablecer', ['token' => $token]);
        }
        Usuario::cambiarPassword((int) $u['id'], post('password'));
        flash('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
        redirigir('login');
    }

    public function logout(): void
    {
        cerrarSesion();
        flash('info', 'Sesión cerrada correctamente.');
        redirigir('inicio');
    }

    // ---------------------------------------------------------- fuerza bruta
    private function bloqueoActivo(): bool
    {
        return isset($_SESSION['login_bloqueo_hasta']) && time() < (int) $_SESSION['login_bloqueo_hasta'];
    }

    private function registrarFallo(): void
    {
        if (empty($_SESSION['login_primer_fallo']) || time() - (int) $_SESSION['login_primer_fallo'] > 900) {
            $_SESSION['login_primer_fallo'] = time();
            $_SESSION['login_intentos'] = 0;
        }
        $_SESSION['login_intentos'] = (int) ($_SESSION['login_intentos'] ?? 0) + 1;
        if ($_SESSION['login_intentos'] >= LOGIN_MAX_INTENTOS) {
            $_SESSION['login_bloqueo_hasta'] = time() + LOGIN_BLOQUEO_SEG;
        }
    }
}
