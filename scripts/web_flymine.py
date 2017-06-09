#
# Use the flymine database to lookup phenotype or expression data.
#    (http://www.flymine.org/)
# examples:
#
#   counts:
#     python python web_flymine.py  FBgn0000382  counts
#     output:{"species_id": 7227, "count_phenotype": 204, "gene_id": "FBgn0000382", "count_expression": 104}
#
#   expression
#      python web_flymine.py  FBgn0000382 expression
#      output: [tab separated list]
#
# requires: intermine
#        (pip install intermine)

import sys
from Mine import Mine

reload(sys)
sys.setdefaultencoding('utf-8')

if (len(sys.argv) != 3):
    print "Usage: python", sys.argv[0], "<fly gene ID> <count|phenotype|expression> "

else:

    # Get command line arguments

    try:
        geneID = str(sys.argv[1])

    except ValueError:
        print "\nError reading value 1",
        sys.exit(0)  # Canopy doesn't like this call

    try:
        searchType = str(sys.argv[2])
        if searchType != 'phenotype' and searchType != 'expression':
            searchType = 'count'

    except ValueError:
        print "\nError reading value 2... setting to count",
        searchType = 'count'

    geneID = geneID.strip()

    mineService = "http://www.flymine.org/flymine/service"

    phenoList = ["primaryIdentifier", "secondaryIdentifier", "alleles.primaryIdentifier",
                 "alleles.secondaryIdentifier", "alleles.alleleClass",
                 "alleles.phenotypeAnnotations.annotationType",
                 "alleles.phenotypeAnnotations.description", "alleles.organism.name"]

    expList = ["primaryIdentifier", "symbol", "rnaSeqResults.stage",
               "rnaSeqResults.expressionScore", "rnaSeqResults.expressionLevel"]

    listList = {'expList': expList, 'phenoList': phenoList}

    mineX = Mine(mineService, 7227)

    searchFields = {'queryType': {'P': 'Gene', 'E': 'Gene'}, 'type': searchType,
                    'searchField': {'P': 'primaryIdentifier', 'E': 'primaryIdentifier'}, 'searchValue': geneID}

    # Run search and output to stdout
    mineX.get_data(searchFields, listList)
