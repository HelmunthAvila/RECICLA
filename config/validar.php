<?php
/**
 * RECICLA+ | Validador de formularios (RNF-04: validar los datos enviados)
 */
declare(strict_types=1);

final class Validador
{
    private array $errores = [];

    public function requerido(?string $valor, string $campo, int $min = 1, int $max = 255): self
    {
        $v = trim((string) $valor);
        if ($v === '') {
            $this->errores[$campo] = 'El campo es obligatorio.';
        } elseif (mb_strlen($v) < $min) {
            $this->errores[$campo] = "Debe tener al menos $min caracteres.";
        } elseif (mb_strlen($v) > $max) {
            $this->errores[$campo] = "No debe superar $max caracteres.";
        }
        return $this;
    }

    public function email(?string $valor, string $campo = 'email'): self
    {
        $v = trim((string) $valor);
        if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->errores[$campo] = 'Correo electrónico no válido.';
        }
        return $this;
    }

    public function documento(?string $valor, string $campo = 'documento'): self
    {
        $v = preg_replace('/\s+/', '', (string) $valor);
        if ($v === '' || !preg_match('/^[0-9]{6,15}$/', (string) $v)) {
            $this->errores[$campo] = 'Documento inválido (solo números, entre 6 y 15 dígitos).';
        }
        return $this;
    }

    public function telefono(?string $valor, string $campo = 'telefono', bool $obligatorio = false): self
    {
        $v = trim((string) $valor);
        if ($v === '' && !$obligatorio) {
            return $this;
        }
        if (!preg_match('/^[0-9+\s\-()]{7,20}$/', $v)) {
            $this->errores[$campo] = 'Teléfono inválido (7 a 20 dígitos).';
        }
        return $this;
    }

    public function password(?string $valor, string $campo = 'password', int $min = 8): self
    {
        $v = (string) $valor;
        if (mb_strlen($v) < $min) {
            $this->errores[$campo] = "La contraseña debe tener al menos $min caracteres.";
        } elseif (!preg_match('/[A-Za-z]/', $v) || !preg_match('/[0-9]/', $v)) {
            $this->errores[$campo] = 'La contraseña debe combinar letras y números.';
        }
        return $this;
    }

    public function fecha(?string $valor, string $campo, bool $futura = false): self
    {
        $v = (string) $valor;
        $d = DateTime::createFromFormat('Y-m-d', $v);
        if (!$d || $d->format('Y-m-d') !== $v) {
            $this->errores[$campo] = 'Fecha inválida.';
        } elseif ($futura && $v < date('Y-m-d')) {
            $this->errores[$campo] = 'La fecha no puede ser anterior a hoy.';
        }
        return $this;
    }

    public function numero(?string $valor, string $campo, float $min = 0.01, float $max = 100000): self
    {
        $v = str_replace(',', '.', trim((string) $valor));
        if (!is_numeric($v)) {
            $this->errores[$campo] = 'Debe ingresar un valor numérico.';
        } elseif ((float) $v < $min || (float) $v > $max) {
            $this->errores[$campo] = "El valor debe estar entre $min y $max.";
        }
        return $this;
    }

    public function enLista(?string $valor, array $permitidos, string $campo): self
    {
        if (!array_key_exists((string) $valor, $permitidos) && !in_array($valor, $permitidos, true)) {
            $this->errores[$campo] = 'Opción no válida.';
        }
        return $this;
    }

    public function error(string $campo, string $mensaje): self
    {
        $this->errores[$campo] = $mensaje;
        return $this;
    }

    public function ok(): bool
    {
        return $this->errores === [];
    }

    public function errores(): array
    {
        return $this->errores;
    }

    public function primero(): string
    {
        return (string) reset($this->errores) ?: 'Revise los datos del formulario.';
    }
}
