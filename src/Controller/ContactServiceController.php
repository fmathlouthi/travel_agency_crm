<?php

namespace App\Controller;

use App\Entity\ContactService;
use App\Entity\LoyaltyCards;
use App\Entity\Service;
use App\Entity\Taux;
use App\Form\ContactServiceType;
use App\Repository\ContactServiceRepository;
use App\Repository\ServiceRepository;
use Doctrine\DBAL\Types\IntegerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use League\Csv\Reader;
use App\Entity\Contact;
use App\Entity\MailTemplate;
use App\Form\ContactType;
use App\Menu\AccountMenu;
use Pd\UserBundle\Form\ProfileType;
use Symfony\Component\Mime\Address;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;



use Pd\MailerBundle\SwiftMailer\PdSwiftMessage;
use Pd\UserBundle\Event\UserEvent;
use Pd\UserBundle\Form\ResettingPasswordType;
use Pd\UserBundle\Model\GroupInterface;
use Pd\UserBundle\Model\ProfileInterface;
use Pd\UserBundle\Model\UserInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use League\Csv\Writer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\Account\Profile;
use App\Entity\Account\User;
use App\Form\Account\RolesType;
use App\Manager\SecurityManager;

use Knp\Component\Pager\PaginatorInterface;
use Pd\UserBundle\Form\ChangePasswordType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;






/**
 * @Route("/contactservice")
 */
class ContactServiceController extends AbstractController
{

    public const CUSTOM_ROLES = [
        'ROLE_CONTACTSERVICE_ALLREAD',
        'ROLE_CONTACTSERVICE_ALLWRITE',
        'ROLE_CONTACTSERVICE_CREATEPROMO',
    ];
    /**
     * @IsGranted("ROLE_CONTACTSERVICE_LIST")
     * @Route("/", name="contact_service_index", methods={"GET"})
     */
    public function index(ContactServiceRepository $contactServiceRepository , Request $request,PaginatorInterface $paginator,\Swift_Mailer $mailer): Response
    {



        $query = $this
            ->getDoctrine()
            ->getRepository(ContactService::class)
            ->createQueryBuilder('cs')

            ->leftJoin(Service::class, 's' ,'WITH' , 'cs.service_id = s.id')

            ->leftJoin(Contact::class, 'c','WITH' , 'cs.contact_id = c.id')
            ->Select('c,cs,s');

        if ($request->get('filter')) {
            $query
                ->where('(c.email LIKE :filter) or (c.firstName LIKE :filter) or (c.lastName LIKE :filter) or (s.service_name LIKE :filter)' )
                ->setParameter('filter', "%{$request->get('filter')}%");
        }
        if ($request->get('score')) {
            $query
                ->andWhere('cs.sccore >= :score')
                ->setParameter('score',(int)$request->get('score'));

        }

        if ($request->get('template_id') ) {

            $campainlist =
                $this
                    ->getDoctrine()
                    ->getRepository(Contact::class)
                    ->createQueryBuilder('c')

                    ->leftJoin(ContactService::class, 'cs', 'WITH', 'cs.contact_id = c.id')
                    ->Select('c,cs')



                    ->WHERE('  cs.sccore >= :scoreC and (SUBSTRING(:now,1,4) - SUBSTRING(c.birhday ,1,4) ) >= :agefrom and (SUBSTRING(:now,1,4) - SUBSTRING(c.birhday ,1,4) ) <= :ageto')

                    ->setParameter('agefrom', (int)$request->get('agefrom'))
                    ->setParameter('ageto', (int)$request->get('ageto'))
                    ->setParameter('now',new \DateTime('now'))


                    ->setParameter('scoreC', (int)$request->get('scoreC'))





                    ->getQuery()
                    ->getScalarResult();

// Send campain promo
            if ($campainlist) {


                foreach ($campainlist as $user) {

                    $this->campainpromo($user, $mailer, 'Promotion for you', (int)$request->get('template_id'),(int)$request->get('service_id'));
                }
            }
        }
        // Get Result
        $pagination = $paginator->paginate(
            $query->getQuery()->getScalarResult(),
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', $this->getParameter('list_count'))
        );

        // Set Back URL
        $this->get('session')->set('backUrl', $request->getRequestUri());

        // Render Page
        return $this->render('contact_service/index.html.twig', [
            'contact_services' => $pagination,
            'promoForm' => $this->createPromoForm()->createView(),
            'filterForm' => $this->createContactFilterForm()->createView(),
        ]);
    }




    private function campainpromo( $user, \Swift_Mailer $mailer, $subject, $templateId,$serviceid): bool
    {

        $body1 =
            $this
                ->getDoctrine()
                ->getRepository(MailTemplate::class)
                ->createQueryBuilder('u')
                ->select('u.template')
                ->where('u.id =  :tid ' )

                ->setParameter('tid',(int)$templateId)

                ->getQuery()
                ->execute();


        $body =  str_replace('[[fullname]]',$user["c_firstName"].' '.$user["c_lastName"],$body1[0]["template"]);
        $repository = $this->getDoctrine()->getRepository(Service::class);
        $ww = $repository->findOneBy(array('id' => $serviceid));
        if($ww)
        {
            $body =  str_replace('[[dis]]',$ww->getDiscreption(),$body);
            $body =  str_replace('[[link]]',$ww->getLinkservice(),$body);
            $body =  str_replace('{CompanyName}',$this->getParameter('CompanyName'),$body);


        }


        // Create Message
        $message = (new PdSwiftMessage())
            ->setTemplateId($templateId)
            ->setFrom($this->getParameter('pd_user.mail_sender_address'), $this->getParameter('pd_user.mail_sender_name'))
            ->setTo($user["c_email"])
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        return (bool) $mailer->send($message);
    }
    private function createPromoForm(): FormInterface
    {


        // $formOptions = array('services' => $t);

        $query1 = $this
        ->getDoctrine()
        ->getRepository(Service::class)
        ->createQueryBuilder('s')

        ;
        $t1[]=array();
        $x1= $query1->getQuery()->getScalarResult();
        foreach ($x1 as $w1)
        {
         $t1[$w1["s_service_name"]]=$w1["s_id"];
        }

        if (array_key_exists(0,$t1))
        {
          unset($t1[0]);
        }

        $query2 = $this
            ->getDoctrine()
            ->getRepository(MailTemplate::class)
            ->createQueryBuilder('mt')
            ->where('mt.templateid LIKE :service' )
            ->setParameter('service','%service%')

        ;
        $t2[]=array();
        $x2= $query2->getQuery()->getScalarResult();
        foreach ($x2 as $w2)
        {

            $t2[$w2["mt_templateid"]]=$w2["mt_id"];
        }

        if (array_key_exists(0,$t2))
        {
            unset($t2[0]);
        }


        // $formOptions = array('services' => $t1);
         $formOptions =array('services' => $t1);
        $formOptions1 =array('template' => $t2);
        $form = $this->get('form.factory')
            ->createNamedBuilder(null, FormType::class, null, [
                'csrf_protection' => false,
                'method' => 'get',
                'allow_extra_fields' => true,
            ])

            ->add('template_id', ChoiceType::class, [

                'label' => 'template ',
                'help' => 'please select the template',
                'choices'  =>  $formOptions1 ['template'],
                'multiple' => false,

                'required' => false,
                'placeholder' => false,
            ])

              ->add('service_id', ChoiceType::class, [

                'label' => 'service ',
              'help' => 'please select the service',

            'choices'  =>  $formOptions ['services'],
        'multiple' => false,

            'required' => false,
            'placeholder' => false,
            ])

            ->add('agefrom', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'Age From',
                'empty_data' => '1',
                'required' => false,

                'help' => 'CHOISE THE START age',

                'attr' => ['placeholder-nt' => '1'],

            ])
            ->add('ageto', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'Age To',
                'empty_data' => '100',


                'help' => 'CHOISE THE end age',

                'attr' => ['placeholder-nt' => '100'],
                'required' => false,
            ])
            ->add('scoreC', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'Score',

                'empty_data' => '0',


                'help' => 'put the score',

                'attr' => ['placeholder-nt' => '00'],
                'required' => false,
            ])

            ->getForm();

        return $form;
    }


    private function createContactFilterForm(): FormInterface
    {
        $form = $this->get('form.factory')
            ->createNamedBuilder(null, FormType::class, null, [

                'method' => 'get',
                'allow_extra_fields' => true,
            ])
            ->add('filter', TextType::class, [
                'label' => 'search_keyword',
                'attr' => ['placeholder' => 'name/email/Service'],
                'required' => false,
            ])
            ->add('score', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'Score',
                'attr' => ['placeholder' => 'put the score'],
                'required' => false,
            ])
            ->getForm();

        return $form;
    }

    /**
     * @IsGranted("ROLE_CONTACTSERVICE_ADD")
     * @Route("/new", name="contact_service_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        $contactService = new ContactService();
        $query = $this
            ->getDoctrine()
            ->getRepository(contact::class)
            ->createQueryBuilder('c')

        ;
        $t[]=array();
        $x= $query->getQuery()->getScalarResult();
        foreach ($x as $w)
        {

            $t[$w["c_email"]]=$w["c_id"];
        }


        // $formOptions = array('categories' => $t);

        $query1 = $this
            ->getDoctrine()
            ->getRepository(Service::class)
            ->createQueryBuilder('s')

        ;
        $t1[]=array();
        $x1= $query1->getQuery()->getScalarResult();
        foreach ($x1 as $w1)
        {
            $t1[$w1["s_service_name"]]=$w1["s_id"];
        }

        if (array_key_exists(0,$t))
        {
            unset($t1[0]);
        }


        // $formOptions = array('services' => $t1);
        $formOptions =array_merge(array('services' => $t1), array('categories' => $t));

        $form = $this->createForm(ContactServiceType::class, $contactService,$formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($contactService);
            $entityManager->flush();

            return $this->redirectToRoute('admin_contact_service_index');
        }

        return $this->render('contact_service/new.html.twig', [
            'contact_service' => $contactService,
            'form' => $form->createView(),
        ]);
    }


    public function show(ContactService $contactService): Response
    {
        return $this->render('contact_service/show.html.twig', [
            'contact_service' => $contactService,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTACTSERVICE_EDIT")
     * @Route("/{id}/edit", name="contact_service_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ContactService $contactService): Response
    {
        $form = $this->createForm(ContactServiceType::class, $contactService);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_contact_service_index');
        }

        return $this->render('contact_service/edit.html.twig', [
            'contact_service' => $contactService,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTACTSERVICE_DELETE")
     * @Route("/delete/{id}", name="contact_service_delete")
     */
    public function delete(Request $request, ContactService $contactService): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($contactService);
        $entityManager->flush();

        // Flash Message
        $this->addFlash('success', 'remove_complete');

        return $this->redirectToRoute('admin_contact_service_index');
    }
    /**
     *  contact edit score .
     *
     *@IsGranted("ROLE_CONTACTSERVICE_EDITSCORE")
     * @Route(name="contact_service_edit_score", path="/contactservice/editscore/{id}")
     */

    public function edit_score(Request $request , ContactService $contactService): Response
    {
        $contactService-> setSccore($contactService->getSccore()+1);


        $em = $this->getDoctrine()->getManager();
        $em->persist($contactService);
        $em->flush();
        $repository = $this->getDoctrine()->getRepository(LoyaltyCards::class);
        $lc = $repository->findOneBy(['customer_id' => $contactService->getContactId()]);
        if($lc)
        {
            $repository = $this->getDoctrine()->getRepository(Taux::class);
            $ww = $repository->findAll();
            if($ww)
            {
                foreach ($ww as $x)
                {
                    $taux=$x->getPointnuber();
                }
            }
            else
            {
                $taux=100;
            }
            $lc->setLoyaltyPoints($lc->getLoyaltyPoints()+$taux);
            $em = $this->getDoctrine()->getManager();
            $em->persist($lc);
            $em->flush();
        }
        return $this->redirectToRoute('admin_contact_service_index');
    }

}
