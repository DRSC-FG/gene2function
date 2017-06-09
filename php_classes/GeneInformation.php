<?php


namespace DRSC;


// wrapper class to lookup genes data
/**
 *
 * Lookup for gene information
 *
 * Class GeneInformation
 * @package DRSC
 *
 */

class GeneInformation {

    public static $sql_debug = true;

    /**
     * @param $dbo
     * @param $eGeneID
     * @return null
     *
     * Given a database connection and an NCBI (entrez) Gene id return data
     */

    public static function getGeneNameFromEntrezID($dbo, $eGeneID) {

        $sql = "SELECT * FROM `Gene_Information` WHERE `geneid` = :geneid ";
        $STH = $dbo->prepare($sql);
        $STH->bindValue(':geneid', $eGeneID, \PDO::PARAM_STR);

        // run the query
        if ($STH->execute()) {
            $returnValue = $STH->fetch(\PDO::FETCH_ASSOC);

            //only return data if some found..
            if (empty($returnValue)) {
                return null;
            }
        } else {
            if (self::$sql_debug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
        }
        return $returnValue;
    }

}