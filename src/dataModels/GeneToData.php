<?php


namespace dataModels;

/**
 * Class GeneToData
 *
 * Collection of functions that take the NCBI geneID and query the databaase for
 * related data, includeing publications
 *
 * @package dataModels
 */


class GeneToData{

    private $dbo;
    public $sql_debug = false;


    /**
     * GeneToData constructor.
     * @param \PDO $pdoObj
     *
     * Takes PDO object for database lookups
     */


    public function __construct(\PDO $pdoObj) {
        $this->dbo = $pdoObj;

    }

    /** Input an array of GeneIDs (NCBI) returns an array of counts.
     *
     *
     * @param $geneIDs
     * @param int $maxGenesPerPaper
     * @return array|null
     *
     *
     */

    /* Return Data:
     *
     * Array (6)
        (
        |    ['19260'] => Array (1)
        |    (
        |    |    ['0'] => Array (1)
        |    |    (
        |    |    |    ['count'] = String(2) "35"
        |    |    )
        |    )
        |    ['26191'] => Array (1)
        |    (
        |    |    ['0'] => Array (1)
        |    |    (
        |    |    |    ['count'] = String(3) "437"
        |    |    )
        .........
     */

    public function getPublicationCounts ($geneIDs, $maxGenesPerPaper=100) {

        if (!is_array($geneIDs)){
            $geneIDs = array($geneIDs);
        }

        foreach ($geneIDs as $key => $val) {
            $geneIDs[$key] = $this->dbo->quote($val);
        }
        $searchIn = implode(',', $geneIDs);


        $sql = "select gene_id, count(*) as count from Gene_Pubmed WHERE gene_id in ({$searchIn}) and genes_per_publication < :max_gene_count GROUP BY gene_id ";

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':max_gene_count', $maxGenesPerPaper, \PDO::PARAM_INT);

        if ($STH->execute()) {

            $resultData = $STH->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_GROUP);

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
     * Input a geneID (NCBI id) and a return size limit and the maximum genes listed per paper,
     * returns an array with details of the publications
     *
     * @param $geneID
     * @param int $limit
     * @param int $maxGenesPerPaper
     * @return array|null
     *
     */
    /* Return Data:
     * Array (437)
    (
    |    ['0'] => Array (5)
    |    (
    |    |    ['id'] = String(7) "1515965"
    |    |    ['tax_id'] = String(4) "9606"
    |    |    ['gene_id'] = String(5) "26191"
    |    |    ['pmid'] = String(8) "27107590"
    |    |    ['genes_per_publication'] = String(1) "1"
    |    )
    |    ['1'] => Array (5)
    |    (
    |    |    ['id'] = String(7) "1515964"
    |    |    ['tax_id'] = String(4) "9606"
    |    |    ['gene_id'] = String(5) "26191"
    |    |    ['pmid'] = String(8) "26782543"
    |    |    ['genes_per_publication'] = String(1) "3"
    |    )
    |    ['2'] => Array (5)
    |    (
    |    |    ['id'] = String(7) "1515963"
    |    |    ['tax_id'] = String(4) "9606"
    |    |    ['gene_id'] = String(5) "26191"
    |    |    ['pmid'] = String(8) "26734582"
    |    |    ['genes_per_publication'] = String(1) "2"
     *
     */

    public function getPMIDs($geneID, $limit=20000, $maxGenesPerPaper=100){
        $sql = "select * from Gene_Pubmed WHERE gene_id = :geneid and genes_per_publication < :max_gene_count order by pmid DESC LIMIT 0, $limit;";

        $STH = $this->dbo->prepare($sql);
        $STH->bindValue(':geneid', $geneID, \PDO::PARAM_INT);
        $STH->bindValue(':max_gene_count', $maxGenesPerPaper, \PDO::PARAM_INT);
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
     * Find Go annotations given array of single NCBI geneID (array of ints)
     *
     * @param $geneIDs (array of int)
     * @return array|null
     */
    /* Return Data:
        *
        *
        * Array (31)
        (
        |    ['0'] => Array (8)
        |    (
        |    |    ['tax_id'] = String(4) "9606"
        |    |    ['GeneID'] = String(5) "26191"
        |    |    ['GO_ID'] = String(10) "GO:0004725"
        |    |    ['evidence'] = String(3) "IDA"
        |    |    ['qualifier'] = String(1) "-"
        |    |    ['GO_term'] = String(37) "protein tyrosine phosphatase activity"
        |    |    ['pubmed'] = String(26) "10068674|16461343|18056643"
        |    |    ['category'] = String(8) "Function"
        |    )
        |    ['1'] => Array (8)
        |    (
        |    |    ['tax_id'] = String(4) "9606"
        |    |    ['GeneID'] = String(5) "26191"
        |    |    ['GO_ID'] = String(10) "GO:0005515"
        |    |    ['evidence'] = String(3) "IPI"
        |    |    ['qualifier'] = String(1) "-"
        |    |    ['GO_term'] = String(15) "protein binding"
        |    |    ['pubmed'] = String(71) "10068674|11882361|16461343|19167335|21719704|23871208|24658140|25040622"
        |    |    ['category'] = String(8) "Function"
    */

    public function getGene2Go ($geneIDs){

        if (!is_array($geneIDs)){
            $geneIDs = array($geneIDs);
        }

        foreach ($geneIDs as $key => $val) {
            $geneIDs[$key] = $this->dbo->quote($val);
        }

        $searchIn = implode(',', $geneIDs);

        $sql = "SELECT * FROM `Gene2Go` WHERE `GeneID` IN ($searchIn) and `evidence` in ('EXP', 'IDA', 'IPI', 'IMP','IGI', 'IEP'  )  ";


        $STH = $this->dbo->prepare($sql);

        if ($STH->execute()) {
            $resultData = $STH->fetchall(\PDO::FETCH_ASSOC);
        } else {
            if ($this->sql_debug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
            $resultData = null;
        }


        if (empty($resultData)){
            return null;
        }
        do_dump ($resultData);

        return $resultData;

    }



}