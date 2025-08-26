<?php
    $servidor = "localhost"; // O usa "127.0.0.1"
    $usuario = "root";
    $pass = ""; // En XAMPP, el usuario root no tiene contraseña por defecto
    $bbdd = "InstitucionEducativa";
    


    try{
        $pdo = new PDO("mysql:host=$servidor;dbname=$bbdd;CHARSET=Windows-1252", $usuario, $pass);
        //$db = new PDO('dblib:host=your_hostname;dbname=your_db;CHARSET=Windows-1252', $user, $pass);
        $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch (PDOException $e){
        echo $e;
    }
?>
