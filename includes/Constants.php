<?php
/*define the constants required to connect to database*/

    define('DB_HOST','localhost');
    define('DB_USER','root');
    define('DB_PASSWORD','dagi90210');
    define('DB_NAME','my_app');
    define('DB_PORT',8080);

    define('USER_CREATED',101);
    define('USER_EXISTS',102);
    define('USER_FAILURE',103);

    define('USER_AUTHENTICATED',201);
    define('USER_NOT_FOUND',202);
    define('USER_PASSWORDS_DO_NOT_MATCH',203);

    define('PASSWORD_CHANGED',301);
    define('PASSWORDS_DO_NOT_MATCH',302);
    define('PASSWORD_NOT_CHANGED',303);


?>
