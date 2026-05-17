<?php
// login_vulnerable.php
session_start();

$conexion = new mysqli("localhost", "root", "", "seguridad_db");
$error = null;
$mensaje = null;

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login_vulnerable.php");
    exit();
}

if (isset($_POST['login_username']) && isset($_POST['login_password'])) {
    $user = $_POST['login_username'];
    $pass = $_POST['login_password'];

    // VULNERABILIDAD
    $query = "SELECT * FROM usuarios WHERE username = '$user' AND password = '$pass'";
    $resultado = $conexion->query($query);

    if ($resultado && $resultado->num_rows > 0) {
        $_SESSION['usuario'] = $resultado->fetch_assoc();
    } else {
        $error = "Credenciales incorrectas o usuario no encontrado.";
    }
}

if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'Administrador') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $new_user = $_POST['new_username'];
        $new_pass = $_POST['new_password'];
        $new_rol = $_POST['new_rol'];
        $conexion->query("INSERT INTO usuarios (username, password, rol) VALUES ('$new_user', '$new_pass', '$new_rol')");
        $mensaje = "El nuevo empleado ha sido registrado en la base de datos.";
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $del_id = (int)$_POST['delete_id'];
        if ($del_id !== (int)$_SESSION['usuario']['id']) {
            $conexion->query("DELETE FROM usuarios WHERE id = $del_id");
            $mensaje = "El registro ha sido eliminado exitosamente.";
        } else {
            $error = "Error de sistema: No puedes eliminar tu propia sesión activa.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusCorp - Entorno Vulnerable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #fff;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        /* Contenedor de partículas */
        #particles-js {
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: #0f0c29; /* Degradado oscuro */
            background: linear-gradient(to right, #24243e, #302b63, #0f0c29);
            z-index: -1;
        }
        /* Efecto Glassmorphism */
        .glass-card {
            background: rgba(25, 25, 35, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
        }
        .text-muted { color: #adb5bd !important; }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
            border-color: #dc3545;
        }
        /* Ocultar label flotante blanco para entorno oscuro */
        .form-floating>label { color: #adb5bd; }
        .table-custom { color: white; border-spacing: 0 10px; border-collapse: separate; }
        .table-custom tr {
            background: rgba(255, 255, 255, 0.03);
            transition: transform 0.2s;
        }
        .table-custom tr:hover { transform: scale(1.01); background: rgba(255, 255, 255, 0.08); }
        .table-custom td { border: none; padding: 15px; vertical-align: middle; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">

<div id="particles-js"></div>

<div class="container py-5">
<?php if (!isset($_SESSION['usuario'])): ?>
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card glass-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-buildings text-danger" style="font-size: 3rem;"></i>
                        <h3 class="fw-bold mt-2">NexusCorp</h3>
                        <p class="text-white-50 small">Acceso Corporativo Interno</p>
                    </div>
                    
                    <form method="POST" class="auth-form">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="login_username" name="login_username" placeholder="Usuario" required>
                            <label for="login_username"><i class="bi bi-person"></i> Identificador</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="login_password" name="login_password" placeholder="Contraseña">
                            <label for="login_password"><i class="bi bi-key"></i> Contraseña</label>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold shadow">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión (Vulnerable)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card glass-card mb-4">
                <div class="card-body p-4 d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25">
                    <div>
                        <h4 class="mb-0 fw-bold"><i class="bi bi-hdd-network text-danger"></i> Base de Datos Central</h4>
                        <span class="text-white-50 small">Estado: Vulnerable a Inyecciones</span>
                    </div>
                    <div>
                        <span class="badge bg-danger text-white px-3 py-2 rounded-pill me-2">
                            <i class="bi bi-person-badge"></i> <?= $_SESSION['usuario']['rol'] ?>
                        </span>
                        <a href="login_vulnerable.php?logout=1" class="btn btn-outline-light rounded-pill btn-sm px-3"><i class="bi bi-power"></i> Salir</a>
                    </div>
                </div>
                
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['usuario']['username']) ?>&background=dc3545&color=fff&rounded=true&size=64" alt="Avatar" class="me-3 shadow">
                        <div>
                            <h3 class="fw-light mb-0">Bienvenido, <span class="fw-bold"><?= htmlspecialchars($_SESSION['usuario']['username']) ?></span></h3>
                        </div>
                    </div>

                    <?php if ($_SESSION['usuario']['rol'] === 'Administrador'): ?>
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="p-4 rounded-4" style="background: rgba(0,0,0,0.2);">
                                    <h5 class="fw-bold text-danger mb-4"><i class="bi bi-person-plus"></i> Nuevo Registro</h5>
                                    <form method="POST" class="auth-form">
                                        <input type="hidden" name="action" value="add">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" name="new_username" placeholder="Usuario" required>
                                            <label>Usuario</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" name="new_password" placeholder="Contraseña" required>
                                            <label>Contraseña</label>
                                        </div>
                                        <div class="form-floating mb-4">
                                            <select name="new_rol" class="form-select">
                                                <option value="Usuario">Usuario Estándar</option>
                                                <option value="Gerente">Gerencia</option>
                                                <option value="Administrador">Administrador</option>
                                            </select>
                                            <label>Asignar Rol</label>
                                        </div>
                                        <button type="submit" class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">Ejecutar INSERT</button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>Empleado</th>
                                                <th>Credenciales (Texto Plano)</th>
                                                <th>Privilegios</th>
                                                <th class="text-end">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $usuarios = $conexion->query("SELECT * FROM usuarios");
                                            while($row = $usuarios->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['username']) ?>&background=random&color=fff&rounded=true&size=40" class="me-2">
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($row['username']) ?></div>
                                                            <div class="text-white-50 small">ID: #<?= $row['id'] ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-danger fw-bold"><i class="bi bi-unlock-fill"></i> <?= htmlspecialchars($row['password']) ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $row['rol'] == 'Administrador' ? 'bg-danger' : 'bg-secondary' ?> border rounded-pill px-3 py-1">
                                                        <?= $row['rol'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <form method="POST" class="d-inline auth-form">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                                        <button type="button" class="btn btn-outline-light btn-sm rounded-circle btn-delete" style="width: 35px; height: 35px;"><i class="bi bi-trash3"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-5 text-center" style="background: rgba(0,0,0,0.2); border-radius: 20px;">
                            <i class="bi bi-shield-lock text-white-50 fs-1 mb-3"></i>
                            <h5>Panel Restringido</h5>
                            <p class="text-white-50">Privilegios insuficientes para visualizar la tabla de empleados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
    // 1. Configuración de Partículas (Rojas / Hacker theme)
    particlesJS("particles-js", {
        particles: {
            number: { value: 60 },
            color: { value: "#dc3545" },
            shape: { type: "circle" },
            opacity: { value: 0.5, random: false },
            size: { value: 3, random: true },
            line_linked: { enable: true, distance: 150, color: "#dc3545", opacity: 0.2, width: 1 },
            move: { enable: true, speed: 2, direction: "none", random: false, straight: false, out_mode: "out", bounce: false }
        },
        interactivity: {
            detect_on: "canvas",
            events: { onhover: { enable: true, mode: "grab" }, onclick: { enable: true, mode: "push" }, resize: true },
            modes: { grab: { distance: 140, line_linked: { opacity: 1 } }, push: { particles_nb: 4 } }
        },
        retina_detect: true
    });

    // 2. SweetAlert2 para Errores/Éxitos
    <?php if($error): ?>
        Swal.fire({ icon: 'error', title: 'Acceso Denegado', text: '<?= $error ?>', background: '#1e1e2f', color: '#fff', confirmButtonColor: '#dc3545' });
    <?php endif; ?>
    <?php if($mensaje): ?>
        Swal.fire({ icon: 'success', title: 'Operación Exitosa', text: '<?= $mensaje ?>', background: '#1e1e2f', color: '#fff', confirmButtonColor: '#198754' });
    <?php endif; ?>

    // 3. Confirmación de Borrado con SweetAlert (Mucho mejor que el confirm nativo)
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            const form = this.closest('form');
            Swal.fire({
                title: '¿Eliminar registro?',
                text: "Esta acción ejecutará un DELETE en la base de datos.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                background: '#1e1e2f', color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) { form.submit(); }
            });
        });
    });

    // 4. Spinners en Botones al enviar formularios
    document.querySelectorAll('.auth-form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if(btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando...';
                btn.disabled = true;
            }
        });
    });
</script>
</body>
</html>