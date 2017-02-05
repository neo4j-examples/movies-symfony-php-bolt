<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MovieController extends Controller
{
    /**
     * Search for any movie.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $searchTerm = $request->query->get('q');
        $term = '(?i).*'.$searchTerm.'.*';
        $query = 'MATCH (m:Movie) WHERE m.title =~ {term} RETURN m';
        $result = $this->getNeo4jClient()->run($query, ['term' => $term]);

        $movies = [];

        foreach ($result->records() as $record) {
            $movieNode = $record->get('m');

            $movie = $movieNode->values();
            $movie['url'] = $this->generateUrl('movies_show', ['title' => $movieNode->get('title')]);

            $movies[] = $movie;
        }

        return new JsonResponse($movies);
    }

    /**
     * Show title and cast about a movie.
     *
     * @param string $title
     *
     * @return JsonResponse
     */
    public function showAction($title)
    {
        $query = 'MATCH (m:Movie) WHERE m.title = {title} OPTIONAL MATCH p=(m)<-[r]-(a:Person) RETURN m, collect({rel: r, actor: a}) as plays';
        $result = $this->getNeo4jClient()->run($query, ['title' => $title]);

        if (!$movie = $result->firstRecord()->get('m', null)) {
            throw $this->createNotFoundException(sprintf('No movie with title "%s"', $title));
        }

        $output = [
            'title' => $movie->value('title'),
            'cast' => []
        ];

        foreach ($result->firstRecord()->get('plays') as $play) {
            $actor = $play['actor']->value('name');
            $job = explode('_', strtolower($play['rel']->type()))[0];

            $output['cast'][] = [
                'job' => $job,
                'name' => $actor,
                'role' => array_key_exists('roles', $play['rel']->values()) ? $play['rel']->value('roles') : null
            ];
        }

        return new JsonResponse($output);
    }

    /**
     * Display the graph in the background.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function graphAction(Request $request)
    {
        $queryParameters = [
            'limit' => $request->query->getInt('limit', 50)
        ];
        $query = 'MATCH (m:Movie)<-[r:ACTED_IN]-(p:Person) RETURN m,r,p LIMIT {limit}';
        $result = $this->getNeo4jClient()->run($query, $queryParameters);
        $nodes = $edges = $identityMap = [];

        foreach ($result->records() as $record) {
            $movieNode = $record->get('m');
            $personNode = $record->get('p');
            $actedRelationship = $record->get('r');

            $nodes[] = [
                'title' => $movieNode->value('title'),
                'label' => $movieNode->labels()[0]
            ];
            $identityMap[$movieNode->identity()] = count($nodes)-1;

            $nodes[] = [
                'title' => $personNode->value('name'),
                'label' => $personNode->labels()[0]
            ];

            $identityMap[$personNode->identity()] = count($nodes)-1;

            $edges[] = [
                'source' => $identityMap[$actedRelationship->startNodeIdentity()],
                'target' => $identityMap[$actedRelationship->endNodeIdentity()]
            ];
        }

        return new JsonResponse([
            'nodes' => $nodes,
            'links' => $edges
        ]);
    }

    /**
     * @return \GraphAware\Neo4j\Client\Client
     */
    private function getNeo4jClient()
    {
        return $this->get('neo4j.client');
    }
}
