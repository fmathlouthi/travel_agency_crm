<?php

namespace App\Controller;

use App\Entity\LoyaltyCardsRequests;
use App\Form\LoyaltyCardsRequestsType;
use App\Repository\LoyaltyCardsRequestsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Repository\PromotionRepository;
use App\Repository\ServiceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Pd\MailerBundle\SwiftMailer\PdSwiftMessage;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use App\Entity\ContactService;
use App\Form\importContactType;
use League\Csv\Reader;
use App\Entity\Contact;
use App\Entity\MailTemplate;
use App\Form\ContactType;
use App\Menu\AccountMenu;
use Pd\UserBundle\Form\ProfileType;
use Symfony\Component\Mime\Address;

use Symfony\Component\Mailer\MailerInterface;


use App\Entity\Account\Profile;
use App\Entity\Account\User;
use App\Form\Account\RolesType;
use App\Manager\SecurityManager;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;


/**
 * @Route("/lcr")
 */
class LoyaltyCardsRequestsController extends AbstractController
{
    /**
     * Security Manager Add Custom Roles.
     */
    public const CUSTOM_ROLES = [
        'ROLE_LCR_ALLREAD',
        'ROLE_LCR_ALLWRITE',

    ];
    /**
     * @IsGranted("ROLE_LCR_LIST")
     * @Route("/", name="loyalty_cards_requests_index", methods={"GET"})
     */
    public function index(LoyaltyCardsRequestsRepository $loyaltyCardsRequestsRepository): Response
    {
        return $this->render('loyalty_cards_requests/index.html.twig', [
            'loyalty_cards_requests' => $loyaltyCardsRequestsRepository->findAll(),
        ]);
    }

    /**
     *  @IsGranted("ROLE_LCR_ADD")
     * @Route("/new", name="loyalty_cards_requests_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $loyaltyCardsRequest = new LoyaltyCardsRequests();
        $form = $this->createForm(LoyaltyCardsRequestsType::class, $loyaltyCardsRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($loyaltyCardsRequest);
            $entityManager->flush();

            return $this->redirectToRoute('loyalty_cards_requests_index');
        }

        return $this->render('loyalty_cards_requests/new.html.twig', [
            'loyalty_cards_request' => $loyaltyCardsRequest,
            'form' => $form->createView(),
        ]);
    }


    public function show(LoyaltyCardsRequests $loyaltyCardsRequest): Response
    {
        return $this->render('loyalty_cards_requests/show.html.twig', [
            'loyalty_cards_request' => $loyaltyCardsRequest,
        ]);
    }

    /**
     *  @IsGranted("ROLE_LCR_EDIT")
     * @Route("/{id}/edit", name="loyalty_cards_requests_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, LoyaltyCardsRequests $loyaltyCardsRequest): Response
    {
        $form = $this->createForm(LoyaltyCardsRequestsType::class, $loyaltyCardsRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('loyalty_cards_requests_index');
        }

        return $this->render('loyalty_cards_requests/edit.html.twig', [
            'loyalty_cards_request' => $loyaltyCardsRequest,
            'form' => $form->createView(),
        ]);
    }

    /**
     *  @IsGranted("ROLE_LCR_DELETE")
     * @Route("/{id}", name="loyalty_cards_requests_delete", methods={"DELETE"})
     */
    public function delete(Request $request, LoyaltyCardsRequests $loyaltyCardsRequest): Response
    {
        if ($this->isCsrfTokenValid('delete'.$loyaltyCardsRequest->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($loyaltyCardsRequest);
            $entityManager->flush();
        }

        return $this->redirectToRoute('loyalty_cards_requests_index');
    }
}
