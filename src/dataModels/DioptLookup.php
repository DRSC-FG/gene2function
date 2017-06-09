<?php
/**
 * DioptLookup Class
 *
 * Looks up information on Orthologs (or paralogs) based on the diopt Ortholog score data from the database.
 *
 * Looks up other values for the Genes and if available for display in the main table.
 *
 */

namespace dataModels;


class DioptLookup {

    private $dbo;
    private $sql_debug= true;
    private $dataModel;


    /**
     * DioptLookup constructor.
     *
     * Takes a database object
     * a $dataModel object which has an "goTypes" element that is an array of valid goTypes to search for.
     *
     * @param \PDO $pdoObj
     */

    public function __construct(\PDO $pdoObj) {

        $this->dbo = $pdoObj;
        $this->dataModel = new \dataModels\DataFields($this->dbo);

    }


    /**
     *  Given a speciesID, GeneID and a target species ID return a table of diopt information sorted.
     *  if targetSpeciesID=0 lookup orthologs otherwise lookup paralogs.
     *
     * @param $speciesID
     * @param $geneID
     * @param int $targetSpeciesID
     * @return array|mixed
     */


    public function getDioptResultsGeneID ($speciesID, $geneID, $targetSpeciesID= 0){

        $table = $this->getDioptTable($speciesID, $geneID, $targetSpeciesID);
        $table = $this->sortDioptTable($table);


        return $table;
    }


    /** Takes input as data table and sorts based on species id and score.
     *
     *
     * @param $table
     * @return sorted $tablex
     */


    private function sortDioptTable ($table){


        // no results.  nothing to do
        if (empty($table)){
            return $table;
        }


        // Get species listing order from the table

        $speciesModel = new \DRSC\Species($this->dbo);
        $do = $speciesModel->getSpeciesInfo();


        $listingOrder = array();
        foreach ($do as $one){
            $key = $one['speciesid'];
            $listingOrder [$key] = $one['display_order'];
        }


        foreach ($table as &$oneResultSet) {

            uasort($oneResultSet, function ($x, $y) use ($listingOrder) {

                $confidence = array('high'=>100, 'moderate'=>5, 'low'=>0);

                // sort by species first.

                $a = $x['species_id'];
                $b = $y['species_id'];

                $a_index = 0;
                $b_index = 0;

                if (isset($listingOrder[$a])) {
                    $a_index = $listingOrder[$a];
                }

                if (isset($listingOrder[$b])) {
                    $b_index = $listingOrder[$b];
                }

                // if same species use score to sort

                if ($a_index == $b_index) {
                    $a = $x['score'];
                    $b = $y['score'];

                    // if score the same use "Best Score Counts"
                    if ($a == $b){

                        return ($x['best_score_count'] < $y['best_score_count'] ) ? 1 : -1;;

                    }

                    return ($a < $b) ? 1 : -1;;
                }

                return ($a_index < $b_index) ? 1 : -1;
            });
        }

        return $table;
    }





    /** function getDioptTable
     *
     * input species and geneid,
     * returns orthologs/paralogs for species.  targetSpeciesID = 0 means all.
     *
     * @param $speciesID
     * @param $entrezGeneid
     * @param $targetSpeciesID
     * @return array
     */

    /* Return Values:
     * Array (1)
        (
        |    ['207'] => Array (13)  (source: NCBI_Gene_ID)
        |    (
        |    |    ['11651'] => Array (23)  (target: NCBI_Gene_ID)
        |    |    (
        |    |    |    ['methods'] => Array (7)
        |    |    |    (
        |    |    |    |    ['0'] = String(7) "Compara"
        |    |    |    |    ['1'] = String(10) "Homologene"
        |    |    |    |    ['2'] = String(10) "Inparanoid"
        |    |    |    |    ['3'] = String(3) "OMA"
        |    |    |    |    ['4'] = String(8) "orthoMCL"
        |    |    |    |    ['5'] = String(7) "Phylome"
        |    |    |    |    ['6'] = String(7) "RoundUp"
        |    |    |    )
        |    |    |    ['best_score'] = String(3) "Yes"
        |    |    |    ['score'] = String(1) "7"
        |    |    |    ['max_score'] = String(2) "13"
        |    |    |    ['best_score_rev'] = String(3) "Yes"
        |    |    |    ['best_score_count'] = Integer(1) 2
        |    |    |    ['confidence'] = NULL(0) NULL
        |    |    |    ['mist_ppi'] = String(1) "-"
        |    |    |    ['mist_genetic'] = String(1) "-"
        |    |    |    ['orf_count'] = String(1) "0"
        |    |    |    ['geneid'] = String(5) "11651"
        |    |    |    ['species_id'] = String(5) "10090"
        |    |    |    ['symbol'] = String(4) "Akt1"
        |    |    |    ['species_specific_geneid'] = String(5) "87986"
        |    |    |    ['species_specific_geneid_type'] = String(3) "MGI"
        |    |    |    ['count'] = Integer(1) 7
        |    |    |    ['uniprot_pdb_counts'] = Integer(1) 0
        |    |    |    ['uniprot_dp_counts'] = Integer(1) 1
        |    |    |    ['publication_counts'] = Integer(4) 1504
        |    |    |    ['go_counts_components'] = Integer(1) 7
        |    |    |    ['go_counts_function'] = Integer(1) 5
        |    |    |    ['go_counts_process'] = Integer(2) 47
        |    |    |    ['disease_annotation'] = Integer(1) 0
        |    |    )
        |    |    ['100490038'] => Array (23)
        |    |    (.............

     */

    public function getDioptTable ($speciesID, $entrezGeneid , $targetSpeciesID) {

        //var_dump ($speciesID, $entrezGeneid, $targetSpeciesID);

        $pred_str ='';


        // Determine if we're looking for Orthologs or Paralogs and build the querry


        if ($targetSpeciesID ==0) {

            $sql = "select distinct geneid1, geneid2, prediction_method,  speciesid2 " .
                "from Ortholog_Pair " .
                "where speciesid1 =  :species1 and  speciesid1 !=  speciesid2 and  " .
                $pred_str .
                "geneid1 in (:genelist)";


            $paralog = "and  speciesid1 !=  speciesid2";

            $sql = <<<SQL
               select OP.geneid1, OP.geneid2, OP.speciesid1, OP.prediction_method, OP.speciesid2 , OPB.score, OPB.best_score, OPB.best_score_rev, OPB.confidence,
               G.geneid, G.speciesid, G.symbol, G.species_specific_geneid, G.species_specific_geneid_type 
               from Ortholog_Pair OP LEFT JOIN Ortholog_Pair_Best OPB ON
               OPB.geneid1 = OP.geneid1 AND
               OPB.geneid2 = OP.geneid2 AND
               OPB.species1 = OP.speciesid1 AND
               OPB.species2 = OP.speciesid2
               LEFT JOIN Gene_Information G ON G.geneid = OP.geneid2 
               where speciesid1 =  :species1  $paralog and  
                $pred_str 
                OP.geneid1 in (:genelist);
SQL;


            $STH = $this->dbo->prepare($sql);
            $STH->bindValue(':species1', $speciesID, \PDO::PARAM_STR);
            $STH->bindValue(':genelist', $entrezGeneid, \PDO::PARAM_STR);


        } else if ($targetSpeciesID == $speciesID) {

            $paralog = "and speciesid1 = speciesid2 ";

            $sql = <<<SQL
               select OP.geneid1, OP.geneid2, OP.speciesid1, OP.prediction_method, OP.speciesid2 , OPB.score, OPB.best_score, OPB.best_score_rev, OPB.confidence,
               G.geneid, G.speciesid, G.symbol, G.species_specific_geneid, G.species_specific_geneid_type 
               from Ortholog_Pair OP LEFT JOIN Ortholog_Pair_Best OPB ON
               OPB.geneid1 = OP.geneid1 AND
               OPB.geneid2 = OP.geneid2 AND
               OPB.species1 = OP.speciesid1 AND
               OPB.species2 = OP.speciesid2
               LEFT JOIN Gene_Information G ON G.geneid = OP.geneid2 
               where speciesid1 = speciesid2 and speciesid1 =  :species1  and  
                $pred_str 
                OP.geneid1 in (:genelist);
SQL;


            $STH = $this->dbo->prepare($sql);
            $STH->bindValue(':species1', $speciesID, \PDO::PARAM_STR);
            $STH->bindValue(':genelist', $entrezGeneid, \PDO::PARAM_STR);

        }


        // Get the MAIN DIOPT Data


        if ($STH->execute()) {
            $resultData = $STH->fetchall(\PDO::FETCH_ASSOC);
        } else {
            if ($this->sql_debug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
             return null;
        }

        if (empty($resultData)){
            return null;
        }



        // Get max score by species into array eg $maxScoreBySpecies[speciesID] = val

        $maxScoresBySpecies = array();

        foreach ($resultData as $oneResult) {

            $targetSpecies = $oneResult['speciesid2'];
            if (! isset($maxScoresBySpecies [$targetSpecies])){
                $maxScoresBySpecies [$targetSpecies] = -1;
            }
            if ( $maxScoresBySpecies [$targetSpecies] < $oneResult['score']){
                $maxScoresBySpecies [$targetSpecies] = $oneResult['score'];
            }
        }




        // loop amd create array : orthologs[gene1][gene2]


        $orthologs = array();
        $genelist = array();  // array of geneids for looking up other values

        $minScoreFilter = 2;

        $orfObj = new \DRSC\OrfLookup($this->dbo);

        foreach ($resultData as $oneResult){
            $bestScoreCount = 0;

            if ($oneResult['best_score']== 'Yes'){
                $bestScoreCount++;
            }
            if ($oneResult['best_score_rev']== 'Yes'){
                $bestScoreCount++;
            }

            //Filter


           // remove scores where the best score is less than the max.
            if ($oneResult['score'] <= $minScoreFilter ){
                $targetSpecies = $oneResult['speciesid2'];

                if ($oneResult['score'] < $maxScoresBySpecies[$targetSpecies] ) {
                    continue;
                }
            }

            // gene1 and gene2
            $g1 = $oneResult['geneid1'];
            $g2 = $oneResult['geneid2'];

            $ppiCount = '-';
            $geneticCount = '-';

            $orfCount = $orfObj->getOrfCounts($oneResult['speciesid'], $g2);

            $genelist[$g2] = intval($g2);

            if (!isset($orthologs[$g1])){
                $orthologs[$g1] = array();
            }
            if (!isset($orthologs[$g1][$g2])){
                $orthologs[$g1][$g2] = array();
            }

            $orthologs[$g1][$g2]['methods'][] = $oneResult['prediction_method'];
            $orthologs[$g1][$g2]['best_score'] = $oneResult['best_score'] ;
            $orthologs[$g1][$g2]['score'] = $oneResult['score'] ;
            $orthologs[$g1][$g2]['max_score'] = '' ;  // default to override later

            $orthologs[$g1][$g2]['best_score_rev'] = $oneResult['best_score_rev'] ;
            $orthologs[$g1][$g2]['best_score_count'] = $bestScoreCount ;
            $orthologs[$g1][$g2]['confidence'] = $oneResult['confidence'] ;

            $orthologs[$g1][$g2]['mist_ppi'] = $ppiCount ;
            $orthologs[$g1][$g2]['mist_genetic'] = $geneticCount;
            $orthologs[$g1][$g2]['orf_count'] = $orfCount;
            $orthologs[$g1][$g2]['geneid'] = $oneResult['geneid'];
            $orthologs[$g1][$g2]['species_id'] = $oneResult['speciesid'];
            $orthologs[$g1][$g2]['symbol'] = $oneResult['symbol'];
            $orthologs[$g1][$g2]['species_specific_geneid'] = $oneResult['species_specific_geneid'];
            $orthologs[$g1][$g2]['species_specific_geneid_type'] = $oneResult['species_specific_geneid_type'];

        }


        // Add additional data for  search gene.

        // Mist interactions, handled via Ajax now
        $ppiCount = '-';
        $geneticCount = '-';
        $orfCount = $orfObj->getOrfCounts($speciesID, $entrezGeneid);


        // looking up source geneid

        if ($targetSpeciesID != $speciesID) {
            $searchGeneInfo = \DRSC\GeneLookup::getGeneInfoFromEntrezID($this->dbo, $entrezGeneid);

            if (!empty($searchGeneInfo)) {
                $searchGeneInfo = $searchGeneInfo[0];
            } else {
                $searchGeneInfo = array('geneid' => '', 'speciesid' => '', 'symbol' => '', 'description' => '', 'locus_tag' => '', 'species_specific_geneid' => '', 'species_specific_geneid_type' => '', 'chromosome' => '', 'map_location' => '', 'gene_type' => '');
            }


            // Add back the search gene to the ortholog list

            $orthologs[$entrezGeneid][$entrezGeneid] = array('methods' => 'NA', 'best_score' => '-', 'score' => '', 'best_score_rev' => '-', 'confidence' => '', 'mist_ppi' => $ppiCount, 'mist_genetic' => $geneticCount, 'orf_count' => $orfCount, 'species_id' => $speciesID, 'geneid' => $entrezGeneid, 'symbol' => $searchGeneInfo['symbol'], 'species_specific_geneid' => $searchGeneInfo['species_specific_geneid'], 'species_specific_geneid_type' => $searchGeneInfo['species_specific_geneid_type']);

            $genelist[$entrezGeneid] = (int)$entrezGeneid;
        }


        //-------------------------------
        // GET OTHER COUNTS (Publications, GO, disease, etc.)


        $uniprotDataObj = new \dataModels\UniprotData($this->dbo);
        $publicationCountObj = new \dataModels\GeneToData($this->dbo);
        $maxGenesForPapers = 100;
        $publicationCounts = $publicationCountObj->getPublicationCounts($genelist, $maxGenesForPapers);

        $gene2GoCounts = $this->getGene2GoCounts($genelist);
        $uniprotCounts = $uniprotDataObj->getUniprotCountsByGene($genelist);
        $dioptDObject = new \dataModels\DioptDiseaseLookup($this->dbo);

        $g2dFilter = array('min-rank'=>'high');

        $diseaseCounts = $dioptDObject->geneToDiseaseCount($genelist, $g2dFilter );

        $diseaseAnnotationCounts = array();


        foreach ($diseaseCounts as $one){
            $key = $one['geneid'];
            $diseaseAnnotationCounts[$key]=$one;
        }

        $drscDioptObj = new \DRSC\DIOPT($this->dbo);

        $maxOrthologScoreArray = $drscDioptObj->getMaxScoreArrayForSpecies($speciesID);


        // Loop through and insert counts into the ortholog array

        foreach ($orthologs as $sourceKey => $oneSource){

            foreach ($oneSource as $resultKey => $oneResult){


                if (isset($oneResult['methods'])){
                    $count = count($oneResult['methods']);
                } else {
                    $count = 0;
                }
                $orthologs[$sourceKey][$resultKey]['count'] = $count;


                // Set max DIOPT score if available

                $sp = $oneResult['species_id'];

                if (isset($maxOrthologScoreArray[$sp] )){
                    $orthologs[$sourceKey][$resultKey]['max_score'] = $maxOrthologScoreArray[$sp]['max_score'];
                }


                // Set uniprot counts

                if (isset($uniprotCounts[$resultKey]['PDB'])){
                    $orthologs[$sourceKey][$resultKey]['uniprot_pdb_counts'] =  (int)($uniprotCounts[$resultKey]['PDB']);
                } else {
                    $orthologs[$sourceKey][$resultKey]['uniprot_pdb_counts'] = 0;
                }
                if (isset($uniprotCounts[$resultKey]['DISRUPTION PHENOTYPE'])){
                    $orthologs[$sourceKey][$resultKey]['uniprot_dp_counts'] =  (int)($uniprotCounts[$resultKey]['DISRUPTION PHENOTYPE']);
                } else {
                    $orthologs[$sourceKey][$resultKey]['uniprot_dp_counts'] = 0;
                }


                if (isset($publicationCounts[$resultKey])){
                    $orthologs[$sourceKey][$resultKey]['publication_counts'] =  (int)($publicationCounts[$resultKey][0]['count']);
                } else {
                    $orthologs[$sourceKey][$resultKey]['publication_counts'] = 0;
                }

                if (isset($gene2GoCounts[$resultKey])){
                    $orthologs[$sourceKey][$resultKey]['go_counts_components'] = $gene2GoCounts[$resultKey]['Component'];
                    $orthologs[$sourceKey][$resultKey]['go_counts_function'] = $gene2GoCounts[$resultKey]['Function'];
                    $orthologs[$sourceKey][$resultKey]['go_counts_process'] = $gene2GoCounts[$resultKey]['Process'];
                } else {
                    $orthologs[$sourceKey][$resultKey]['go_counts_components'] = 0;
                    $orthologs[$sourceKey][$resultKey]['go_counts_function'] = 0;
                    $orthologs[$sourceKey][$resultKey]['go_counts_process'] = 0;
                }

                if (isset($diseaseAnnotationCounts[$resultKey])){
                    $orthologs[$sourceKey][$resultKey]['disease_annotation'] = $diseaseAnnotationCounts[$resultKey]['count'];
                } else {
                    $orthologs[$sourceKey][$resultKey]['disease_annotation'] = 0;
                }

            }
        }


        return $orthologs;
    }


    /** Takes a list of geneIDs and returns an array of Go annotations for
     *  evidence types:  'EXP', 'IDA', 'IPI', 'IMP','IGI', 'IEP'
     *
     * @param $geneIDs
     * @return array|null
     */

     /* Return Data:
         * Array (10)
            (
            |    ['207'] => Array (3)
            |    (
            |    |    ['Component'] = Integer(1) 6
            |    |    ['Function'] = Integer(2) 11
            |    |    ['Process'] = Integer(2) 43
            |    )
            |    ['11651'] => Array (3)
            |    (
            |    |    ['Component'] = Integer(1) 7
            |    |    ['Function'] = Integer(1) 5
            |    |    ['Process'] = Integer(2) 47
            ............
      */


    public function getGene2GoCounts ($geneIDs){

        if (!is_array($geneIDs)){
            $geneIDs = array($geneIDs);
        }

        foreach ($geneIDs as $key => $val) {
            $geneIDs[$key] = $this->dbo->quote($val);
        }

        $searchIn = implode(',', $geneIDs);

        $sql = "SELECT geneID, category, count(*) as count FROM `Gene2Go` WHERE `GeneID` IN ($searchIn) and `evidence` in ('EXP', 'IDA', 'IPI', 'IMP','IGI', 'IEP'  )  GROUP BY geneID, category ";

        $STH = $this->dbo->prepare($sql);

        if ($STH->execute()) {
            $resultData = $STH->fetchall(\PDO::FETCH_ASSOC| \PDO::FETCH_GROUP);
        } else {
            if ($this->sql_debug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
            $resultData = null;
        }

        $counts = array();

        foreach ($resultData as $goKey => $goValue){

            foreach ($this->dataModel->goTypes as $one){
                $counts[$one]=0;
            }

            foreach ($goValue as $oneValue) {
                $newKey = $oneValue['category'];
                $newValue = $oneValue['count'];
                $counts[$newKey] = (int) $newValue;
            }

            $resultData[$goKey] = $counts;
        }

        return $resultData;

    }

}