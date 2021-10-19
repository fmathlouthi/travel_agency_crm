<?php

namespace App\Controller;

use App\Entity\Account\Profile;
use App\Entity\Account\User;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Form\ReclaType;
use App\Repository\ReclamationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Pd\MailerBundle\SwiftMailer\PdSwiftMessage;
use Pd\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Contact;
use App\Entity\ContactService;
use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;

use League\Csv\Reader;
use App\Entity\MailTemplate;
use App\Form\ContactType;
use App\Menu\AccountMenu;
use Pd\UserBundle\Form\ProfileType;
use Symfony\Component\Mime\Address;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/reclamation")
 */
class ReclamationController extends AbstractController
{
    /**
     * Security Manager Add Custom Roles.
     */
    public const CUSTOM_ROLES = [
        'ROLE_RECLAMATION_ALLREAD',
        'ROLE_RECLAMATION_ALLWRITE',

    ];
    /**
     * @IsGranted("ROLE_RECLAMATION_LIST")
     * @Route("/", name="reclamation_index", methods={"GET"})
     */
    public function index(ReclamationRepository $reclamationRepository,Request $request, PaginatorInterface $paginator): Response
    {
        // Query
        $query = $this
            ->getDoctrine()
            ->getRepository(Reclamation::class)
            ->createQueryBuilder('s')



            ->leftJoin(Contact::class, 'c','WITH' , 's.contact_id = c.id')
            ->Select('c,s')
            ->where(' s.status = 0 ' );

        if ($request->get('filter')) {
            $query
                ->andwhere(' c.firstName LIKE :filter or c.lastName LIKE :filter ' )
                ->setParameter('filter', "%{$request->get('filter')}%");}
        // Get Result
        $pagination = $paginator->paginate(
            $query->getQuery()->getScalarResult(),
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', $this->getParameter('list_count'))
        );

        // Set Back URL
        $this->get('session')->set('backUrl', $request->getRequestUri());

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $pagination,
            'filterForm' => $this->createServiceFilterForm()->createView(),

        ]);

    }
    /**
     * @IsGranted("ROLE_RECLAMATIONLOG_LIST")
     * @Route("/log", name="reclamation_log_index", methods={"GET"})
     */
    public function indexlog(ReclamationRepository $reclamationRepository,Request $request, PaginatorInterface $paginator): Response
    {
        // Query
        $query = $this
            ->getDoctrine()
            ->getRepository(Reclamation::class)
            ->createQueryBuilder('s')



            ->leftJoin(Contact::class, 'c','WITH' , 's.contact_id = c.id')
            ->leftJoin(User::class, 'u','WITH' , 's.user_id = u.id')
            ->leftJoin(Profile::class, 'p','WITH' , 'u.profile = p.id')
            ->Select('c,s,p,u')

            ->where(' s.status = 1 ' );

        if ($request->get('filter')) {
            $query
                ->andwhere(' c.firstName LIKE :filter or c.lastName LIKE :filter ' )
                ->setParameter('filter', "%{$request->get('filter')}%");}
        // Get Result
        $pagination = $paginator->paginate(
            $query->getQuery()->getScalarResult(),
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', $this->getParameter('list_count'))
        );

        // Set Back URL
        $this->get('session')->set('backUrl', $request->getRequestUri());

        return $this->render('reclamation/indexlog.html.twig', [
            'reclamations' => $pagination,
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
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation-> setReclamationDate(new \DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('reclamation_index');
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    /**
     *  @IsGranted("ROLE_RECLAMATIONLOG_SHOW")
     * @Route("/{id}", name="reclamationlog_show", methods={"GET"})
     */
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }



    public function edit(Request $request, Reclamation $reclamation): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reclamation_index');
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    private function sendEmail(Reclamation $user, \Swift_Mailer $mailer, $subject, $body4, $templateId): bool
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

        $repository = $this->getDoctrine()->getRepository(Contact::class);
        $product = $repository->findOneBy(['id' => $user->getContactId()]);
        $body =  str_replace('[[name]]',$product->getFirstName().' '.$product->getLastName(),$body1[0]["template"]);
        $body =  str_replace('[[reclamation]]',$body4['confirmationUrl'],$body);


        // Create Message
        $message = (new PdSwiftMessage())
            ->setTemplateId($templateId)
            ->setFrom($this->getParameter('pd_user.mail_sender_address'), $this->getParameter('pd_user.mail_sender_name'))
            ->setTo($product->getEmail())
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        return (bool) $mailer->send($message);
    }

    /**
     * @IsGranted("ROLE_RECLAMATION_ANSWER")
     * @Route("/{id}/send", name="reclamation_send", methods={"GET","POST"})
     */
    public function answer(Request $request, Reclamation $reclamation,\Swift_Mailer $mailer): Response
    {


        $form = $this->createForm(ReclaType::class );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Send Confirmation Email
            $emailBody = [
                'confirmationUrl' => $form->get('Answer')->getData(),
            ];
            $this->sendEmail($reclamation, $mailer, 'Reclamation answer', $emailBody, 'reclamation');
            $reclamation-> setAnswer($form->get('Answer')->getData());
            $reclamation-> setStatus(1);
            $reclamation-> setUserId($this->getUser()->getId());
            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($reclamation);
            $em->flush();
            return $this->redirectToRoute('reclamation_index');
        }

        return $this->render('reclamation/answer.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
            'recla'=>$reclamation->getDescription(),
        ]);
    }

 /**
     *  @IsGranted("ROLE_RECLAMATION_DELETE")
     * @Route("/delete/{id}", name="reclamation_delete")
     */
    public function delete(Request $request, Reclamation $reclamation): Response
    {
    
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reclamation);
            $entityManager->flush();
       // Flash Message
        $this->addFlash('success', 'remove_complete');

        return $this->redirectToRoute('reclamation_index');
    }
}
