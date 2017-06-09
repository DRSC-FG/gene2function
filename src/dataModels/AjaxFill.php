<?php
/**
 * Created by PhpStorm.
 * User: acomjean
 * Date: 01/06/16
 * Time: 11:17 AM
 */

namespace dataModels;


class AjaxFill {

    private $dbo;

    public function __construct (\PDO $pdoObj) {

        $this->dbo = $pdoObj;

    }





}