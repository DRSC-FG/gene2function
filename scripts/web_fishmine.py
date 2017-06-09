#
# Use the fishmine database to lookup phenotype or expression data.
#    (http://www.zebrafishmine.org/)
# examples:
#
# counts:
#   python web_fishmine.py ZDB-GENE-030131-7513 counts
#   output: {"species_id": 7955, "count_phenotype": 0, "gene_id": "ZDB-GENE-030131-7513", "count_expression": 15}
# expression: 
#    python web_fishmine.py ZDB-GENE-030131-7513 expression
#    output: [tab separated list]
#
# requires: intermine
#        (pip install intermine)

import sys
from Mine import Mine

reload(sys)
sys.setdefaultencoding('utf-8')

if (len(sys.argv) != 3):
    print "Usage: python", sys.argv[0], "<fish gene ID> <count|phenotype|expression> "

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

    mineService = "http://www.zebrafishmine.org/service"

    phenoList = ["fish.genotype.features.genes.symbol", "fish.genotype.features.genes.name",
                 "fish.genotype.features.genes.primaryIdentifier", "fish.wildtype", "superTerm.name",
                 "subTerm.name", "phenotypeTerm.name", "superTerm2.name", "subTerm2.name",
                 "tag", "fish.genotype.features.symbol", "fish.name",
                 "figure.publication.primaryIdentifier", "phenotypeIsMonogenic"]

    expList = ["primaryIdentifier", "symbol", "name", "primaryIdentifier", "expressions.expressionFound",
               "expressions.anatomy.name", "expressions.subterm.name",
               "expressions.fish.name", "expressions.startStage.name",
               "expressions.endStage.name", "expressions.publication.primaryIdentifier", "expressions.fish.wildtype",
               "expressions.environment.StandardEnvironment"]

    listList = {'expList': expList, 'phenoList': phenoList}

    mineX = Mine(mineService, 7955)

    searchFields = {'queryType': {'P': 'Phenotype', 'E': 'Gene'}, 'type': searchType,
                    'searchField': {'P': 'fish.genotype.features.genes.primaryIdentifier', 'E': 'primaryIdentifier'},
                    'searchValue': geneID}

    # Run search and output to stdout
    mineX.get_data(searchFields, listList)
