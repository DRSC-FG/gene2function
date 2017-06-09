import json
import sys

from intermine.webservice import Service

reload(sys)
sys.setdefaultencoding('utf-8')


class Mine(object):
    """Base class for mining genetic data of species

    Instead of handling one type of search, it handles 3:
        * phenotype - returns tab-separated results to STDOUT
        * expression - returns tab-separated results to STDOUT
        * counts - returns JSON of counts (number of rows) that pertain to querying phenotype and expression
            eg: {"species_id": 9606, "count_phenotype": 309, "gene_id": "5468", "count_expression": 376}"""

    def __init__(self, service, speciesID):
        self.service = service
        self.speciesID = speciesID


    def get_data(self, searchFields, fieldList):
        """Mines data then prints to STDOUT
        :param searchFields: (dictionary)
        :param fieldList:
        :return:"""

        service = Service(self.service)
        search_type = searchFields['type']
        geneID = searchFields['searchValue']
        searchFieldP = searchFields['searchField']['P']
        searchFieldE = searchFields['searchField']['E']
        queryTypeP = searchFields['queryType']['P']
        queryTypeE = searchFields['queryType']['E']

        # ---------------------------------
        # Get the Data
        # ---------------------------------
        # Phenotype
        if search_type == 'count' or search_type == 'phenotype':
            query = service.new_query(queryTypeP)
            expList = fieldList['phenoList']
            query.add_view(expList)

            # Mining for fish data requires extra constraints and joins
            if self.speciesID == 7955:
                query.add_constraint("tag", "=", "abnormal", code="A")
                query.add_constraint("fish.wildtype", "=", "false", code="B")
                query.add_constraint("phenotypeIsMonogenic", "=", "t", code="C")
                query.add_constraint(searchFieldP, "=", geneID, code="D")
                query.add_constraint("fish.STRs.symbol", "IS NULL", code="E")
                query.outerjoin("subTerm")
                query.outerjoin("superTerm2")
                query.outerjoin("subTerm2")
            else:
                query.add_constraint(searchFieldP, "=", geneID, code="A")

            if search_type == 'count':
                count_phenotype = query.count()

        # Expression
        if search_type == 'count' or search_type == 'expression':
            query = service.new_query(queryTypeE)
            expList = fieldList['expList']
            query.add_view(expList)
            query.add_constraint(searchFieldE, "=", geneID, code="A")

            # Mining for fish data requires extra constraints and joins
            if self.speciesID == 7955:
                query.add_constraint("expressions.fish.wildtype", "=", "true", code="C")
                query.outerjoin("expressions.subterm")
                query.outerjoin("expressions.startStage")
                query.outerjoin("expressions.endStage")

            if search_type == 'count':
                count_exp = query.count()

        # ---------------------------------
        # Output Results as JSON
        # ---------------------------------
        if search_type == 'count':
            data = {}
            data['count_phenotype'] = count_phenotype
            data['count_expression'] = count_exp
            data['gene_id'] = geneID
            data['species_id'] = self.speciesID

            jdata = json.dumps(data, ensure_ascii=False)
            print jdata

        else:
            fieldSeperator = "\t"

            # Header
            for key in expList:
                print key, fieldSeperator,
            print ""

            for row in query.rows():
                for key in expList:
                    print row[key], fieldSeperator,
                print " "
