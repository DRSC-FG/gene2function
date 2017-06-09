<?php


namespace dataModels;
/**
 * Class DioptDiseaseLookup
 * @package dataModels
 *
 * This class does the diopt disease lookups for getting disease and gene information from the database
 *
 *
 *
 */

class DioptDiseaseLookup {

    private $dbo;
    private $sql_debug= false;


    /**
     * DioptDiseaseLookup constructor.
     *
     * @param \PDO $pdoObj
     *
     * Takes a database access PDO as input
     *
     */

    public function __construct(\PDO $pdoObj) {

        $this->dbo = $pdoObj;

    }


    /**
     * @param $diseaseIdArray
     * @return array
     *
     * given a list of Disease IDs, return an array with a structure like the following:
     *
     */

     /* Return Data:
     *
     * Array (2)
    |    ['diseaseGenes'] => Array (1)
    |    (
    |    |    ['0'] => Array (4)
    |    |    |    ['disease_term'] = String(44) "Brain tumor-polyposis syndrome 2, 175100 (3)"
    |    |    |    ['source'] = String(4) "OMIM"
    |    |    |    ['diseaseid'] = String(4) "2464"
    |    |    |    ['geneid'] = String(3) "324"
    |    )
    |    ['geneInfo'] => Array (1)
    |    (
    |    |    ['324'] => Array (1)
    |    |    (
    |    |    |    ['0'] => Array (3)
    |    |    |    |    ['symbol'] = String(3) "APC"
    |    |    |    |    ['species_specific_geneid'] = String(3) "583"
    |    |    |    |    ['species_specific_geneid_type'] = String(4) "HGNC"
    |    |    )
    |    )
     */

    public function getDiseaseInformationFromIds ( $diseaseIdArray ) {
        $time_start = microtime(true);


        $diseaseGenes = $this->diseaseIDsToHumanGenes($diseaseIdArray);

        // lookup genes
        $geneList2 = array();

        foreach ($diseaseGenes as $oneResult){
            $key  = (int) $oneResult['geneid'];
            $geneList2[$key]= $key;
        }

        $geneInfo = $this->getGeneInfo ($geneList2);

        return array('diseaseGenes'=> $diseaseGenes, 'geneInfo' => $geneInfo);

    }


    /**
     * @param $geneIDArray
     * @return array|null
     *
     * Takes a array of GeneIDs (NCBI gene ids)
     * and returns an array with found geneIDs and the corresponding protein ID
     */

    public function getProteinData ($geneIDArray) {

        $geneIDs = implode(',', $geneIDArray );

        $sql = "select distinct geneid1, proteinid1 from Protein_Pair where geneid1 in 
 ({$geneIDs});";

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':geneList', $geneIDs, \PDO::PARAM_STR);

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
     * Input list of integers (NCBI gene ids) returns information about Gene (Symbol, species specific ids)
     *
     * @param $geneIDArray
     * @return array|null
     *
     *
     * Output an array with gene symbol, and species specific gene ids and symbols
     * Returns null on error, or empty array if nothing found.
     */


     /*  Return Data:
      array (size=1)
      0 =>
        array
          'gene_id'=>'343'
          'symbol' => string 'AIF1'
          'species_specific_geneid' => string '352'
          'species_specific_geneid_type'=>'HGNC'
      1 => ...
      */

    public function getGeneInfo ($geneIDArray) {

        $idArray = array();
        foreach ($geneIDArray as $oneRow){
            $idArray[] = (int) $oneRow;
        }

        $geneIDs = implode(',', $idArray );

        $sql = "select geneid, symbol, species_specific_geneid, species_specific_geneid_type from Gene_Information where geneid in ({$geneIDs});";
        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':geneList', $geneIDs, \PDO::PARAM_STR);

        if ($STH->execute()) {
            $resultData = $STH->fetchAll(\PDO::FETCH_ASSOC| \PDO::FETCH_GROUP);
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
     * Given a Disease ID number array returns the genes Associated with it
     *
     * @param $diseaseIDs
     * @return array|null
     *
     *
     * Returns null on error, or empty array if nothing found.
     */

    /* Return Data:
     *
     Array (351)
    (
    |    ['0'] => Array (4)
    |    (
    |    |    ['disease_term'] = String(14) "Neonatal lupus"
    |    |    ['source'] = String(4) "GWAS"
    |    |    ['diseaseid'] = String(4) "6367"
    |    |    ['geneid'] = String(3) "199"
    |    )
    |    ['1'] => Array (4)
    |    (
    |    |    ['disease_term'] = String(28) "Systemic lupus erythematosus"
    |    |    ['source'] = String(4) "GWAS"
    |    |    ['diseaseid'] = String(4) "8136"
    |    |    ['geneid'] = String(3) "274"
    |    )
    |    ['2'] => Array (4)
    |    (
    |    |    ['disease_term'] = String(28) "Systemic lupus erythematosus"
    |    |    ['source'] = String(4) "GWAS"
    |    |    ['diseaseid'] = String(4) "8136"
    |    |    ['geneid'] = String(3) "393"

     */

    // input is an array of disease IDs.

    public function diseaseIDsToHumanGenes ($diseaseIDs) {
        $idArray = array();

        foreach ($diseaseIDs as $oneRow){
            $idArray[] = (int) $oneRow;
        }

        $diseaseIDs = implode(',', $idArray );

        $sql = <<<SQL
              
select d.disease_term, d.source, dga.diseaseid, dga.source, dga.geneid
from Disease_Gene_Association dga,  Disease_Category_Mapping dcm, Disease d 
where dga.diseaseid = dcm.diseaseid and dga.diseaseid in ({$diseaseIDs}) and d.diseaseid = dga.diseaseid 
group by dga.diseaseid, dga.source, dga.geneid order by  dga.geneid;
SQL;

        $STH = $this->dbo->prepare($sql);
        //$STH->bindValue(':species_id', $speciesID, \PDO::PARAM_STR);

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
     * Given a Disease Name string return the first 200 matches from the disease table
     *
     * @param $diseaseName
     * @return array|null
     *
     *
     */


    private function diseaseNameToIDs ($diseaseName) {

        $diseaseName = "%{$diseaseName}%";

        $sql = "SELECT * FROM `Disease` WHERE `disease_term` LIKE :diseaseName Limit 200;";

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':diseaseName', $diseaseName, \PDO::PARAM_STR);

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
     * Takes a disease name, does some processing on it (removes spaces, plurals)
     * returns array of information from the disease table indexed by the diseaseid
     *
     *
     * @param $diseaseName (string)
     * @return array|null
     */

    /* Return Data:

    Array (4)
    |    ['847'] => Array
    |    (
    |    |    ['diseaseid'] = String "847"
    |    |    ['disease_term'] = String "{Colon cancer, susceptibility to}, 114500 (3)"
    |    |    ['source'] = String "OMIM"
    |    )
    |    ['3122'] => Array
    ...............
    )

    */

    // do a bunch of lookups using slight modifications to the  input.  Combine the results

    public function findManyDiseaseNameToIDs ($diseaseName) {

        $diseaseName = trim($diseaseName);

        $diseases = $this->diseaseNameToIDs($diseaseName);

        // 2 look modified (remove trailing s etc..).

        if (substr($diseaseName, -1) == 's' || substr($diseaseName, -1) == 'S') {
            $diseaseName = substr($diseaseName, 0, -1);
        }

        $diseaseName = preg_replace("/[^[:alnum:][:space:]]/u", '', $diseaseName);
        $diseases2 = $this->diseaseNameToIDs($diseaseName);

        $diseases = $diseases + $diseases2;


        // 3.  Remove any posessives... and plurals in search term ('s)

        $diseaseName3 = str_replace("'s", "", $diseaseName);
        $diseaseName3 = str_replace("'S", "", $diseaseName3);
        $diseaseName3 = str_replace("s ", " ", $diseaseName3);
        $diseaseName3 = str_replace("S ", " ", $diseaseName3);


        if (substr($diseaseName3, -1) == 's' || substr($diseaseName3, -1) == 'S') {
            $diseaseName3 = substr($diseaseName3, 0, -1);
        }
        $diseaseName3 = preg_replace("/[^[:alnum:][:space:]]/u", '', $diseaseName3);


        // only search again if we changed the name we are searching for

        if ($diseaseName3 != $diseaseName){
            $diseases3 = $this->diseaseNameToIDs($diseaseName3);
            $diseases = $diseases + $diseases3;
        }

        // If nothing return null

        if (empty($diseases)){
            return null;
        }

        $idArray = array();

        // return as array indexed on disease id,  also removes duplicated entries

        foreach ($diseases as $oneRow) {
            $key = (int)$oneRow['diseaseid'];
            $idArray[$key] = $oneRow;
        }

        return $idArray;

    }




    /*--------------------------------------------------------------------

     Gene-> Disease Lookups.

    ---------------------------------------------------------------------*/

    /**  Given an array of NCBI gene ID #s return an array of disease counts for each.
     *
     * @param $geneList
     * @param null $filter
     * @return array|null
     *
     */

    /* Return Data:
     Array (10)
     (
     |    ['0'] => Array (2)
     |    (
     |    |    ['geneid'] = String(4) "1630"
     |    |    ['count'] = String(2) "15"
    ........
    */

    public function geneToDiseaseCount ($geneList, $filter = null){
        $idArray = array();

        foreach ($geneList as $oneRow){
            $idArray[] = (int) $oneRow;
        }

        $geneIDs = implode(',', $idArray );

        $filterString ='';

        if (isset ($filter['min-rank'])){
            if ($filter['min-rank'] == 'high'){
                $filterString = " and rank = 'high' ";
            }
        }

        $sql = "SELECT geneid, count(*) as count FROM `Disease_Gene_Association`  WHERE `geneid` in( {$geneIDs}) {$filterString} group by `geneid`";

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':geneID', $geneIDs, \PDO::PARAM_STR);

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
     * @param $geneID
     * @param null $filter
     * @return array|null
     *
     */

    /* Return Data:
    Array (28)
    (
    |    ['5996'] => Array (6)
    |    (
    |    |    ['disease_term'] = String(30) "Mirror movements 1, 157600 (3)"
    |    |    ['source'] = String(4) "OMIM"
    |    |    ['diseaseid'] = String(4) "5996"
    |    |    ['geneid'] = String(4) "1630"
    |    |    ['rank'] = String(4) "high"
    |    |    ['extra'] => Array (3)
    |    |    (
    |    |    |    ['0'] => Array (5)
    |    |    |    (
    |    |    |    |    ['informationID'] = String(5) "14788"
    |    |    |    |    ['diseaseID'] = String(4) "5996"
    |    |    |    |    ['info_type'] = String(13) "Genome region"
    |    |    |    |    ['info_value'] = String(7) "18q21.2"
    |    |    |    |    ['extra_info'] = String(0) ""
    |    |    |    )
    |    |    |    ['1'] => Array (5)
    |    |    |    (
    |    |    |    |    ['informationID'] = String(5) "14789"
    |    |    |    |    ['diseaseID'] = String(4) "5996"
    |    |    |    |    ['info_type'] = String(7) "OMIM_ID"
    |    |    |    |    ['info_value'] = String(6) "120470"
    |    |    |    |    ['extra_info'] = String(12) "OMIM gene ID"
    |    |    |    )
    |    |    )
    |    )
    |    ['4111'] = > Array (6)
    .......
    */

    public function geneToDiseaseInfo ($geneID, $filter = null){

        $filterString ='';

        if (isset ($filter['min-rank'])){

            if ($filter['min-rank'] == 'high'){
                $filterString = " and rank = 'high' ";

            }
        }


        $sql = <<<SQL
            select d.disease_term, d.source, dga.diseaseid, dga.source, dga.geneid, dga.rank from Disease_Gene_Association dga,  Disease_Category_Mapping dcm, Disease d  where dga.diseaseid = dcm.diseaseid and dga.geneid = :geneID and d.diseaseid = dga.diseaseid {$filterString} group by dga.diseaseid, dga.source, dga.geneid order by  d.source DESC, dga.rank;
SQL;

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':geneID', $geneID, \PDO::PARAM_STR);

        if ($STH->execute()) {
            $resultData = $STH->fetchAll(\PDO::FETCH_ASSOC);
        } else {

            if ($this->sql_debug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }

            $resultData = null;

            return $resultData;
        }

        // additional disease information lookup

        $diseaseData = array();

        foreach ($resultData as $onerow){
            $key = $onerow['diseaseid'];

            $diseaseData[$key] = $onerow;

            // look up addtional data.

            $key = (int) $key;
            $sql = "SELECT *   FROM `Disease_Information`  WHERE `diseaseID` = :diseaseID; ";

            $STH = $this->dbo->prepare($sql);
            $STH->bindValue(':diseaseID', $key, \PDO::PARAM_INT);

            if ($STH->execute()) {
                $extraValues = $STH->fetchAll(\PDO::FETCH_ASSOC);
            } else {

                if ($this->sql_debug) {
                    echo " Query didn't work : {$sql} \n";
                    print_r($STH->errorInfo());
                }
            }


            if (! empty ($extraValues)){

                $diseaseData[$key]['extra'] = $extraValues;

            }
        }

        return $diseaseData;
    }



}