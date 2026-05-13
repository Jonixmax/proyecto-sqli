# 🛡️ Proyecto: Laboratorio de Inyección SQL (SQLi)

Este repositorio contiene un entorno de pruebas local (laboratorio) diseñado para demostrar las vulnerabilidades de Inyección SQL (SQLi) en aplicaciones web y cómo mitigarlas en el Gestor de Base de Datos (SGBD).

El proyecto contrasta un escenario altamente vulnerable con un escenario seguro, permitiendo la ejecución de ataques manuales y automatizados bajo un enfoque de **Hacking Ético**.

## 🎯 Objetivos del Proyecto
*   **Demostrar** fallos críticos en la seguridad de los SGBD debido a la mala sanitización de entradas.
*   **Ejecutar** escenarios de ataque (Bypass de autenticación y exposición de datos sensibles).
*   **Implementar** técnicas de prevención y mitigación reales (Consultas Parametrizadas / Prepared Statements).

## 🛠️ Requisitos Previos
Para desplegar este laboratorio en tu computadora, necesitas:
*   Un servidor web local con soporte para PHP y MySQL (Recomendado: **Laragon** o **XAMPP**).
*   Un gestor de bases de datos (HeidiSQL, phpMyAdmin, DBeaver, etc.).

---

## 🚀 Instrucciones de Instalación (Despliegue)

Sigue estos pasos para replicar el entorno de pruebas exactamente como fue diseñado:

1. **Clonar o Descargar el Repositorio:**
   Descarga los archivos de este proyecto y colócalos en la carpeta pública de tu servidor local.
   *   Si usas Laragon: `C:\laragon\www\proyecto-sqli\`
   *   Si usas XAMPP: `C:\xampp\htdocs\proyecto-sqli\`

2. **Iniciar los Servicios:**
   Abre Laragon/XAMPP y enciende los servicios de **Apache** y **MySQL**.

3. **Importar la Base de Datos:**
   * Abre tu cliente de base de datos (Ej. HeidiSQL).
   * Conéctate a tu servidor local (usuario: `root`, sin contraseña por defecto).
   * Carga y ejecuta el script `database.sql` incluido en este repositorio. 
   * *Nota: Este script creará automáticamente la base de datos `seguridad_db`, la tabla de usuarios y los registros ficticios necesarios para las pruebas.*

---

## ⚔️ Cómo realizar las pruebas

El laboratorio consta de dos portales. Navega a las siguientes URLs en tu explorador web:

### 1. Entorno Vulnerable (Escenario de Ataque)
* **URL:** `http://localhost/proyecto-sqli/login_vulnerable.php`
* **Prueba de ataque manual:** En el campo de "Usuario", introduce el payload `admin' #` y deja la contraseña en blanco o escribe cualquier texto.
* **Resultado esperado:** Lograrás un Bypass de autenticación, ingresando al sistema con privilegios de Administrador sin conocer la contraseña, obteniendo acceso a las funciones del CRUD y exposición de datos sensibles.

### 2. Entorno Seguro (Escenario de Defensa)
* **URL:** `http://localhost/proyecto-sqli/login_seguro.php`
* **Prueba de defensa:** Intenta el mismo ataque ingresando `admin' #`.
* **Resultado esperado:** El sistema bloqueará el intento de inyección. El código fuente de este archivo demuestra la mitigación de la vulnerabilidad mediante el uso de **Sentencias Preparadas** (`stmt->bind_param`), separando estrictamente la lógica de la base de datos de los datos del usuario.

---

