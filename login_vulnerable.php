<?php
// login_vulnerable.php
session_start(); // Iniciamos la sesión para mantener al usuario conectado

$conexion = new mysqli("localhost", "root", "", "seguridad_db");
$error = null;
$mensaje = null;

// ================= LÓGICA DE CERRAR SESIÓN =================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login_vulnerable.php");
    exit();
}

// ================= LÓGICA DE LOGIN =================
if (isset($_POST['login_username']) && isset($_POST['login_password'])) {
    $user = $_POST['login_username'];
    $pass = $_POST['login_password'];

    // LA VULNERABILIDAD PRINCIPAL
    $query = "SELECT * FROM usuarios WHERE username = '$user' AND password = '$pass'";
    $resultado = $conexion->query($query);

    if ($resultado && $resultado->num_rows > 0) {
        $_SESSION['usuario'] = $resultado->fetch_assoc();
    } else {
        $error = "Credenciales incorrectas.";
    }
}

// ================= LÓGICA CRUD (SOLO PARA ADMINS) =================
if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'Administrador') {
    
    // Acción: Agregar nuevo empleado
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $new_user = $_POST['new_username'];
        $new_pass = $_POST['new_password'];
        $new_rol = $_POST['new_rol'];
        
        $conexion->query("INSERT INTO usuarios (username, password, rol) VALUES ('$new_user', '$new_pass', '$new_rol')");
        $mensaje = "¡Nuevo usuario agregado exitosamente a la base de datos!";
    }

    // Acción: Eliminar empleado
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $del_id = (int)$_POST['delete_id'];
        
        // Evitamos que el admin se borre a sí mismo por accidente
        if ($del_id !== (int)$_SESSION['usuario']['id']) {
            $conexion->query("DELETE FROM usuarios WHERE id = $del_id");
            $mensaje = "Usuario eliminado del sistema.";
        } else {
            $error = "Acción bloqueada: No puedes eliminar tu propia cuenta activa.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Corporativo - Entorno Vulnerable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .login-container { max-width: 400px; margin-top: 10vh; }
        .dashboard-container { max-width: 900px; margin-top: 5vh; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['usuario'])): ?>
    <!-- ================= FORMULARIO DE LOGIN ================= -->
    <div class="container login-container">
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-header bg-dark text-white text-center py-3">
                <h4 class="mb-0"><i class="bi bi-shield-lock"></i> Acceso Corporativo</h4>
            </div>
            <div class="card-body p-4">
                <?php if($error): ?>
                    <div class="alert alert-danger text-center"> <?= $error ?> </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Usuario</label>
                        <input type="text" name="login_username" class="form-control" placeholder="Ej. empleado1" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Contraseña</label>
                        <input type="password" name="login_password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 fw-bold">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ================= PANEL DE CONTROL (DASHBOARD) ================= -->
    <div class="container dashboard-container">
        
        <?php if($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?= $mensaje ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-lg border-0 rounded-3 mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0">Portal Interno</h4>
                <div>
                    <span class="badge bg-primary fs-6 me-3">Rol: <?= $_SESSION['usuario']['rol'] ?></span>
                    <a href="login_vulnerable.php?logout=1" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Salir</a>
                </div>
            </div>
            
            <div class="card-body p-4">
                <h2 class="mb-4">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['username']) ?> 👋</h2>

                <?php if ($_SESSION['usuario']['rol'] === 'Administrador'): ?>
                    <!-- VISTA DE ADMINISTRADOR: GESTIÓN DE LA BASE DE DATOS (CRUD) -->
                    
                    <div class="row">
                        <!-- Columna Izquierda: Formulario para Agregar -->
                        <div class="col-md-4">
                            <div class="card bg-light border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-success"><i class="bi bi-person-plus"></i> Nuevo Empleado</h5>
                                    <hr>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="add">
                                        <div class="mb-2">
                                            <input type="text" name="new_username" class="form-control form-control-sm" placeholder="Usuario" required>
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" name="new_password" class="form-control form-control-sm" placeholder="Contraseña" required>
                                        </div>
                                        <div class="mb-3">
                                            <select name="new_rol" class="form-select form-select-sm">
                                                <option value="Usuario">Usuario</option>
                                                <option value="Gerente">Gerente</option>
                                                <option value="Administrador">Administrador</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm w-100">Registrar</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Tabla de Usuarios (La Base de Datos en vivo) -->
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-danger"><i class="bi bi-database"></i> Registros del Sistema</h5>
                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Usuario</th>
                                                    <th>Contraseña</th>
                                                    <th>Rol</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Consultamos todos los usuarios para llenar la tabla
                                                $todosLosUsuarios = $conexion->query("SELECT * FROM usuarios");
                                                while($row = $todosLosUsuarios->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td class="fw-bold"><?= htmlspecialchars($row['username']) ?></td>
                                                    <td class="text-muted"><?= htmlspecialchars($row['password']) ?></td>
                                                    <td><span class="badge bg-secondary"><?= $row['rol'] ?></span></td>
                                                    <td>
                                                        <form method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este registro de la base de datos?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Fin Row -->

                <?php else: ?>
                    <!-- VISTA DE USUARIO ESTÁNDAR -->
                    <div class="alert alert-info border-info">
                        <h5 class="alert-heading">Panel de Empleado</h5>
                        <p class="mb-0">Tu nivel de acceso es estándar. No tienes privilegios para ver o modificar la base de datos.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php endif; ?>

</body>
</html>