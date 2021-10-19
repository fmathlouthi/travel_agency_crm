<?php
namespace App\Controller;

use App\Entity\Promotion;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class FadiController extends ApiController
{
    /**
     * @Route("/movies", methods="GET")
     */
    public function index(PromotionRepository $movieRepository)
    {
        $movies = $movieRepository->transformAll();

        return $this->respond($movies);
    }

    /**
     * @Route("/movies", methods="POST")
     */
    public function create(Request $request, PromotionRepository $movieRepository, EntityManagerInterface $em)
    {
        $request = $this->transformJsonBody($request);

        if (! $request) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        // validate the title
        if (! $request->get('title')) {
            return $this->respondValidationError('Please provide a title!');
        }

        // persist the new movie
        $movie = new Movie;
        $movie->setName($request->get('title'));
        $movie->setDescription(0);
        $em->persist($movie);
        $em->flush();

        return $this->respondCreated($movieRepository->transform($movie));
    }

    /**
     * @Route("/movies/{id}/count", methods="POST")
     */
    public function increaseCount($id, EntityManagerInterface $em, PromotionRepository $movieRepository)
    {
        $movie = $movieRepository->find($id);

        if (! $movie) {
            return $this->respondNotFound();
        }

        $movie->setName($movie->getId() + 1);
        $em->persist($movie);
        $em->flush();

        return $this->respond([
            'count' => $movie->getDescription()
        ]);
    }
}
