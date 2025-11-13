<?php

$password = password_hash($_GET["p"], PASSWORD_DEFAULT);

echo "<pre>";
print_r([
    "string" => $_GET["p"],
    "hash"   => $password
]);
echo "</pre>";
