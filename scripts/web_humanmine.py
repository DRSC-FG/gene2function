#
# Use the worm mine database to lookup phenotype or expression data.
#    (http://www.humanmine.org/)
#
# examples:
#
#   counts:
#     python  python web_humanmine.py  5781  counts
#     output: {"species_id": 9606, "count_phenotype": 0, "gene_id": "5781", "count_expression": 1816}
#
#   expression: 
#     python atlasExpression.condition
#     output: [tab separated list]
#
# requires: python web_humanmine.py  5781  expression
#        (pip install intermine)



import sys      # Used for Command Line Parameters
import json
from Mine import Mine

reload(sys)
sys.setdefaultencoding('utf-8')




if (len(sys.argv) != 3):

    print "Usage: python", sys.argv[0], "<human geneID(Entrez) {count|phenotype|expression}> "

else:

    # Get command line arguments

    try:
        geneID = str(sys.argv[1])

    except ValueError:              # try-except catches errors
        print "\nError reading value 1",
        sys.exit(0)                 # Canopy doesn't like this call


    try:
        searchType = str(sys.argv[2])

        if searchType != 'phenotype' and searchType != 'expression' :

            #print "Setting to count"
            searchType = 'count'

    except ValueError:  # try-except catches errors
        print "\nError reading value 2... setting to count",
        searchType = 'count'

    #create variables need to do search

    geneID = geneID.strip();

    mineService = "http://www.humanmine.org/humanmine/service"
    phenoList = ["primaryIdentifier", "symbol", "diseases.hpoAnnotations.hpoTerm.name",
               "diseases.publications.pubMedId"]

    expList = ["symbol", "name", "organism.name", "atlasExpression.condition",
               "atlasExpression.type", "atlasExpression.tStatistic",
               "atlasExpression.pValue", "atlasExpression.expression"]

    listList = {'expList': expList, 'phenoList': phenoList}

    mineX = Mine(mineService, 9606)

    searchFields = {'queryType': {'P':'Gene','E':'Gene'}, 'type': searchType, 'searchField': {'P':'primaryIdentifier','E':'primaryIdentifier'}, 'searchValue': geneID}

    # Run search and output to stdout
    mineX.get_data(searchFields, listList)
