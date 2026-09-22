<?php
/**
 * RECICLA+ | Catálogo público de materiales (RF-04, RF-05)
 * Accesible desde el celular sin necesidad de iniciar sesión.
 */
declare(strict_types=1);

final class CatalogoController
{
    public function index(): void
    {
        $f = [
            'categoria_id' => get_int('categoria'),
            'q'            => trim((string) ($_GET['q'] ?? '')),
            'estado'       => 'activo',
        ];
        $res = Material::listar($f, max(1, get_int('pag', 1)), 12);
        vista('publico/materiales', [
            'titulo'     => 'Materiales reciclables',
            'res'        => $res,
            'filtros'    => $f,
            'categorias' => Material::categorias(true),
        ]);
    }

    public function detalle(): void
    {
        $id = get_int('id');
        $m = Material::obtener($id);
        if (!$m || $m['estado'] !== 'activo') {
            flash('warning', 'El material consultado no está disponible.');
            redirigir('materiales');
        }
        $relacionados = Material::listar(['categoria_id' => (int) $m['categoria_id'], 'estado' => 'activo'], 1, 4)['filas'];
        vista('publico/material_detalle', [
            'titulo'      => $m['nombre'],
            'm'           => $m,
            'relacionados' => $relacionados,
        ]);
    }
}
