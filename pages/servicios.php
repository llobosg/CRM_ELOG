<?php
require_once __DIR__ . '/../config.php';

// Solo mostrar si viene concatenado
$concatenado = $_GET['concatenado'] ?? '';
if (!$concatenado) {
    die("<script>alert('Falta código del prospecto'); history.back();</script>");
}

if ($_POST && isset($_POST['save_service'])) {
    try {
        $pdo->beginTransaction();

        $concatenado = $_POST['concatenado'] ?? '';
        $stmt = $pdo->prepare("SELECT id_ppl FROM prospectos WHERE concatenado = ?");
        $stmt->execute([$concatenado]);
        $prospecto = $stmt->fetch();
        if (!$prospecto) throw new Exception("Prospecto no encontrado");
        $id_prospect = $prospecto['id_ppl'];

        // Generar id_srvc: concatenado + correlativo
        $stmt_last = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(id_srvc, '-', -1) AS UNSIGNED)) as max_id FROM servicios WHERE id_prospect = ?");
        $stmt_last->execute([$id_prospect]);
        $last = $stmt_last->fetch();
        $correlativo = str_pad(($last['max_id'] ?? 0) + 1, 2, '0', STR_PAD_LEFT);
        $id_srvc = "{$concatenado}-{$correlativo}";

        // Insertar servicio
        $stmt_serv = $pdo->prepare("INSERT INTO servicios (
            id_srvc, id_prospect, servicio, nombre_corto, tipo, trafico, sub_trafico,
            base_calculo, moneda, tarifa, iva, estado, costo, venta,
            costogastoslocalesdestino, ventasgastoslocalesdestino
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt_serv->execute([
            $id_srvc, $id_prospect,
            $_POST['servicio'] ?? '',
            $_POST['nombre_corto'] ?? '',
            $_POST['tipo'] ?? '',
            $_POST['trafico'] ?? '',
            $_POST['sub_trafico'] ?? '',
            $_POST['base_calculo'] ?? '',
            $_POST['moneda'] ?? 'CLP',
            $_POST['tarifa'] ?? 0,
            $_POST['iva'] ?? 19,
            $_POST['estado'] ?? 'Activo',
            $_POST['costo'] ?? 0,
            $_POST['venta'] ?? 0,
            $_POST['costogastoslocalesdestino'] ?? 0,
            $_POST['ventasgastoslocalesdestino'] ?? 0,
        ]);

        $pdo->commit();
        // Responder en JSON para que el modal cierre y actualice
        echo "<script>
            window.opener?.actualizarServicios?.();
            window.close();
        </script>";
        exit;
    } catch (Exception $e) {
        echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
// Si se accede directamente (GET), redirigir o mostrar error
header('Location: ../index.php?page=prospectos');
exit;
?>