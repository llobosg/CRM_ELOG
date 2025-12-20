<?php
require_once '../config/db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

try {
    if ($action === 'save') {
        $data = $input;
        $id_transp_nac = $data['id_transp_nac'] ?? null;

        $id_prospect = intval($data['id_prospect']);
        $id_srvc = $data['id_srvc'];
        $moneda = in_array($data['moneda'], ['USD', 'EUR', 'CLP']) ? $data['moneda'] : 'CLP';
        $costo = floatval($data['costo']);
        $venta = floatval($data['venta']);
        $acepta = in_array($data['acepta'], ['Si', 'No']) ? $data['acepta'] : 'No';
        $afecto = in_array($data['afecto'], ['Si', 'No']) ? $data['afecto'] : 'No';

        $transportista = $data['transportista'] ?? '';
        $direc_retiro = $data['direc_retiro'] ?? '';
        $contacto_retiro = $data['contacto_retiro'] ?? '';
        $fono_retiro = $data['fono_retiro'] ?? '';
        $direc_entrega = $data['direc_entrega'] ?? '';
        $fono_entrega = $data['fono_entrega'] ?? '';
        $empresa_entrega = $data['empresa_entrega'] ?? '';
        $contacto_entrega = $data['contacto_entrega'] ?? '';

        if ($id_transp_nac) {
            // UPDATE
            $stmt = $pdo->prepare("
                UPDATE ro_transp_nac SET
                    id_prospect = ?, id_srvc = ?, moneda = ?, costo = ?, venta = ?,
                    acepta = ?, afecto = ?,
                    transportista = ?, direc_retiro = ?, contacto_retiro = ?, fono_retiro = ?,
                    direc_entrega = ?, fono_entrega = ?, empresa_entrega = ?, contacto_entrega = ?
                WHERE id_transp_nac = ?
            ");
            $stmt->execute([
                $id_prospect, $id_srvc, $moneda, $costo, $venta,
                $acepta, $afecto,
                $transportista, $direc_retiro, $contacto_retiro, $fono_retiro,
                $direc_entrega, $fono_entrega, $empresa_entrega, $contacto_entrega,
                $id_transp_nac
            ]);
            echo json_encode(['success' => true, 'message' => 'Registro actualizado.', 'id' => $id_transp_nac]);
        } else {
            // INSERT
            $stmt = $pdo->prepare("
                INSERT INTO ro_transp_nac (
                    id_prospect, id_srvc, moneda, costo, venta,
                    acepta, afecto,
                    transportista, direc_retiro, contacto_retiro, fono_retiro,
                    direc_entrega, fono_entrega, empresa_entrega, contacto_entrega
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id_prospect, $id_srvc, $moneda, $costo, $venta,
                $acepta, $afecto,
                $transportista, $direc_retiro, $contacto_retiro, $fono_retiro,
                $direc_entrega, $fono_entrega, $empresa_entrega, $contacto_entrega
            ]);
            $newId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Registro guardado.', 'id' => $newId]);
        }

    } elseif ($action === 'delete') {
        $id_transp_nac = intval($_GET['id']);
        $stmt = $pdo->prepare("DELETE FROM ro_transp_nac WHERE id_transp_nac = ?");
        $stmt->execute([$id_transp_nac]);
        echo json_encode(['success' => true, 'message' => 'Registro eliminado.']);

    } elseif ($action === 'get') {
        $id_srvc = $_GET['id_srvc'];
        $stmt = $pdo->prepare("SELECT * FROM ro_transp_nac WHERE id_srvc = ?");
        $stmt->execute([$id_srvc]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($record) {
            echo json_encode(['success' => true, 'data' => $record]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No encontrado.']);
        }
    } else {
        throw new Exception('Acción no válida.');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}