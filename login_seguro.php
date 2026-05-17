<?php
// login_seguro.php
session_start();

$conexion = new mysqli("localhost", "root", "", "seguridad_db");
$error = null;
$mensaje = null;

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login_seguro.php");
    exit();
}

// SOLUCIÓN: Sentencias Preparadas
if (isset($_POST['login_username']) && isset($_POST['login_password'])) {
    $user = $_POST['login_username'];
    $pass = $_POST['login_password'];

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $_SESSION['usuario'] = $resultado->fetch_assoc();
    } else {
        $error = "Intento bloqueado: Las credenciales son incorrectas o se detectó una inyección.";
    }
}

if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'Administrador') {
    
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $new_user = $_POST['new_username'];
        $new_pass = $_POST['new_password'];
        $new_rol = $_POST['new_rol'];
        
        $stmt_add = $conexion->prepare("INSERT INTO usuarios (username, password, rol) VALUES (?, ?, ?)");
        $stmt_add->bind_param("sss", $new_user, $new_pass, $new_rol);
        $stmt_add->execute();
        $mensaje = "Transacción parametrizada completada. Usuario registrado.";
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $del_id = (int)$_POST['delete_id'];
        
        if ($del_id !== (int)$_SESSION['usuario']['id']) {
            $stmt_del = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt_del->bind_param("i", $del_id);
            $stmt_del->execute();
            $mensaje = "Registro eliminado de forma segura mediante Bind Param.";
        } else {
            $error = "Acción denegada por reglas de integridad.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusCorp - Entorno Blindado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; margin: 0; overflow-x: hidden; color: #333; }
        
        /* Contenedor de partículas Claro */
        #particles-js {
            position: fixed; width: 100vw; height: 100vh;
            background: #eef2f3;
            background: linear-gradient(to right, #8e9eab, #eef2f3);
            z-index: -1;
        }
        
        /* Efecto Glassmorphism Claro */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            border-top: 5px solid #0d6efd;
        }
        
        .table-custom { border-spacing: 0 10px; border-collapse: separate; }
        .table-custom tr { background: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.02); transition: transform 0.2s; }
        .table-custom tr:hover { transform: scale(1.01); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
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
                        <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                        <h3 class="fw-bold mt-2 text-dark">NexusCorp</h3>
                        <p class="text-muted small">Autenticación Parametrizada</p>
                    </div>
                    
                    <form method="POST" class="auth-form">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="login_username" name="login_username" placeholder="Usuario" required>
                            <label for="login_username"><i class="bi bi-person-check text-primary"></i> Identificador</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="login_password" name="login_password" placeholder="Contraseña">
                            <label for="login_password"><i class="bi bi-shield-lock text-primary"></i> Contraseña</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                            <i class="bi bi-check-circle"></i> Conexión Segura
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
                <div class="card-body p-4 d-flex justify-content-between align-items-center border-bottom border-primary border-opacity-25">
                    <div>
                        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-server text-primary"></i> Bóveda de Datos</h4>
                        <span class="text-success small fw-medium"><i class="bi bi-check-circle-fill"></i> SGBD Blindado contra SQLi</span>
                    </div>
                    <div>
                        <span class="badge bg-primary text-white px-3 py-2 rounded-pill me-2 shadow-sm">
                            <i class="bi bi-person-badge"></i> <?= $_SESSION['usuario']['rol'] ?>
                        </span>
                        <a href="login_seguro.php?logout=1" class="btn btn-outline-dark rounded-pill btn-sm px-3"><i class="bi bi-power"></i> Salir</a>
                    </div>
                </div>
                
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['usuario']['username']) ?>&background=0d6efd&color=fff&rounded=true&size=64" alt="Avatar" class="me-3 shadow-sm">
                        <div>
                            <h3 class="fw-light mb-0 text-dark">Bienvenido, <span class="fw-bold"><?= htmlspecialchars($_SESSION['usuario']['username']) ?></span></h3>
                        </div>
                    </div>

                    <?php if ($_SESSION['usuario']['rol'] === 'Administrador'): ?>
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="p-4 rounded-4 bg-white shadow-sm border border-light">
                                    <h5 class="fw-bold text-primary mb-4"><i class="bi bi-person-fill-add"></i> Alta Parametrizada</h5>
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
                                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">Procesar Consulta Segura</button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>Empleado</th>
                                                <th>Credenciales Protegidas</th>
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
                                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['username']) ?>&background=random&color=fff&rounded=true&size=40" class="me-2 shadow-sm">
                                                        <div>
                                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['username']) ?></div>
                                                            <div class="text-muted small">ID: #<?= $row['id'] ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-success fw-bold"><i class="bi bi-shield-lock-fill"></i> <?= htmlspecialchars($row['password']) ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $row['rol'] == 'Administrador' ? 'bg-primary' : 'bg-info' ?> bg-opacity-10 text-dark border rounded-pill px-3 py-1">
                                                        <?= $row['rol'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <form method="POST" class="d-inline auth-form">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle btn-delete" style="width: 35px; height: 35px;"><i class="bi bi-trash3"></i></button>
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
                        <div class="p-5 text-center bg-white shadow-sm" style="border-radius: 20px;">
                            <i class="bi bi-shield-check text-success fs-1 mb-3"></i>
                            <h5 class="text-dark">Políticas de Acceso Aplicadas</h5>
                            <p class="text-muted">Tu nivel de acceso restringe las operaciones directas sobre el SGBD.</p>
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
    // 1. Configuración de Partículas (Azules / Trust theme)
    particlesJS("particles-js", {
        particles: {
            number: { value: 50 },
            color: { value: "#0d6efd" },
            shape: { type: "circle" },
            opacity: { value: 0.3, random: false },
            size: { value: 4, random: true },
            line_linked: { enable: true, distance: 150, color: "#0d6efd", opacity: 0.2, width: 2 },
            move: { enable: true, speed: 1.5, direction: "none", random: false, straight: false, out_mode: "out", bounce: false }
        },
        interactivity: {
            detect_on: "canvas",
            events: { onhover: { enable: true, mode: "grab" }, onclick: { enable: true, mode: "push" }, resize: true },
            modes: { grab: { distance: 140, line_linked: { opacity: 0.8 } }, push: { particles_nb: 3 } }
        },
        retina_detect: true
    });

    // 2. SweetAlert2 
    <?php if($error): ?>
        Swal.fire({ icon: 'error', title: 'Operación Bloqueada', text: '<?= $error ?>', confirmButtonColor: '#0d6efd' });
    <?php endif; ?>
    <?php if($mensaje): ?>
        Swal.fire({ icon: 'success', title: 'Ejecución Segura', text: '<?= $mensaje ?>', confirmButtonColor: '#198754' });
    <?php endif; ?>

    // 3. Confirmación de Borrado
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            const form = this.closest('form');
            Swal.fire({
                title: '¿Confirmar eliminación?',
                text: "Se ejecutará un DELETE parametrizado.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Proceder',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) { form.submit(); }
            });
        });
    });

    // 4. Spinners
    document.querySelectorAll('.auth-form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if(btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Protegiendo Datos...';
                btn.disabled = true;
            }
        });
    });
</script>
</body>
</html>