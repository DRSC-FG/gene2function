<?php namespace dataModels;

/**
 * Class UniprotData
 * @package dataModels
 *
 * This class is responsible for grabbing data from a table with UniProt data. The table is expected to at least have
 * fields that contain:
 *   1. gene IDs
 *   2. UniProt phenotype annotations
 *   3. species IDs
 */
class UniprotData {
    private $dbo;
    public $uniprotInfoTypes;
    private $gene_table;
    private $gene_id;
    private $uniprot_type;
    private $species_id;

    /**
     * UniprotData constructor.
     * @param \PDO $pdoObj
     */
    public function __construct(\PDO $pdoObj) {
        $this->dbo = $pdoObj;

        /** Replace with your list of interested annotations */
        $this->uniprotInfoTypes = array('pdb' => 'PDB' , 'disruption_phenotype' => 'DISRUPTION PHENOTYPE');

        /** Replace with your uniprot table name */
        $this->gene_table = "Uniprot";

        /** Replace with your column name for gene IDs */
        $this->gene_id = "gene_id";

        /** Replace with your column name for phenotype annotations */
        $this->uniprot_type = "uniprot_infotype";

        /** Replace with your column name for species IDs */
        $this->species_id = "species_id";
    }

    /**
     * @param $speciesID (int)
     * @param $geneID (int)
     * @param $type (string)
     * @return array|null
     *
     * This will return all UniProt data of specific species + gene ID + annotation.
     */
    public function getUniprotData($speciesID, $geneID, $type) {
        if (! array_key_exists($type, $this->uniprotInfoTypes)){
            return null;
        }

        $sql = "SELECT * FROM $this->gene_table WHERE $this->uniprot_type = :type AND $this->gene_id = :gene_id 
            AND $this->species_id = :species_id";

        $STH = $this->dbo->prepare($sql);

        $STH->bindValue(':type', $this->uniprotInfoTypes[$type], \PDO::PARAM_STR);
        $STH->bindValue(':gene_id', $geneID, \PDO::PARAM_INT);
        $STH->bindValue(':species_id', $speciesID, \PDO::PARAM_INT);

        if ($STH->execute()) {
            return $STH->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            return null;
        }
    }

    /**
     * @param $geneIDs (array of key-value pairs)
     * @return array
     *
     * This will return a 2D array of UniProt counts grouped by gene IDs, then annotation.
     */
    public function getUniprotCountsByGene($geneIDs){
        if (!is_array($geneIDs)){
           $geneIDs = array($geneIDs);
        }

        foreach ($geneIDs as $key => $val) {
            $geneIDs[$key] = $this->dbo->quote($val);
        }
        $searchIn = implode(',', $geneIDs);

        $sql = "SELECT $this->gene_id, $this->uniprot_type, count(*) as count FROM $this->gene_table 
            WHERE $this->gene_id IN ($searchIn) GROUP BY $this->gene_id, $this->uniprot_type";

        $STH = $this->dbo->prepare($sql);

        if ($STH->execute()) {
            $results = $STH->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $results = null;
        }

        $resultsOrganized = array();
        foreach ($results as $result) {
            $geneid = $result[$this->gene_id];
            $type = $result[$this->uniprot_type];

            if (!isset ($resultsOrganized[$geneid])){
                $resultsOrganized[$geneid] = array();
            }
            $resultsOrganized[$geneid][$type] = $result['count'];
        }

        return $resultsOrganized;
    }
}