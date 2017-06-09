<?php

namespace dataModels;

/**
 * Class DataFields
 * @package dataModels
 *
 * Constants array and
 *
 *
 *
 */


class DataFields {

    private $dbo;
    public $sql_debug = false;
    private $orderby='';
    public $filterLabel;
    public $goTypes;
    public $reportTypes;
    public $speciesInfo;


    public function __construct(\PDO $pdoObj) {
        $this->dbo = $pdoObj;

        /* These are the diopt filter types */
        $this->filterLabel = array('None' => 'None', 'Best' => 'Best', 'NoLow' => 'No Low',
            'AboveTwo' => 'Score Above Two', 'HighRank' => 'High Rank');

        /* The mine reports come in two types */
        $this->reportTypes = array('phenotype','expression');

        /* GO annotation types */
        $this->goTypes = array('Component', 'Function' ,'Process');

        /* species Info and which scripts to execute for shell, and if lookup is based on ncbi id vs species specific type */
        $this->speciesInfo =  array(7227 =>array('name' =>'fly',  'script'=>'flymine_run.sh', 'use_species_geneid'=>true  ),
            4932 => array ('name' =>'yeast',  'script'=>'yeastmine_run.sh', 'use_species_geneid'=>true ),
            4896 => array ('name' =>'pombe',  'script'=>'-', 'use_species_geneid'=>true ),
            6239 => array ('name' =>'worm',   'script'=>'wormmine_run.sh', 'use_species_geneid'=>true ),
            7955 => array ('name' =>'fish',   'script'=>'fishmine_run.sh', 'use_species_geneid'=>true ),
            8364 => array ('name' =>'frog',   'script'=>'frogmine_run.sh', 'use_species_geneid'=>true ),
            9606 => array ('name' =>'human',  'script'=>'humanmine_run.sh', 'use_species_geneid'=>false ),
           10090 => array ('name' => 'mouse', 'script'=>'mousemine_run.sh', 'use_species_geneid'=>true )
        );
    }

}