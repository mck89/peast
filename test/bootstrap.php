<?php
$ds = DIRECTORY_SEPARATOR;
$paths = array(
    ".." . $ds . "vendor",
    ".." . $ds . ".." . $ds . ".."
);
foreach ($paths as $path) {
    $abspath = __DIR__ . $ds . $path . $ds . "autoload.php";
    if (file_exists($abspath)) {
        require_once $abspath;
        break;
    }
}
