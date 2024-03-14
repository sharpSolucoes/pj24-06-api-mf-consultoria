<?php
date_default_timezone_set('America/Sao_Paulo');
define('VERSION', '24.2.0-alpha');
define('SANDBOX', true);
define('CORS_ORIGIN', (SANDBOX ? '*' : '*'));

include '_server/initialize.php';
