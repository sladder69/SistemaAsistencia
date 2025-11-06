<?php
session_start();

// Configuración de la base de datos (SIN CAMBIOS)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistema_profesores');

// Función para conectar a la base de datos (SIN CAMBIOS)
function conectarDB() {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    return $conexion;
}

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = trim($_POST['usuario_id']);
    $codigo_empleado = $_POST['codigo_empleado'];
    
    // Validaciones básicas
    if (empty($usuario_id) || empty($codigo_empleado)) {
        // CORRECCIÓN: Usar $usuario_id en la URL
        header('Location: login.html?error=' . urlencode('Todos los campos son obligatorios') . '&usuario_id=' . urlencode($usuario_id));
        exit();
    }
    
    // Conectar a la base de datos
    $conexion = conectarDB();
    
    // CORRECCIÓN: Seleccionar 'id' (para la sesión) y usar $usuario_id en el WHERE
    // Se asume que codigo_empleado es la contraseña
    $stmt = $conexion->prepare("SELECT id, usuario_id, codigo_empleado FROM profesores WHERE usuario_id = ?");
    $stmt->bind_param("s", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        
        // CORRECCIÓN: Verificar la contraseña. Se asume que codigo_empleado es la columna de la contraseña.
        // ADVERTENCIA: Esta verificación es insegura, en producción usa password_verify($codigo_empleado, $usuario['codigo_empleado'])
        if ($codigo_empleado === $usuario['codigo_empleado']) {
            // Iniciar sesión
            // Se asume que 'id' es la clave primaria y 'usuario_id' es la matrícula visible
            $_SESSION['usuario_id'] = $usuario['id']; 
            $_SESSION['matricula'] = $usuario['usuario_id']; 
            $_SESSION['logged_in'] = true;
            
            // Redirigir al dashboard
            header('Location: index.html');
            exit();
        } else {
            // Contraseña incorrecta
            // CORRECCIÓN: Usar $usuario_id en la URL
            header('Location: login.html?error=' . urlencode('Contraseña incorrecta') . '&usuario_id=' . urlencode($usuario_id));
            exit();
        }
    } else {
        // Matrícula no encontrada
        // CORRECCIÓN: Usar $usuario_id en la URL
        header('Location: login.html?error=' . urlencode('Matrícula no encontrada') . '&usuario_id=' . urlencode($usuario_id));
        exit();
    }
    
    $stmt->close();
    $conexion->close();
} else {
    // Si no es POST, redirigir al login
    header('Location: login.html');
    exit();
}
?>