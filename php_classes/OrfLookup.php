<?php

namespace DRSC;

/**
 * Class OrfLookup
 * @package DRSC
 *
 * Common routines to lookup ORF data
 *
 */

class OrfLookup {


    public $debug = false;
    public $sqlDebug = false;

    private $dbo;

    /**
     *
     * @param \PDO $dbo
     *
     * OrfLookup constructor.
     *
     * input a PDO object for database lookups
     *
     */

    public function __construct(\PDO $dbo) {

        $this->dbo = $dbo;
    }



    /**
     *
     * @param $speciesID
     * @param $geneID
     * @return int
     *
     * get counts for given geneID
     *
     */

    public function getOrfCounts ($speciesID, $geneID){


        $sql = "select count(*) as count from Orf_Clones where gene_id=:geneID and species_id=:speciesID;";

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':geneID', $geneID, \PDO::PARAM_INT);
        $STH->bindValue(':speciesID', $speciesID, \PDO::PARAM_INT);

        if ($STH->execute()) {
            $resultData = $STH->fetch(\PDO::FETCH_ASSOC);
        } else {
            if ($this->sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
            $resultData = 0;
        }

        if (empty($resultData)){
            return 0;
        }

        return $resultData['count'];
    }


    /**
     *
     * @param $speciesID
     * @param $geneID
     * @return array|null
     *
     * get counts for given geneID and Species.
     */

    public function getOrfData ($speciesID, $geneID){

        $sql = "select * from Orf_Clones where gene_id=:geneID and species_id=:speciesID;";

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':geneID', $geneID, \PDO::PARAM_INT);
        $STH->bindValue(':speciesID', $speciesID, \PDO::PARAM_INT);

        if ($STH->execute()) {
            $resultData = $STH->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            if ($this->sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
            $resultData = null;
        }

        if (empty($resultData)){
            return null;
        }

        return $resultData;
    }
}
