<?php
spl_autoload_register(function($className) {
    if (strpos($className, "Workman\\") === 0) {
        $fileName = str_replace("\\", '/', $className) . ".php";
        require_once __DIR__ . '/' . $fileName;
    }
});