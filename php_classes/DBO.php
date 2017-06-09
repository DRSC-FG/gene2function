<?php
/**
 *
 * Connections to Database centrallized here
 *
 */


namespace DRSC;

// database connection class.
/**
 * Class DBO
 * @package DRSC
 *
 * Static class for database connection.  Uses phps PDO
 *
 */

class DBO {


    /**
     * Constructor.
     *
     * Not used Static class.
     *
     *
     */

    private function __constructor(){

    }

    /**
     *
     * Creates PDO object based on input parameters
     *
     *
     * @param null $kind
     * @param bool $errormode
     * @return \PDO
     *
     */

    public static function getDBO ($kind = null, $errormode = false) {



        if ($kind === 'cli') {

            $user = "username_cli";
            $p = "password_cli";
            $dns = "mysql:dbname=cli_my_database;host=127.0.0.1";

        } else {

            $user = "username";
            $p = "password";
            $dns = "mysql:dbname=my_database;host=127.0.0.1";
        }


        if ($errormode) {
            $dbo = new \PDO($dns, $user, $p, array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ));
        } else {
            $dbo = new \PDO($dns, $user, $p);
        }

        return $dbo;
    }




}