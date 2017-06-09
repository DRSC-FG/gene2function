#
# Use the fishmine database to lookup phenotype or expression data.
#    (http://www.xenmine.org/)
# examples:
#
# counts:
#   python web_frogmine.py XB-GENE-5996034 counts
#   output: {"species_id": 8364, "count_phenotype": 9, "gene_id": "XB-GENE-5996034", "count_expression": 117}
#
# # expression:
#    python  web_frogmine.py XB-GENE-5996034 expression
#    output: [tab separated list]
#
# requires: intermine
#        (pip install intermine)

import sys
from Mine import Mine

reload(sys)
sys.setdefaultencoding('utf-8')

if (len(sys.argv) != 3):
    print "Usage: python", sys.argv[0], "<frog gene ID> <count|phenotype|expression> "

else:
    try:

        # Get command line arguments


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

    mineService = "http://www.xenmine.org/xenmine/service"

    phenoList = ["primaryIdentifier", "secondaryIdentifier", "ontologyAnnotations.ontologyTerm.identifier",
                 "ontologyAnnotations.ontologyTerm.namespace",
                 "ontologyAnnotations.ontologyTerm.name", "briefDescription", "symbol"]

    expList = ["primaryIdentifier", "secondaryIdentifier", "symbol", "name", "expressionScores.stage",
               "expressionScores.FPKM", "expressionScores.experiments.publication.pubMedId"]

    listList = {'expList': expList, 'phenoList': phenoList}

    mineX = Mine(mineService, 8364)

    searchFields = {'queryType': {'P': 'Gene', 'E': 'Gene'}, 'type': searchType,
                    'searchField': {'P': 'secondaryIdentifier', 'E': 'secondaryIdentifier'}, 'searchValue': geneID}

    # Run search and output to stdout
    mineX.get_data(searchFields, listList)
