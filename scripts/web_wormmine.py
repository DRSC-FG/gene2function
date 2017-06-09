#
# Use the worm mine database to lookup phenotype or expression data.
#    (http://wormbase.org)
#
# examples:
#
#   counts:
#     python web_wormmine.py  WBGene00004214 counts
#     output: {"species_id": 6239, "count_phenotype": 0, "gene_id": "WBGene00004214", "count_expression": 0}
#
#   expression: 
#     python web_wormmine.py  WBGene00004214 expression
#     output: [tab separated list]
#
# requires: intermine
#        (pip install intermine)


import sys      # Used for Command Line Parameters
import json
from Mine import Mine

reload(sys)
sys.setdefaultencoding('utf-8')

# Check argument counts

if (len(sys.argv) != 3):

    print "Usage: python", sys.argv[0], "<worm gene id> <{count|phenotype|expression}> "

else:

    # get input from command line params
      
    try:
        geneID = str(sys.argv[1])

    except ValueError:              # try-except catches errors
        print "\nError reading value 1",
        sys.exit(0)                 # Canopy doesn't like this call

    try:
        searchType = str(sys.argv[2])

        if searchType != 'phenotype' and searchType != 'expression' :

            searchType = 'count'

    except ValueError:  # try-except catches errors
        print "\nError reading value 2... setting to count",
        searchType = 'count'


    #setup search parameters

    geneID = geneID.strip();

    mineService = "http://im-257.wormbase.org/tools/wormmine/service"

    phenoList = ["primaryIdentifier", "secondaryIdentifier", "symbol",
    "alleles.primaryIdentifier", "alleles.symbol",
    "alleles.phenotypesObserved.identifier", "alleles.phenotypesObserved.name"]


    expList = ["primaryIdentifier", "symbol",
    "expressionPatterns.lifeStages.anatomyTerms.primaryIdentifier",
    "expressionPatterns.lifeStages.anatomyTerms.name",
    "expressionPatterns.lifeStages.anatomyTerms.synonym"]

    listList = {'expList': expList, 'phenoList': phenoList}

    mineX = Mine(mineService, 6239)

    searchFields = {'queryType': {'P':'Gene.symbol','E':'Gene.symbol'}, 'type': searchType, 'searchField': {'P':'primaryIdentifier','E':'primaryIdentifier'}, 'searchValue': geneID}

    # Run search and output to stdout
    mineX.get_data( searchFields, listList)


