from neo4j import GraphDatabase
import json

driver = GraphDatabase.driver("bolt://spot.imsi.athenarc.gr:7688")

session = driver.session()

query = """
MATCH (d:Disease)-[:MENTIONED_IN_PUBLICATION]->(p:Publication) WHERE p.DOI IN
["10.1016/j.ajoc.2018.02.026", "10.1371/journal.pone.0009879"]
RETURN
    p.DOI as doi,
    d.name as name,
    d.id as id,
    d.description as description
"""
result = session.run(query)

if result:

    for item in result:
        print(item)
        # print(item['doi'] + "\t" + item['name'])

else:
    print("No results found.")
