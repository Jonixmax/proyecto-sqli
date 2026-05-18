# 🛡️ Proyecto: Laboratorio de Inyección SQL (SQLi)

Este repositorio contiene un entorno de pruebas local (laboratorio) diseñado para demostrar las vulnerabilidades de Inyección SQL (SQLi) en aplicaciones web y cómo mitigarlas en el Gestor de Base de Datos (SGBD).

El proyecto contrasta un escenario altamente vulnerable con un escenario seguro, permitiendo la ejecución de ataques manuales avanzados (Bypass, UNION, Error-Based, Stacked Queries) y automatizados (SQLMap) bajo un enfoque de **Hacking Ético**.

## 🎯 Objetivos del Proyecto
* **Demostrar** fallos críticos en la seguridad de los SGBD debido a la mala sanitización de entradas.
* **Ejecutar** escenarios de ataque para exponer datos sensibles, modificar registros y provocar denegación de servicio.
* **Implementar** técnicas de prevención y mitigación estructurales (Consultas Parametrizadas / Prepared Statements).

## 🛠️ Requisitos Previos
Para desplegar este laboratorio en tu computadora, necesitas:
* Un servidor web local con soporte para PHP y MySQL (Recomendado: **Laragon** o **XAMPP**).
* Un gestor de bases de datos (HeidiSQL, phpMyAdmin, DBeaver, etc.).

---

## 🚀 Instrucciones de Instalación (Despliegue)

Sigue estos pasos para replicar el entorno de pruebas exactamente como fue diseñado:

1. **Clonar o Descargar el Repositorio:**
   Descarga los archivos de este proyecto y colócalos en la carpeta pública de tu servidor local.
   * Si usas Laragon: `C:\laragon\www\proyecto-sqli\`
   * Si usas XAMPP: `C:\xampp\htdocs\proyecto-sqli\`

2. **Iniciar los Servicios:**
   Abre Laragon/XAMPP y enciende los servicios de **Apache** y **MySQL**.

3. **Importar la Base de Datos:**
   * Abre tu cliente de base de datos (Ej. HeidiSQL).
   * Conéctate a tu servidor local (usuario: `root`, sin contraseña por defecto).
   * Carga y ejecuta el script `database.sql` incluido en este repositorio. 
   * *Nota: Este script creará automáticamente la base de datos `seguridad_db`, la tabla de usuarios y los registros ficticios necesarios para las pruebas.*

---

## ⚔️ Guía de Pruebas y Explotación Manual

El laboratorio consta de dos portales. Navega a las siguientes URLs en tu explorador web:

### 1. Entorno Vulnerable (Escenario de Ataque)
* **URL:** `http://localhost/proyecto-sqli/login_vulnerable.php`

En este entorno, el código PHP utiliza concatenación directa y soporta consultas múltiples (`multi_query`). Puedes probar los siguientes vectores de ataque introduciendo los *payloads* en el campo **Usuario** (la contraseña puede quedar en blanco):

* **A. Bypass de Autenticación (Acceso Administrativo):**
    * `admin' #`
    * *Impacto:* Evasión del login ingresando al sistema con la cuenta del administrador sin conocer la clave.

* **B. Extracción Visual de Datos (UNION SELECT):**
    * `fantasma' UNION SELECT 1, database(), @@version, 'Administrador' #`
    * *Impacto:* Imprime el nombre de la base de datos y la versión del motor de MySQL directamente en la interfaz gráfica del panel de control.

* **C. Fuga de Información (Error-Based SQLi):**
    * `admin' AND EXTRACTVALUE(1, CONCAT(0x7e, database(), 0x7e, @@version)) #`
    * *Impacto:* Provoca un colapso en la sintaxis XPATH obligando al SGBD a revelar información sensible en la pantalla de error de PHP.

* **D. Escalamiento de Privilegios Vertical (Stacked Queries - UPDATE):**
    * `empleado1'; UPDATE usuarios SET rol='Administrador' WHERE username='empleado1'; -- `
    * *Impacto:* Permite a un usuario estándar alterar la base de datos desde el login para auto-asignarse rol de Administrador.

* **E. Sabotaje / Destrucción (Stacked Queries - DELETE):**
    * `admin'; DELETE FROM usuarios; -- `
    * *Impacto:* Vacía por completo la tabla de la base de datos. *(Nota de Contingencia: Tras ejecutar este ataque, deberás volver a importar el archivo `database.sql` en tu gestor para restaurar el laboratorio).*

---

### 2. Entorno Seguro (Escenario de Defensa)
* **URL:** `http://localhost/proyecto-sqli/login_seguro.php`

* **Prueba de mitigación:** Intenta ejecutar cualquiera de los ataques mencionados arriba.
* **Resultado esperado:** El sistema bloqueará los intentos de inyección y devolverá un mensaje de error genérico. El código fuente de este archivo demuestra la mitigación absoluta de la vulnerabilidad mediante el uso de **Sentencias Preparadas** (`stmt->bind_param`), aislando la estructura de la consulta de los datos introducidos por el usuario.