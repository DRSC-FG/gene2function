<?php

namespace DRSC;




use Symfony\Component\Config\Definition\Exception\Exception;

/**
 *  wrapper class to lookup genes.
 *  based on existing perl scripts
 *     e.g. the Gene.pm code
 *
 * Class GeneLookup
 * @package DRSC
 *
 */


class GeneLookup {

    static $sqlDebug = false;


    /**
     *
     *
     *
     * @param $dbo
     * @param $fbgn
     * @return null
     *
     * Given a database object (PDO) and an FBGN, return symbol and tag values.
     */
    /* Result Data:
    Array (3)
    |    ['flybase_id'] = String(11) "FBgn0027885"
    |    ['symbol'] = String(5) "Aac11"
    |    ['tag_value'] = String(6) "CG6582"
    */

    public static function getGeneInfoFromFBgn(\PDO $dbo, $fbgn) {
        // Returns FBgn + gene symbol + annotation (CG) based on FBgn; if first query returns empty, run second query based
        // on assumption that symbol = CG to return gene info


        $sql = "SELECT flybase_id, symbol, tag_value FROM Flybase_Gene fb 
            LEFT JOIN Flybase_Gene_Tag gt ON fb.flybase_gene_id = gt.flybase_gene_id 
            WHERE fb.type != 'Gone' AND fb.flybase_id = :fbgn AND gt.tag_type = 'SYN' AND 
            gt.tag_value LIKE 'CG%' AND gt.tag_value NOT LIKE '%-%' 
            ORDER BY flybase_gene_tag_id DESC LIMIT 1; ";


        $STH = $dbo->prepare($sql);
        $STH->bindValue(':fbgn', $fbgn, \PDO::PARAM_STR);

        if ($STH->execute()) {
            $result = $STH->fetch(\PDO::FETCH_ASSOC);

            if (!empty($result)) {
                return $result;

            } else {

                // no results initially, try a second search

                $sql2 = "SELECT flybase_id, symbol, symbol AS 'tag_value' FROM Flybase_Gene 
                    WHERE flybase_id = :fbgn AND type != 'Gone'; ";

                $STH2 = $dbo->prepare($sql2);
                $STH2->bindValue(':fbgn', $fbgn, \PDO::PARAM_STR);

                $STH2->execute();
                $x = $STH2->fetch(\PDO::FETCH_ASSOC);
                return $x;
            }
        } else {
            if (self::sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
        }

        return null;
    }

    /**
     *
     * @param $dbo
     * @param $searchTerm (flybase gene indtifier)
     * @return array of matching genes
     *
     *
     */
    /* Example return data
      ['0'] Object
        |    (
        |    |    flybase_gene_id = String(3) "121"
        |    |    symbol = String(5) "Aac11"
        |    |    flybase_id = String(11) "FBgn0027885"
        |    )
        | .............
)
    */

    public static function getFBGNFromDatabase (\PDO $dbo, $searchTerm){

        $searchTerm = trim($searchTerm);
        $geneLookups = array();


        // if its a FBgn use this querey first
        if (preg_match("/^FBgn/i",$searchTerm)) {
            $geneLookups[] = "select distinct flybase_gene_id, symbol, flybase_id from Flybase_Gene where type = 'Active' and flybase_id = :string";
        }

        $geneLookups[] = "select distinct g.flybase_gene_id, g.symbol, g.flybase_id from Flybase_Gene g, Flybase_Gene_Tag t where g.type = 'Active' and g.flybase_gene_id = t.flybase_gene_id and t.tag_type = 'ID2' and t.tag_value = :string ";
        $geneLookups[] = "select distinct flybase_gene_id, flybase_id, symbol from Flybase_Gene where type = 'Active' and binary symbol = :string ";
        $geneLookups[] = "select distinct g.flybase_gene_id, g.flybase_id, g.symbol from Flybase_Gene g, Flybase_Gene_Tag t where g.type = 'Active'  and g.flybase_gene_id = t.flybase_gene_id and t.tag_type = 'NAM' and binary t.tag_value = :string ";
        $geneLookups[] = "select distinct g.flybase_gene_id, g.flybase_id, g.symbol from Flybase_Gene g, Flybase_Gene_Tag t where g.type = 'Active' and g.flybase_gene_id = t.flybase_gene_id and t.tag_type = 'SYN' and binary t.tag_value = :string ";
        $geneLookups[] = "select distinct flybase_gene_id, flybase_id, symbol from Flybase_Gene where type = 'Active' and symbol = :string ";
        $geneLookups[] = "select distinct g.flybase_gene_id, g.flybase_id, g.symbol from Flybase_Gene g, Flybase_Gene_Tag t where g.type = 'Active' and g.flybase_gene_id = t.flybase_gene_id and t.tag_type = 'NAM' and t.tag_value = :string ";
        $geneLookups[] = "select distinct g.flybase_gene_id, g.flybase_id, g.symbol from Flybase_Gene g, Flybase_Gene_Tag t  where g.type = 'Active'  and g.flybase_gene_id = t.flybase_gene_id and t.tag_type = 'SYN' and t.tag_value = :string ";


        $returnValue=array();

        // loop through all queries.

        foreach ($geneLookups as $oneSQLLookup){

            //only lookup if not found anything yet.. otherwise just go through the loop
            if (empty($returnValue)) {
                $STH = $dbo->prepare($oneSQLLookup);

                $STH->bindValue(':string', $searchTerm, \PDO::PARAM_STR);

                // run the query
                if ($STH->execute()) {
                    $returnValue = $STH->fetchAll(\PDO::FETCH_OBJ);

                } else {
                    if (self::sqlDebug) {
                        echo " Query didn't work : {$oneSQLLookup} \n";
                        print_r($STH->errorInfo());
                    }
                }
            }
        }

        return $returnValue;
    }

    /**
     *
     * @param $dbo
     * @param $eGeneID
     * @return null
     *
     * Given database reference (PDO) and a Entrez (NCBI) gene identifier return the symbol (embeded in array).
     *
     */
    /* Return Data:
    | Array (1)
    |    ['0'] => Array (1)
    |    (
    |    |    ['symbol'] = String(4) "INMT"
    |    )
    */


    public static function getGeneNameFromEntrezID(\PDO $dbo, $eGeneID) {

        $sql = "SELECT symbol FROM `Gene_Information` WHERE `geneid` = :geneid ";
        $STH = $dbo->prepare($sql);
        $STH->bindValue(':geneid', $eGeneID, \PDO::PARAM_STR);

        // run the query
        if ($STH->execute()) {
            $returnValue = $STH->fetchAll(\PDO::FETCH_ASSOC);

            //only return data if some found..
            if (!empty($returnValue)) {
                return $returnValue;
            }
        } else {
            if (self::sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
        }

        return null;
    }

    /**
     *
     * @param $dbo
     * @param $eGeneID
     * @return array null
     *
     * Takes database reference (PDO) and Entrez Gene ID and returns information as array
     *
     */
    /* Return Data:
        Array (1)
    (
    |    ['0'] => Array (10)
    |    (
    |    |    ['geneid'] = String(5) "11185"
    |    |    ['speciesid'] = String(4) "9606"
    |    |    ['symbol'] = String(4) "INMT"
    |    |    ['description'] = String(35) "indolethylamine N-methyltransferase"
    |    |    ['locus_tag'] = String(1) "-"
    |    |    ['species_specific_geneid'] = String(4) "6069"
    |    |    ['species_specific_geneid_type'] = String(4) "HGNC"
    |    |    ['chromosome'] = String(1) "7"
    |    |    ['map_location'] = String(6) "7p14.3"
    |    |    ['gene_type'] = String(14) "protein-coding"
    |    )
    |    ..............
    )
    */

    public static function getGeneInfoFromEntrezID(\PDO $dbo, $eGeneID) {

        $sql = "SELECT * FROM `Gene_Information` WHERE `geneid` = :geneid ";
        $STH = $dbo->prepare($sql);
        $STH->bindValue(':geneid', $eGeneID, \PDO::PARAM_STR);

        // run the query
        if ($STH->execute()) {
            $returnValue = $STH->fetchAll(\PDO::FETCH_ASSOC);

            //only return data if some found..
            if (!empty($returnValue)) {
                return $returnValue;
            }
        } else {
            if (self::sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
        }

        return null;
    }

    /**
     *
     * @param $dbo
     * @param $geneName
     * @param $speciesID
     * @return null
     *
     * Given a database reference (PDO), genename  and a species ID, return geneinformation.
     *
     */
    /* Return Data:
     * Array (1)
    (
    |    ['0'] => Array (10)
    |    (
    |    |    ['geneid'] = String(3) "207"
    |    |    ['speciesid'] = String(4) "9606"
    |    |    ['symbol'] = String(4) "AKT1"
    |    |    ['description'] = String(29) "AKT serine/threonine kinase 1"
    |    |    ['locus_tag'] = String(1) "-"
    |    |    ['species_specific_geneid'] = String(3) "391"
    |    |    ['species_specific_geneid_type'] = String(4) "HGNC"
    |    |    ['chromosome'] = String(2) "14"
    |    |    ['map_location'] = String(8) "14q32.33"
    |    |    ['gene_type'] = String(14) "protein-coding"
    |    )
)
     *
     */

    public static function getEntrezIDFromGeneName(\PDO $dbo, $geneName, $speciesID) {


        $sql = "SELECT * FROM `Gene_Information` WHERE `symbol` = :symbol and `speciesid` = :speciesid";
        $STH = $dbo->prepare($sql);
        $STH->bindValue(':symbol', $geneName, \PDO::PARAM_STR);
        $STH->bindValue(':speciesid', $speciesID, \PDO::PARAM_STR);

        // run the query
        if ($STH->execute()) {
            $returnValue = $STH->fetchAll(\PDO::FETCH_ASSOC);

            //only return data if some found..
            if (!empty($returnValue)) {
                return $returnValue;
            }
        } else {
            if (self::sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
        }

        return null;
    }

    /**
     *
     *
     *
     * @param \PDO $dbo
     * @param $searchTerm
     * @param $speciesID
     * @return array|null
     *
     * Given a database reference (PDO) and a search string, returns array with information about the gene
     * Note does exact match first and if none found, expands to search
     */
    /* Result Data:

        Array (1)
        (
        |    ['0'] => Array (7)
        |    (
        |    |    ['genemappingid'] = String(6) "753466"
        |    |    ['speciesid'] = String(4) "9606"
        |    |    ['geneid'] = String(3) "207"
        |    |    ['idtype'] = String(9) "Gene name"
        |    |    ['idvalue'] = String(4) "AKT1"
        |    |    ['idvalue2'] = String(4) "AKT1"
        |    |    ['extra_info'] = String(22) "Entrez official symbol"
        |    )
             ............
        )
    */


    public static function getGeneIDFromName (\PDO $dbo, $searchTerm, $speciesID){

        // Search using =

        $sql='SELECT * FROM `GeneID_Mapping` WHERE `idvalue` = :search_term and `speciesid`= :species_id  LIMIT 150';

        $STH = $dbo->prepare($sql);
        $STH->bindValue(':search_term', $searchTerm, \PDO::PARAM_STR);
        $STH->bindValue(':species_id', $speciesID, \PDO::PARAM_INT);

        // run the query
        if ($STH->execute()) {
            $returnValue = $STH->fetchAll(\PDO::FETCH_ASSOC);

            //only return data if some found..
            if (!empty($returnValue)) {
                return $returnValue;
            }
        } else {
            if (self::sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
        }



        // Expand Search using Like when none found.

        $sql='SELECT * FROM `GeneID_Mapping` WHERE `idvalue` like :search_term and `speciesid`= :species_id LIMIT 150';



        $STH = $dbo->prepare($sql);
        $searchTerm = "%{$searchTerm}%";
        $STH->bindValue(':search_term', $searchTerm, \PDO::PARAM_STR);
        $STH->bindValue(':species_id', $speciesID, \PDO::PARAM_INT);
        // run the query
        if ($STH->execute()) {
            $returnValue = $STH->fetchAll(\PDO::FETCH_ASSOC);

            //only return data if some found..
            if (!empty($returnValue)) {
                return $returnValue;
            }
        }else {
            if (self::sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
        }



        return null;
    }


    /**
     *
     *
     *
     * @param \PDO $dbo
     * @param $searchTerm
     * @param $speciesID
     * @return array|null
     *
     * Given a database reference (PDO) and a search string, returns array with information about the gene
     * Note does exact match first and if none found, expands to search
     * Similar to "getGeneIDFromName" function but includes gene symbol
     */

    /* Result Data:
        Array (1)
        (
        |    ['0'] => Array (8)
        |    (
        |    |    ['genemappingid'] = String(6) "753466"
        |    |    ['speciesid'] = String(4) "9606"
        |    |    ['geneid'] = String(3) "207"
        |    |    ['idtype'] = String(9) "Gene name"
        |    |    ['idvalue'] = String(4) "AKT1"
        |    |    ['idvalue2'] = String(4) "AKT1"
        |    |    ['extra_info'] = String(22) "Entrez official symbol"
        |    |    ['symbol'] = String(4) "AKT1"
        |    )
        )
    */


    public static function getGeneIDFromNameWithSymbol (\PDO $dbo, $searchTerm, $speciesID){

        // Search using =

        $sql='SELECT GeneID_Mapping.*, Gene_Information.symbol FROM `GeneID_Mapping` left Join Gene_Information ON Gene_Information.geneid = GeneID_Mapping.geneid   WHERE `GeneID_Mapping`.`idvalue` = :search_term and `GeneID_Mapping`.`speciesid`= :species_id  LIMIT 150';

        $STH = $dbo->prepare($sql);
        $STH->bindValue(':search_term', $searchTerm, \PDO::PARAM_STR);
        $STH->bindValue(':species_id', $speciesID, \PDO::PARAM_INT);

        // run the query
        if ($STH->execute()) {
            $returnValue = $STH->fetchAll(\PDO::FETCH_ASSOC);

            //only return data if some found..
            if (!empty($returnValue)) {
                return $returnValue;
            }
        } else {
            if (self::sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
        }

        // Expand Search using Like when none found.

        $sql='SELECT GeneID_Mapping.*, Gene_Information.symbol FROM `GeneID_Mapping` left Join Gene_Information ON Gene_Information.geneid = GeneID_Mapping.geneid   WHERE `GeneID_Mapping`.`idvalue` like :search_term and `GeneID_Mapping`.`speciesid`= :species_id LIMIT 150';


        $STH = $dbo->prepare($sql);
        $searchTerm = "%{$searchTerm}%";
        $STH->bindValue(':search_term', $searchTerm, \PDO::PARAM_STR);
        $STH->bindValue(':species_id', $speciesID, \PDO::PARAM_INT);
        // run the query
        if ($STH->execute()) {
            $returnValue = $STH->fetchAll(\PDO::FETCH_ASSOC);

            //only return data if some found..
            if (!empty($returnValue)) {
                return $returnValue;
            }
        } else {
            if (self::sqlDebug) {
                echo " Query didn't work : {$sql} \n";
                print_r($STH->errorInfo());
            }
        }



        return null;
    }

}