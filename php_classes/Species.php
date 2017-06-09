<?php
/**
 * Created by PhpStorm.
 * User: acomjean
 * Date: 2/21/17
 * Time: 4:59 PM
 */

namespace DRSC;

/**
 * Class Species
 * @package DRSC
 *
 * Species lookup from species table
 *
 */

class Species {

    private $dbo;
    public $sql_debug = false;

    public function __construct(\PDO $pdoObj) {
        $this->dbo = $pdoObj;

    }

    /**
     *
     * @return array|null
     *
     * Get the species information table
     *
     */

    /* Return Data:
     * Array (10)
        |    ['0'] => Array (8)
        |    |    ['speciesid'] = String(4) "4896"
        |    |    ['species_name'] = String(25) "Schizosaccharomyces pombe"
        |    |    ['short_species_name'] = String(2) "sp"
        |    |    ['common_name'] = String(13) "Fission yeast"
        |    |    ['species_specific_geneid_type'] = String(7) "PomBase"
        |    |    ['species_specific_database_URL'] = String(23) "http://www.pombase.org/"
        |    |    ['species_specific_URL_format'] = String(39) "http://www.pombase.org/spombe/result/%s"
        |    |    ['display_order'] = String(2) "10"
        |    ['1'] => Array (8)
        |    |    ['speciesid'] = String(4) "4932"
        |    |    ['species_name'] = String(24) "Saccharomyces cerevisiae"
     */

    public function getSpeciesInfo() {
        $sql = "SELECT * FROM `Species`";
        $STH = $this->dbo->prepare($sql);

        if ($STH->execute()) {

            $resultData = $STH->fetchAll(\PDO::FETCH_ASSOC);

        } else {

            if ($this->sql_debug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
            $resultData = null;
        }

        return $resultData;
    }

    /**
     *
     * @return array|null
     *
     * Get the species information table indexed by the species numeric id
     *
     */
     /* Return Data:
        Array (9)
        |    ['4896'] => Array (8)
        |    |    ['speciesid'] = String(4) "4896"
        |    |    ['species_name'] = String(25) "Schizosaccharomyces pombe"
        |    |    ['short_species_name'] = String(2) "sp"
        |    |    ['common_name'] = String(13) "Fission yeast"
        |    |    ['species_specific_geneid_type'] = String(7) "PomBase"
        |    |    ['species_specific_database_URL'] = String(23) "http://www.pombase.org/"
        |    |    ['species_specific_URL_format'] = String(39) "http://www.pombase.org/spombe/result/%s"
        |    |    ['display_order'] = String(2) "10"
        |    ['4932'] => Array (8)
        |    |    ['speciesid'] = String(4) "4932"
        |    |    ['species_name'] = String(24) "Saccharomyces cerevisiae"
        |    |    ['short_species_name'] = String(2) "sc"
       */

    public function getSpeciesInfoIndexedByID() {

        $speciesData = $this->getSpeciesInfo();

        if (empty($speciesData)){
            return $speciesData;

        }
        $speciesList = array();

        foreach ($speciesData as $oneSpecies) {
            $key = $oneSpecies['speciesid'];
            if ($key == 0) {
                continue;
            }
            $speciesList[$key] = $oneSpecies;
        }

        return $speciesList;
    }

}