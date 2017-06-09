#
# Use the flymine database to lookup phenotype or expression data.
#    (http://www.mousemine.org/)
# examples:
#
#   counts:
#     python python web_mousemine.py MGI:102523 counts
#     output:{"species_id": 10090, "count_phenotype": 112, "gene_id": "MGI:102523", "count_expression": 145}
#
#   expression
#      python web_mousemine.py MGI:102523 expression
#      output: [tab separated list]
#
# requires: intermine
#        (pip install intermine)


import sys      # Used for Command Line Parameters
import json
from Mine import Mine

reload(sys)
sys.setdefaultencoding('utf-8')




if (len(sys.argv)!= 3):

    print "Usage: python", sys.argv[0], "<mouse geneID> <{count|phenotype|expression}> "

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


    geneID = geneID.strip();

    mineService = "http://www.mousemine.org/mousemine/service"

    phenoList = ["subject.primaryIdentifier",
    "ontologyTerm.identifier",
    "ontologyTerm.name", "evidence.publications.pubMedId",
    "evidence.comments.type", "evidence.comments.description"]


    expList = [ "assayType", "feature.symbol", "feature.primaryIdentifier", "stage", "age",
    "structure.name", "strength", "pattern", "genotype.symbol", "sex",
    "assayId", "probe", "image", "publication.mgiJnum"]

    listList = {'expList': expList, 'phenoList': phenoList}

    mineX = Mine(mineService, 10090)

    searchFields = {'queryType': {'P':'OntologyAnnotation','E':'GXDExpression'},  'type': searchType, 'searchField': {'P':'subject.primaryIdentifier','E':'feature.primaryIdentifier'}, 'searchValue': geneID}

    # Run search and output to stdout
    mineX.get_data( searchFields, listList)


