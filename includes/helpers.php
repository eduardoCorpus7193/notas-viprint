<?php

function formatearFecha($fecha) {
    if (empty($fecha) || $fecha === '0000-00-00') {
        return '—';
    }

    return date('d/m/Y', strtotime($fecha));
}

function claseEstado($estado) {
    return match ($estado) {
        'pendiente' => 'status-pendiente',
        'en_proceso' => 'status-en_proceso',
        'terminado' => 'status-terminado',
        'entregado' => 'status-entregado',
        'cancelado' => 'status-cancelado',
        default => 'status-pendiente'
    };
}

function textoEstado($estado) {
    return match ($estado) {
        'en_proceso' => 'En proceso',
        default => ucfirst(str_replace('_', ' ', $estado))
    };
}