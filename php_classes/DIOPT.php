<?php

namespace DRSC;


// A lot of functionality for the original DIOPT was brought into various tools.
// This is a class to store some of the common functionality.
/**
 *
 * Class DIOPT
 * @package DRSC
 *
 * DIOPT ortholog scores are stored in a database table,
 *
 */

class DIOPT {

    private $dbo;
    public $sql_debug = true;

    /**
     * DIOPT constructor.
     * @param \PDO $pdoObj
     *
     * Input a PDO database object
     */


    public function __construct(\PDO $pdoObj) {
        $this->dbo = $pdoObj;

    }



    /**
     *
     * @param $speciesID_1
     * @param $speciesID_2
     * @return array|int
     *
     * Given two species ID get the maximum DIOPT score from the database
     */

    /* since not all species are tracked by all databases, get a count of which species are tracked by which databases
    Example Output:
    (
    |    [0] => Array (5)
    |    |    ['species1'] = String(4) "9606"
    |    |    ['species2'] = String(4) "4896"
    |    |    ['common_name'] = String(13) "Fission yeast"
    |    |    ['species_name'] = String(25) "Schizosaccharomyces pombe"
    |    |    ['max_score'] = String(1) "8"
    |    [1] => Array (5)
    |    |    ['species1'] = String(4) "9606"
    |    |    ['species2'] = String(4) "4932"
    |    |    ['common_name'] = String(5) "Yeast"
     ....
     */

    public function getMaxDioptScore($speciesID_1, $speciesID_2) {

        $data = array();
        //   $sql = 'select species1, species2, max(score) from Ortholog_Pair_Best WHERE species1 = :sp1 group by Species1, species2;';

        $sql = 'select species1, species2, common_name, species_name, max_score from Ortholog_Max_Score WHERE `species1` = :sp1 and `species2` = :sp2  group by Species1, species2 ;';

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':sp1', $speciesID_1, \PDO::PARAM_STR);
        $STH->bindValue(':sp2', $speciesID_2, \PDO::PARAM_STR);

        if ($STH->execute()) {

            $data = $STH->fetchAll(\PDO::FETCH_ASSOC);

        } else {

            $errorArray[] = "could not get data";
            if ($this->sql_debug) {
                echo " Query didn't work : {$sql} <br>\n";
                print_r($STH->errorInfo());
            }
            $data = 0;
        }

        return $data;

    }


    /**
     *
     * @param $speciesID (int)
     * @return array
     *
     * getMaxScoreArrayForSpecies given a species id number
     *
     */
    /* Example Output:
     * Array (9)
    (
    |    ['4896'] => Array (5)
    |    |    ['species1'] = String(4) "9606"
    |    |    ['species2'] = String(4) "4896"
    |    |    ['common_name'] = String(13) "Fission yeast"
    |    |    ['species_name'] = String(25) "Schizosaccharomyces pombe"
    |    |    ['max_score'] = String(1) "8"
    |    ['4932'] => Array (5)
    |    |    ['species1'] = String(4) "9606"
    |    |    ['species2'] = String(4) "4932"
    |    |    ['common_name'] = String(5) "Yeast"
     *
     */

    public function getMaxScoreArrayForSpecies($speciesID) {

        $sql = 'select  `species1`,  `species2`,  `common_name`,  `species_name`,  `max_score`  from Ortholog_Max_Score  WHERE species1 = :sp1 ;';

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':sp1', $speciesID, \PDO::PARAM_STR);


        if ($STH->execute()) {

            $data = $STH->fetchAll(\PDO::FETCH_ASSOC);

        } else {

            $errorArray[] = "could not get data";
            if ($this->sql_debug) {
                echo " Query didn't work : {$sql} <br>\n";
                print_r($STH->errorInfo());
            }
            $data = null;
        }

        #return it as an array index by the species for

        $returnData = array();

        foreach ($data as $oneRow){
            $key = $oneRow['species2'];

            $returnData[$key] =  $oneRow;


        }
        return $returnData;
    }
}