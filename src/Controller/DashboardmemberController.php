<?php

/**
 * This file is part of the pdAdmin package.
 *
 * @package     pd-admin
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-admin
 */

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Image;
use App\Entity\LoyaltyCards;
use App\Entity\Promotion;
use App\Entity\Reclamation;
use App\Entity\Service;
use App\Entity\User;
use App\Form\ImageType;
use App\Form\PasswordMemberType;
use App\Form\PwMemberType;
use App\Form\ReclamationType;
use App\Form\RegisterType;

use App\Repository\ImageRepository;
use Pd\UserBundle\Form\ChangePasswordMemberType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Repository\LoyaltyCardsRepository;
use App\Service\FrontMemberService;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Factory\QrCodeFactory;
use Knp\Component\Pager\PaginatorInterface;
use Pd\UserBundle\Form\ChangePasswordType;
use Pd\UserBundle\Form\ProfileType;
use Pd\WidgetBundle\Widget\WidgetInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Middleware\ApiAi;


use App\Repository\LoyaltyCardsRequestsRepository;
use App\Entity\LoyaltyCardRequest;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


use App\Entity\LoyaltyCardsRequests;
use App\Form\LoyaltyCardsRequestsType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Admin Dashboard.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class DashboardmemberController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function getMember(){

        $MemberId = $this->getUser()->getId();

        $member = $this
            ->getDoctrine()
            ->getRepository(Contact::class)
            ->createQueryBuilder('c')
            ->select('c')
            ->where('c.userId = :MemberId' )
            ->setParameter('MemberId', $MemberId)
        ->getQuery()
            ->getScalarResult();



            return $member;

    }
    /**
     * Member Dashboard Index.
     *@IsGranted("ROLE_USER")
     * @Route(name="dashboardmember", path="/member")
     *
     */
    public function index(PaginatorInterface $paginator): Response
    {


        $query = $this
            ->getDoctrine()
            ->getRepository(Promotion::class)
            ->createQueryBuilder('p')
            ->WHERE ('  p.startsAt  <=  :now  and  p.endsAt  >= :now ')
            ->setParameter('now',new \DateTime('now'))

        ;



        // Get Result
        $pagination = $paginator->paginate(
            $query->getQuery()

        );
        $id = $this->getUser()->getId();

        $member = $this
            ->getDoctrine()
            ->getRepository(Contact::class)
            ->createQueryBuilder('c')
            ->select('c')
            ->where('c.userId = :MemberId and  MONTH ( c.birhday ) = MONTH( :now ) and DAY ( c.birhday ) = DAY ( :now )  ' )

              ->setParameter('now',new \DateTime('now'))
            ->setParameter('MemberId', $id)
            ->getQuery()
            ->getScalarResult();

        // Render Page

        return $this->render('User/front/index.html.twig', [
            'promo' => $pagination,
            'name' => $this->getUser()->getProfile()->getFullname(),
            'birthday'=>$member,


        ]);
    }
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/member/promotion", name="memberpromotion")
     */
    function galerrypromotion(Request $request)
    {
        $xx =
            $this
            ->getDoctrine()
            ->getRepository(Promotion::class)
            ->createQueryBuilder('p')
                ->WHERE ('  p.startsAt  <=  :now  and  p.endsAt  >= :now ')
                ->setParameter('now',new \DateTime('now'))


            ->getQuery()
                ->getresult()
        ;

        return $this->render('User/promotiongallery.html.twig', [
            'promotions' => $xx,
        ]);
    }
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/member/service", name="memberservice")
     */
    function galerryservice(Request $request)
    {

        $repository = $this->getDoctrine()->getRepository(Service::class);
        $lcs = $repository->findAll();
        return $this->render('User/servicegallery.html.twig', [
            'services' => $lcs,
        ]);
    }
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/member/memberchatbot", name="memberchatbot")
     */
    function indexAction(Request $request)
    {
        return $this->render('User/memberchatbot.html.twig', [
            'title' => 'Eagle Chat Bot'
        ]);
    }
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/member/membermessage", name="membermessage")
     */
    function messageAction(Request $request, \App\Services\BotService $botService)
    {


        // Create a BotMan instance, using the WebDriver
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);
        $botman = BotManFactory::create([]); //No config options required

        //Setup DialogFlow middleware
        $dialogflow = ApiAi::create($this->getParameter('DIALOGFLOW_TOKEN'))->listenForAction();
        $botman->middleware->received($dialogflow);

        // Give the bot some things to listen for.
        $botman->hears('(hello|hi|hey)', function (BotMan $bot) use ($botService) {
            $bot->reply($botService->handleHello().' dear '.$this->getUser()->getProfile()->getFirstname());
        });

        $botman->hears('(what night|when) is club night.*', function (BotMan $bot) use ($botService) {
            $bot->reply($botService->handleClubNights());
        });

        $botman->hears('_THISWEEK_', function (Botman $bot) use ($botService) {
            $bot->reply($botService->handleThisWeeksActivities());
        })->middleware($dialogflow);

        $botman->hears('_ENROLMENT_', function (Botman $bot) use ($botService) {
            //$extras = $bot->getMessage()->getExtras();
            $bot->reply($botService->handleEnrolment());
        })->middleware($dialogflow);

        $botman->hears('_INSURANCE_', function (Botman $bot) use ($botService) {
            $bot->reply($botService->handleInsurance());
        })->middleware($dialogflow);

        $botman->hears('_MEMBERSHIP_', function (Botman $bot) use ($botService) {
            $bot->reply($botService->handleMembership());
        })->middleware($dialogflow);
// Give the bot something to listen for.
        $botman->hears('hello', function (BotMan $bot) {
            $bot->reply('Hello yourself.');
        });
        $botman->hears('hello1', function (BotMan $bot) {
            $bot->reply('Hello yourself.');
        });
        $botman->hears('call me {name}', function ($bot, $name) {
            $bot->reply('Your name is: '.$name);
        });
        $botman->hears('call me {name} the {adjective}', function ($bot, $name, $adjective) {
            $bot->reply('Hello '.$name.'. You truly are '.$adjective);
        });
        $botman->hears('I want ([0-9]+)', function ($bot, $number) {
            $bot->reply('You will get: '.$number);
        });
        $botman->hears('I want ([0-9]+) portions of (Cheese|Cake)', function ($bot, $amount, $dish) {
            $bot->reply('You will get '.$amount.' portions of '.$dish.' served shortly.');
        });
        $botman->hears('.*Bonjour.*', function ($bot) {
            $bot->reply('Nice to meet you!');
        });
        $botman->fallback(function($bot) {
            $bot->reply('Sorry, I did not understand these commands. Here is a list of commands I understand: ... ');
        });
        // Start listening
        $botman->listen();

        //Send an empty response (Botman has already sent the output itself - https://github.com/botman/botman/issues/342)
        return new Response();
    }

    /**
     * Default route to customer account
     * @IsGranted("ROLE_USER")
     * @Route("/member/accountmember/", name="accountmember", methods={"GET", "POST"})
     * @param Request $request
     * @param QrCodeFactory $qrCodeFactory

     * @param FrontMemberService $FrontMemberService
     * @return Response
     */
    public function accountIndex(Request $request,  QrCodeFactory $qrCodeFactory,LoyaltyCardsRepository $loyaltyCardsRepository,  FrontMemberService $FrontMemberService ,LoyaltyCardsRequestsRepository $loyaltyCardsRequestsRepository,ImageRepository $imager): Response {

        // If the customer is not logged, redirect him to the login page


        $customer = $this->getMember();



        // call the service to get all that we need
        $generalInfo = $FrontMemberService->getInfoMember($customer, $qrCodeFactory);
        $cardRequest = $this->entityManager->getRepository(LoyaltyCardsRequests::class)
            ->createQueryBuilder('lcr')

            ->WHERE ('lcr.customer_id = :bb and lcr.status = 0')
            ->setParameter('bb',$customer[0]['c_id'] )
            ->getQuery()
            ->getScalarResult();
        $xx=  $imager
            ->findOneBy(array('contact_id' => $customer[0]['c_id']));
        if(empty($xx))
        {
            $cc='images/profil_default_image.png';
        }
        else
        {
            $cc='images/'.$xx->getImagepath();
        }
        return $this->render('User/front/account/account_index.html.twig', [

            'customer' => $customer[0],
            'customer_image' => $cc,
            'loyalty_cards'=> $generalInfo['loyalty_cards'],
            'card_request' => $generalInfo['card_request'],
            'qr_code' => $generalInfo['qr_code'],
            'member_menu' => "scores"
        ]);

    }


    /**
     * @IsGranted("ROLE_USER")
     * Requests for loyalty cards, sent by customers
     * @Route("/member/membercardrequest/", name="card_request", methods={"GET", "POST"})
     * @param Request $request
     * @param Contact $customer

     * @return Response
     */
    public function cardRequest(Request $request): Response {



        $customer = $this->getMember();

       // $client = $this->entityManager->getRepository(Contact::class)
          //  ->createQueryBuilder('c')
          //  ->select('c.id')
          //  ->WHERE ('c.userId= :xx')
          //  ->setParameter('xx',$customer->getId() )
           // ->getQuery()
          //  ->getSingleScalarResult();
        $cardcount = $this->entityManager->getRepository(LoyaltyCards::class)
            ->createQueryBuilder('u')
            ->select('count(u.id)')
            ->WHERE ('u.customer_id = :bb')
            ->setParameter('bb',$customer[0]['c_id'] )
            ->getQuery()
            ->getSingleScalarResult();

        // compare, check if the customer in session is the right person, and if he doesn't already have a card
        if ($cardcount > 0) {
            return $this->redirectToRoute('dashboardmember');
        }

        $loyaltyCardRequest = New  LoyaltyCardsRequests();
        $loyaltyCardRequest-> setDateOfRequest(new \DateTime('now'));
        $loyaltyCardRequest-> setStatus(0);
        $loyaltyCardRequest->setCustomerId($customer[0]['c_id']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($loyaltyCardRequest);
        $entityManager->flush();

        // redirecting to the account page
        return $this->redirectToRoute("accountmember");
    }


    /**
     * @IsGranted("ROLE_USER")
     * @Route("/member/account/editprofile/", name="member_edit", methods={"GET", "POST"})
     * @param Request $request

     * @param Packages $packages
     * @param QrCodeFactory $qrCodeFactory
     * @param FrontMemberService $FrontMemberService

     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function memberEdit(Request $request, Packages $packages, QrCodeFactory $qrCodeFactory, FrontMemberService $FrontMemberService, UserPasswordEncoderInterface $passwordEncoder,ParameterBagInterface $bag,ImageRepository $imager){


        // If the customer is not logged, redirect him to the login page


        $customer = $this->getMember();

        if(empty($customer)){
            return $this->redirectToRoute("dashboardmember");
        }



        $form = $this->createForm(ProfileType::class,$this->getUser(), [
            'parameter_bag' => $bag,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($this->getUser());
            $entityManager->flush();
            $contact = $this
                ->getDoctrine()
                ->getRepository(Contact::class)
                ->findOneBy(array('userId' => $this->getUser()->getId()));

            $contact-> setModifiedAt(new \DateTime('now'));

            $contact->setFirstName($this->getUser()->getProfile()->getFirstName());
            $contact->setLastName($this->getUser()->getProfile()->getLastName());
            $contact->setEmail($this->getUser()->getEmail());
            $contact->setPhone($this->getUser()->getProfile()->getPhone());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('notice',
                'Félicitation, votre profil a été mis à jour !');

            return $this->redirectToRoute("member_edit");

        }

        $generalInfo = $FrontMemberService->getInfoMember($customer, $qrCodeFactory);
        $xx=  $imager
            ->findOneBy(array('contact_id' => $customer[0]['c_id']));
        if(empty($xx))
        {
            $cc='images/profil_default_image.png';
        }
        else
        {
            $cc='images/'.$xx->getImagepath();
        }
        return $this->render('User/front/account/account_edit.html.twig', [

            'customer' => $customer[0],

            'customer_image' => $cc,
            'loyalty_cards' => $generalInfo['loyalty_cards'],
            'card_request' => $generalInfo['card_request'],
            'qr_code' => $generalInfo['qr_code'],
            'member_menu' => "profile",
            'form' => $form->createView()
        ]);
    }



    /**
     * @IsGranted("ROLE_USER")
     * @Route("/member/account/editprofilepw/", name="member_editpw", methods={"GET", "POST"})
     * @param Request $request

     * @param Packages $packages
     * @param QrCodeFactory $qrCodeFactory
     * @param FrontMemberService $FrontMemberService

     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function memberEditPW(Request $request, Packages $packages, QrCodeFactory $qrCodeFactory, FrontMemberService $FrontMemberService, UserPasswordEncoderInterface $passwordEncoder,ImageRepository $imager){

        $img_profile_name = 'uploads/images/pic09.jpg';

        // If the customer is not logged, redirect him to the login page


        $customer = $this->getMember();

        if(empty($customer)){
            return $this->redirectToRoute("dashboardmember");
        }

        $form = $this->createForm(ChangePasswordType::class, $this->getUser(), [
            'disable_current_password' => $this->isGranted(\App\Entity\Account\User::ROLE_DEFAULT) ||
                $this->isGranted('ADMIN_ACCOUNT_ALLWRITE'),
        ]);

        $form->handleRequest($request);

        // Form Submit & Valid
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($this->getUser(), $request->get('plainPassword')->getData());
            $this->getUser()->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($this->getUser());
            $entityManager->flush();

            $this->addFlash('notice',
                'Félicitation, votre profil a été mis à jour !');

            return $this->redirectToRoute("member_editpw");

        }

        $generalInfo = $FrontMemberService->getInfoMember($customer, $qrCodeFactory);
        $xx=  $imager
            ->findOneBy(array('contact_id' => $customer[0]['c_id']));
        if(empty($xx))
        {
             $cc='images/profil_default_image.png';
        }
        else
        {
            $cc='images/'.$xx->getImagepath();
        }
        return $this->render('User/front/account/account_editpw.html.twig', [

            'customer' => $customer[0],
            'customer_image' => $cc,
            'loyalty_cards' => $generalInfo['loyalty_cards'],
            'card_request' => $generalInfo['card_request'],
            'qr_code' => $generalInfo['qr_code'],
            'member_menu' => "profilepw",
            'form' => $form->createView(),
            
        ]);
    }




    /**
     * @IsGranted("ROLE_USER")
     * @Route("/member/account/editprofileimg/", name="member_editimg", methods={"GET", "POST"})
     * @param Request $request
 * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function memberEditimge(Request $request,Packages $packages, QrCodeFactory $qrCodeFactory, FrontMemberService $FrontMemberService,ImageRepository $imager){



        // If the customer is not logged, redirect him to the login page


        $customer = $this->getMember();



        if(empty($customer)){
            return $this->redirectToRoute("dashboardmember");
        }

        $image = $this
            ->getDoctrine()
            ->getRepository(Image::class)
            ->createQueryBuilder('cs')



            ->leftJoin(Contact::class, 'c','WITH' , 'cs.contact_id = c.id')
            ->Select('cs.imagepath')
            ->where('cs.contact_id LIKE :filter ' )
            ->setParameter('filter', $customer[0]['c_id'])
            ->getQuery()
            ->getScalarResult();



        $form = $this->createForm(ImageType::class);

        $form->handleRequest($request);

        // Form Submit & Valid
        if ($form->isSubmitted() && $form->isValid()) {

            $x=$form->get('imagepath')->getData();
            $originalFilename = pathinfo($x->getClientOriginalName(), PATHINFO_FILENAME).rand(14752100,85547741210);
            $extension = $x->guessExtension();
            $x->move($this->getParameter('upload_dir_image_pro'),$originalFilename.'.'.$extension );

            if(empty($image)){
                $imgpro = new Image();
                $imgpro->setImagepath($originalFilename.'.'.$extension);
                $imgpro->setContactId($customer[0]['c_id']);
            }
            else{
                $imgpro= $imager
                ->findOneBy(array('contact_id' => $customer[0]['c_id']));
                    $imgpro->setImagepath($originalFilename.'.'.$extension);

            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($imgpro);
            $entityManager->flush();

            $this->addFlash('notice',
                'Félicitation, votre profil a été mis à jour !');

            return $this->redirectToRoute("member_editimg");

        }

      $xx=  $imager
            ->findOneBy(array('contact_id' => $customer[0]['c_id']));
if(empty($xx))
{
    $cc='images/profil_default_image.png';
}
else
{
    $cc='images/'.$xx->getImagepath();
}
        $generalInfo = $FrontMemberService->getInfoMember($customer, $qrCodeFactory);
        return $this->render('User/front/account/account_editimg.html.twig', [

            'customer' => $customer[0],
            'customer_image' => $cc,
            'form' => $form->createView(),
            'member_menu' => 'profileimg',


            'loyalty_cards' => $generalInfo['loyalty_cards'],
            'card_request' => $generalInfo['card_request'],
            'qr_code' => $generalInfo['qr_code'],


        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * Scores route to customer account
     * @Route("/member/accountmember/{part}", name="account_member")
     * @param $part
     * @param Request $request
     * @param QrCodeFactory $qrCodeFactory
     * @param FrontMemberService $FrontMemberService
     * @return Response
     */
    public function accountScores($part, Request $request,  QrCodeFactory $qrCodeFactory,  FrontMemberService $FrontMemberService,ImageRepository $imager): Response {

        // $customerId = $this->getUser()->getId();


        $customer = $this->getMember();

        $xx=  $imager
            ->findOneBy(array('contact_id' => $customer[0]['c_id']));
if(empty($xx))
{
     $cc='images/profil_default_image.png';
}
else
{
    $cc='images/'.$xx->getImagepath();
}
        $generalInfo = $FrontMemberService->getInfoMember($customer, $qrCodeFactory);

        return $this->render('User/front/account/account_index.html.twig', [

            'customer' => $customer[0],
            'customer_image' => $cc,
            'loyalty_cards' => $generalInfo['loyalty_cards'],
            'card_request' => $generalInfo['card_request'],
            'qr_code' => $generalInfo['qr_code'],
            'member_menu' => $part
        ]);

    }


    /** Need customer.id, to know if the card request was sent,
     * Then need to know the status of the loyalty card (to display the correct information)
     * We will switch the views, or rather content in the wiews depending on the situation
     */
    public function cardDisplay() {
        return $this->render('User/front/components/_card_display.html.twig'/*, [
                'dataNeeded' => $dataNeeded
            ]*/);
    }



    /**
     * @IsGranted("ROLE_USER")
     * @Route("/member/account/recla/", name="member_recla", methods={"GET", "POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function memberrecla(Request $request){



        // If the customer is not logged, redirect him to the login page


        $customer = $this->getMember();



        if(empty($customer)){
            return $this->redirectToRoute("dashboardmember");
        }
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation-> setReclamationDate(new \DateTime('now'));
            $reclamation-> setContactId($customer[0]['c_id']);
            $reclamation-> setStatus(0);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'Félicitation, votre reclamation a été bien envoi !');
             return $this->redirectToRoute('dashboardmember');
        }
        return $this->render('User/front/account/recla.html.twig', [


            'form' => $form->createView(),



        ]);
    }
}
