<?php

namespace App\Controller;

use App\Entity\LoyaltyCards;
use App\Entity\Taux;
use App\Form\LoyaltyCardsType;
use App\Repository\LoyaltyCardsRepository;
use App\Service\CardSchemeEncoder;
use App\Service\QRCodeEncoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Entity\LoyaltyCardsRequests;
use App\Form\LoyaltyCardsRequestsType;
use App\Repository\LoyaltyCardsRequestsRepository;


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
 * @Route("/lC")
 */
class LoyaltyCardsController extends AbstractController
{
    /**
     * Security Manager Add Custom Roles.
     */
    public const CUSTOM_ROLES = [
        'ROLE_LC_ALLREAD',
        'ROLE_LC_ALLWRITE',

    ];


    /**
      * @IsGranted("ROLE_LC_REFUSECARD")
     * @Route("/cardrefuse/{id}", name="card_refuse")
     */
    public function RefuseCard(LoyaltyCardsRequests $loyaltyCardRequest) {




        $loyaltyCardRequest->setStatus(2);
        $this->getDoctrine()->getManager()->flush();

        $customer = $loyaltyCardRequest->getCustomerId();
        $member = $this
            ->getDoctrine()
            ->getRepository(Contact::class)
            ->createQueryBuilder('c')
            ->where('c.id = :MemberId' )
            ->setParameter('MemberId', $customer)
            ->getQuery()
            ->getScalarResult();

        # Notification
        $this->addFlash('notice',
            'Enregistrement du refus de carte pour #'.$member[0]['c_id'].' '. $member[0]['c_firstName'] . ' '. $member[0]['c_lastName'] . '');

        return $this->redirectToRoute("admin_dashboard");

    }
    private function sendEmail(array $user, \Swift_Mailer $mailer, $subject,LoyaltyCards $body4, $templateId): bool
    {


        $body1 =
            $this
                ->getDoctrine()
                ->getRepository(MailTemplate::class)
                ->createQueryBuilder('u')
                ->select('u.template')
                ->where('u.templateid like  :score' )

                ->setParameter('score',$templateId)

                ->getQuery()
                ->execute();


        $body =  str_replace('[[name]]',$user['c_firstName'].'  '.$user['c_lastName'],$body1[0]["template"]);
        $body =  str_replace('[[cardcode]]',$body4->getCardCode(),$body);
        $body =  str_replace('[[desc]]',$body4->getLoyaltyPoints(),$body);

        // Create Message
        $message = (new PdSwiftMessage())
            ->setTemplateId($templateId)
            ->setFrom($this->getParameter('pd_user.mail_sender_address'), $this->getParameter('pd_user.mail_sender_name'))
            ->setTo($user['c_email'])
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        return (bool) $mailer->send($message);
    }

    /**
     * @IsGranted("ROLE_LC_VALIDATECARD")
     * @Route("/cardvalidate/{id}", name="card_validate")
     */
    public function ValidateCard(LoyaltyCardsRequests $loyaltyCardRequest,\Swift_Mailer $mailer)
    {


        $loyaltyCardRequest->setStatus(1);
        $this->getDoctrine()->getManager()->flush();

        $customer = $loyaltyCardRequest->getCustomerId();
        $repository = $this->getDoctrine()->getRepository(LoyaltyCards::class);
        $ww = $repository->findOneBy(array('customer_id' => $customer));
        if($ww) {
            return $this->redirectToRoute("admin_dashboard");
        }
        else{
        $member = $this
            ->getDoctrine()
            ->getRepository(Contact::class)
            ->createQueryBuilder('c')
            ->where('c.id = :MemberId')
            ->setParameter('MemberId', $customer)
            ->getQuery()
            ->getScalarResult();

        $loyaltyCard = new LoyaltyCards();
        $loyaltyCard->setCustomerId($member[0]['c_id']);


        $cardEn = new CardSchemeEncoder();
        $cardCode = intval(rand(100000, 9999999) . $member[0]['c_id']);
        $loyaltyCard->setCardCode($cardCode);

        $QRCodeEncoder = new QRCodeEncoder();
        $customerName = $member[0]['c_firstName'] . ' ' . $member[0]['c_lastName'];
        $qrCode = $QRCodeEncoder->encodeQRCode($loyaltyCard->getCardCode(), $customerName);
        $loyaltyCard->setQrcode($qrCode);
        $repository = $this->getDoctrine()->getRepository(Taux::class);
        $lc = $repository->findAll();
        if ($lc) {
            foreach ($lc as $x) {
                $taux = $x->getPointnuber();
            }
        } else {
            $taux = 100;
        }
        $loyaltyCard->setLoyaltyPoints($taux);
        $loyaltyCard->setDateOfIssue(new \DateTime('now'));
        $loyaltyCard->setIsValid(1);
        $loyaltyCard->setStatus('withdraw');
        # Insertion en BDD
        // then persist it
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($loyaltyCard);
        $entityManager->flush();
        $this->sendEmail($member[0], $mailer, 'confirm card request ', $loyaltyCard, 'lcvalide');


//send mail informe


        # Notification
        $this->addFlash('notice',
            'Nouvelle carte validée pour client #' . $member[0]['c_id'] . ' ' . $member[0]['c_firstName'] . ' ' . $member[0]['c_lastName'] . '');

        return $this->redirectToRoute("admin_dashboard");}

    }


    /**
     * @IsGranted("ROLE_LC_LIST")
     * @Route("/", name="loyalty_cards_index", methods={"GET"})
     */
    public function index(LoyaltyCardsRepository $loyaltyCardsRepository,Request $request, PaginatorInterface $paginator): Response
    {

        // Query
        $query = $this
            ->getDoctrine()
            ->getRepository(LoyaltyCards::class)
            ->createQueryBuilder('s')



            ->leftJoin(Contact::class, 'c','WITH' , 's.customer_id = c.id')
            ->Select('c,s');


        if ($request->get('filter')) {
            $query
                ->where('s.status LIKE :filter or s.qrcode  LIKE :filter or c.firstName LIKE :filter or c.lastName LIKE :filter ' )
                ->setParameter('filter', "%{$request->get('filter')}%");
        }
        // Get Result
        $pagination = $paginator->paginate(
            $query->getQuery()->getScalarResult(),
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', $this->getParameter('list_count'))
        );

        // Set Back URL
        $this->get('session')->set('backUrl', $request->getRequestUri());

        return $this->render('loyalty_cards/index.html.twig', [
            'loyalty_cards' => $pagination,
            'filterForm' => $this->createServiceFilterForm()->createView(),

        ]);


    }

    /**
     * Create contact Filter Form.
     */
    private function createServiceFilterForm(): FormInterface
    {
        $form = $this->get('form.factory')
            ->createNamedBuilder(null, FormType::class, null, [

                'method' => 'get',
                'allow_extra_fields' => true,
            ])
            ->add('filter', TextType::class, [
                'label' => 'search_keyword',
                'attr' => ['placeholder' => 'catd staus or qrcode info'],
                'required' => false,
            ])
            ->getForm();

        return $form;
    }


    public function new(Request $request): Response
    {
        $loyaltyCard = new LoyaltyCards();
        $form = $this->createForm(LoyaltyCardsType::class, $loyaltyCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($loyaltyCard);
            $entityManager->flush();

            return $this->redirectToRoute('loyalty_cards_index');
        }

        return $this->render('loyalty_cards/new.html.twig', [
            'loyalty_card' => $loyaltyCard,
            'form' => $form->createView(),
        ]);
    }


    public function show(LoyaltyCards $loyaltyCard): Response
    {
        return $this->render('loyalty_cards/show.html.twig', [
            'loyalty_card' => $loyaltyCard,
        ]);
    }

    /**
     * @IsGranted("ROLE_LC_EDIT")
     * @Route("/{id}/edit", name="loyaltycards_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, LoyaltyCards $loyaltyCard): Response
    {
        $form = $this->createForm(LoyaltyCardsType::class, $loyaltyCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('loyalty_cards_index');
        }

        return $this->render('loyalty_cards/edit.html.twig', [
            'loyalty_card' => $loyaltyCard,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_LCR_DELETE")
     * @Route("/delete/{id}", name="loyalty_cards_delete")
     */
    public function delete(Request $request, LoyaltyCards $loyaltyCard): Response
    {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($loyaltyCard);
            $entityManager->flush();

        $this->addFlash('success', 'remove_complete');
        return $this->redirectToRoute('loyalty_cards_index');
    }
}
