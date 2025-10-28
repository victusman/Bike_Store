<?php
header('Content-Type: application/json; charset=utf-8');
$result = ['success'=>false,'data'=>null,'error'=>null];

try {
    // Obtener ruta de sesiones
    $savePath = ini_get('session.save_path');
    if (!$savePath) $savePath = sys_get_temp_dir();
    // En algunas configuraciones session.save_path puede incluir directives (N;path). Manejar eso.
    if (strpos($savePath, ';') !== false) {
        $parts = explode(';', $savePath);
        $savePath = end($parts);
    }
    $savePath = rtrim($savePath, "\\/");

    $files = @glob($savePath . DIRECTORY_SEPARATOR . 'sess_*');
    $sessions = [];
    if ($files && is_array($files)) {
        foreach ($files as $file) {
            if (!is_file($file)) continue;
            $content = @file_get_contents($file);
            $mtime = date('d/m H:i', filemtime($file));
            $id = basename($file);
            $id = preg_replace('/^sess_/', '', $id);
            $usuario = null;
            // Intentar capturar patrón simple: usuario|s:NN:"value";
            if ($content && preg_match('/usuario\|s:\d+:"([^"]+)";/', $content, $m)) {
                $usuario = $m[1];
            } else if ($content && preg_match('/"usuario";s:\d+:"([^"]+)";/', $content, $m2)) {
                $usuario = $m2[1];
            }
            $sessions[] = ['id'=>$id, 'usuario'=>$usuario, 'mtime'=>$mtime];
        }
    }
    $result['success'] = true;
    $result['data'] = ['count'=>count($sessions), 'sessions'=>$sessions];
} catch (Exception $e){
    $result['error'] = $e->getMessage();
}

echo json_encode($result);
