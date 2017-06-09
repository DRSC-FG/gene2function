-- Table definitions for gene2function

--
-- Table structure for table `Gene_Information`
--

CREATE TABLE `Gene_Information` (
  `geneid` int(11) NOT NULL,
  `speciesid` int(11) DEFAULT NULL,
  `symbol` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `description` text CHARACTER SET latin1,
  `locus_tag` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `species_specific_geneid` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `species_specific_geneid_type` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `chromosome` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `map_location` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `gene_type` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`geneid`),
  KEY `symbol` (`symbol`)
) 
--
-- Table structure for table `GeneID_Mapping`
--


CREATE TABLE `GeneID_Mapping` (
  `genemappingid` int(11) NOT NULL AUTO_INCREMENT,
  `speciesid` int(11) DEFAULT NULL,
  `geneid` int(11) DEFAULT NULL,
  `idtype` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `idvalue` varchar(512) CHARACTER SET latin1 DEFAULT '' COMMENT 'Not Case Sensitive',
  `idvalue2` varchar(512) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL COMMENT 'Case Sensitive',
  `extra_info` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`genemappingid`),
  KEY `GIM_typevalue` (`idtype`,`idvalue`(128)),
  KEY `GIM_idvalue` (`idvalue`(128)),
  KEY `geneid` (`geneid`),
  KEY `idvalue2` (`idvalue2`)
) ENGINE=InnoDB AUTO_INCREMENT=21333586 DEFAULT CHARSET=utf8;


--
-- Table structure for table `Disease`
--


CREATE TABLE `Disease` (
  `diseaseid` int(11) NOT NULL,
  `disease_term` varchar(256) DEFAULT NULL,
  `source` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`diseaseid`)
)

--
-- Table structure for table `Disease_Gene_Association`
--

CREATE TABLE `Disease_Gene_Association` (
  `DG_associationid` int(11) NOT NULL,
  `diseaseid` int(11) DEFAULT NULL,
  `speciesid` int(11) DEFAULT NULL,
  `geneid` int(11) DEFAULT NULL,
  `source` varchar(16) DEFAULT NULL,
  `rank` varchar(16) DEFAULT 'high',
  PRIMARY KEY (`DG_associationid`)
)

--
-- Table structure for table `Ortholog_Max_Score`
--

DROP TABLE IF EXISTS `Ortholog_Max_Score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ortholog_Max_Score` (
  `species1` int(11) NOT NULL DEFAULT '0',
  `species2` int(11) NOT NULL DEFAULT '0',
  `common_name` varchar(64) DEFAULT NULL,
  `species_name` varchar(128) DEFAULT NULL,
  `max_score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Ortholog_Pair_Best`
--


CREATE TABLE `Ortholog_Pair_Best` (
  `species1` int(11) NOT NULL DEFAULT '0',
  `geneid1` int(11) NOT NULL DEFAULT '0',
  `species2` int(11) NOT NULL DEFAULT '0',
  `geneid2` bigint(11) NOT NULL DEFAULT '0',
  `score` int(11) DEFAULT NULL,
  `best_score` enum('Yes','No','Yes_Adjusted') CHARACTER SET utf8 DEFAULT NULL,
  `best_score_rev` enum('Yes','No') CHARACTER SET utf8 DEFAULT NULL,
  `confidence` enum('high','low','moderate') CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`species1`,`geneid1`,`species2`,`geneid2`),
  KEY `geneid1` (`geneid1`),
  KEY `geneid2` (`geneid2`)
) 

--
-- Table structure for table `Ortholog_Pair`
--


CREATE TABLE `Ortholog_Pair` (
  `ortholog_pairid` int(11) NOT NULL AUTO_INCREMENT,
  `speciesid1` int(11) DEFAULT NULL,
  `geneid1` int(11) DEFAULT NULL,
  `speciesid2` int(11) DEFAULT NULL,
  `geneid2` int(11) DEFAULT NULL,
  `prediction_method` varchar(50) DEFAULT NULL,
  `blast_score` double DEFAULT NULL,
  `orig_score` double DEFAULT NULL,
  `orig_score_info` varchar(100) DEFAULT NULL,
  `orig_clusterid` varchar(100) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`ortholog_pairid`),
  KEY `OP_speciesgene1` (`speciesid1`,`geneid1`),
  KEY `OP_geneid1` (`geneid1`)
)

--
-- Table structure for table `Orf_Clones`
--

CREATE TABLE `Orf_Clones` (
  `species_id` int(11) DEFAULT NULL,
  `gene_id` int(11) DEFAULT NULL,
  `genbank_accession` varchar(255) DEFAULT NULL,
  `vector` varchar(255) DEFAULT NULL,
  `stop_codon_status` varchar(255) DEFAULT NULL,
  `plasmid_id` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL
)

-- Table structure for table `Gene_Pubmed`
--


CREATE TABLE `Gene_Pubmed` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tax_id` int(11) DEFAULT NULL,
  `gene_id` int(11) DEFAULT NULL,
  `pmid` int(11) DEFAULT NULL,
  `genes_per_publication` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gene_id` (`gene_id`)
)

--
-- Table structure for table `Uniprot`
--


CREATE TABLE `Uniprot` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `species_id` int(11) DEFAULT NULL,
  `gene_id` bigint(11) DEFAULT NULL,
  `uniprot_entry` varchar(255) DEFAULT NULL,
  `uniprot_entryname` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `uniprot_proteinname` varchar(32767) CHARACTER SET latin1 DEFAULT NULL,
  `uniprot_infotype` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `uniprot_infovalue` text CHARACTER SET latin1,
  `url` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `speciesid` (`species_id`),
  KEY `geneid` (`gene_id`)
)

--
-- Table structure for table `Gene2Go`
--

CREATE TABLE `Gene2Go` (
  `tax_id` int(11) DEFAULT NULL,
  `GeneID` int(11) DEFAULT NULL,
  `GO_ID` varchar(255) DEFAULT NULL,
  `evidence` varchar(255) DEFAULT NULL,
  `qualifier` varchar(255) DEFAULT NULL,
  `GO_term` varchar(255) DEFAULT NULL,
  `pubmed` varchar(1024) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  KEY `tax_id` (`tax_id`),
  KEY `GeneID` (`GeneID`)
)

--
-- Table structure for table `Species`
--


CREATE TABLE `Species` (
  `speciesid` int(11) DEFAULT NULL,
  `species_name` varchar(128) DEFAULT NULL,
  `short_species_name` varchar(5) DEFAULT NULL,
  `common_name` varchar(64) DEFAULT NULL,
  `species_specific_geneid_type` varchar(100) DEFAULT NULL,
  `species_specific_database_URL` varchar(255) DEFAULT NULL,
  `species_specific_URL_format` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT NULL
)

--
-- Table structure for table `Flybase_Gene`
--

CREATE TABLE `Flybase_Gene` (
  `flybase_gene_id` int(11) NOT NULL AUTO_INCREMENT,
  `flybase_id` varchar(11) NOT NULL DEFAULT '',
  `flybase_id_number` int(11) DEFAULT NULL COMMENT 'just the integer part of the flybase ID',
  `symbol` varchar(32) NOT NULL DEFAULT '',
  `type` enum('Active','Gone','Combined') DEFAULT NULL,
  PRIMARY KEY (`flybase_gene_id`),
  KEY `fg_flybase_id` (`flybase_id`),
  KEY `flybase_id_number` (`flybase_id_number`)
) 

--
-- Table structure for table `Flybase_Gene_Tag`
--

CREATE TABLE `Flybase_Gene_Tag` (
  `flybase_gene_tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `flybase_gene_id` int(11) NOT NULL DEFAULT '0',
  `tag_type` enum('ASQ','GO','CLA','DBA','DT','ID2','PAC','SYN','TE','NAM','PDOM','DHO') DEFAULT NULL,
  `tag_value` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`flybase_gene_tag_id`),
  KEY `fgt_tag_value` (`tag_value`),
  KEY `fgt_tag_type` (`tag_type`),
  KEY `fgt_flybase_gene_id` (`flybase_gene_id`)
) 

--
-- Table structure for table `Protein_Pair`
--


CREATE TABLE `Protein_Pair` (
  `protein_pair_id` int(11) NOT NULL AUTO_INCREMENT,
  `proteinid1` int(11) NOT NULL,
  `geneid1` int(11) DEFAULT NULL,
  `status1` varchar(50) DEFAULT NULL,
  `proteinid2` int(11) NOT NULL,
  `geneid2` int(11) DEFAULT NULL,
  `status2` varchar(50) DEFAULT NULL,
  `align_seq1` text,
  `start_pos1` int(11) DEFAULT NULL,
  `end_pos1` int(11) DEFAULT NULL,
  `align_seq2` text,
  `start_pos2` int(11) DEFAULT NULL,
  `end_pos2` int(11) DEFAULT NULL,
  `match_seq` text,
  `align_score` float DEFAULT NULL,
  `align_identity` float DEFAULT NULL,
  `align_length` int(11) DEFAULT NULL,
  `align_similarity` float DEFAULT NULL,
  `align_gaps` float DEFAULT NULL,
  `align_identity_count` int(11) DEFAULT NULL,
  `align_similarity_count` int(11) DEFAULT NULL,
  `align_gaps_count` int(11) DEFAULT NULL,
  `comment` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`proteinid1`,`proteinid2`),
  KEY `geneid1` (`geneid1`),
  KEY `protein_pair_id` (`protein_pair_id`)
)


