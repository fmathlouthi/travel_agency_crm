<?php

namespace App\Controller;
use App\Entity\ContactService;
use App\Entity\LoyaltyCardsRequests;
use App\Entity\Reclamation;
use App\Form\importContactType;
use App\Repository\ImageRepository;
use App\Repository\LoyaltyCardsRepository;
use App\Repository\LoyaltyCardsRequestsRepository;
use App\Repository\UserRepository;
use League\Csv\Reader;
use App\Entity\Contact;
use App\Entity\MailTemplate;
use App\Form\ContactType;
use App\Menu\AccountMenu;
use Pd\UserBundle\Form\ProfileType;
use Symfony\Component\Mime\Address;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use \Datetime;

use Pd\MailerBundle\SwiftMailer\PdSwiftMessage;
use Pd\UserBundle\Event\UserEvent;
use Pd\UserBundle\Form\ResettingPasswordType;
use Pd\UserBundle\Model\GroupInterface;
use Pd\UserBundle\Model\ProfileInterface;
use Pd\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use League\Csv\Writer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Routing\Annotation\Route;

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



use App\Entity\LoyaltyCards;
use App\Form\LoyaltyCardsType;


/**
 * @Route("/contact")
 */
class ContactController extends AbstractController
{
    /**
     * Security Manager Add Custom Roles.
     */
    public const CUSTOM_ROLES = [
        'ROLE_CONTACT_ALLREAD',
        'ROLE_CONTACT_ALLWRITE',

    ];
    private function sendEmail(UserInterface $user, \Swift_Mailer $mailer, $subject, $body4, $templateId): bool
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


        $body =  str_replace('[[name]]',$user->getProfile()->getFullName(),$body1[0]["template"]);
        $body =  str_replace('[[confirmurl]]',$body4['confirmationUrl'],$body);
        $body =  str_replace('[[username]]',$user->getUsername(),$body);

        // Create Message
        $message = (new PdSwiftMessage())
            ->setTemplateId($templateId)
            ->setFrom($this->getParameter('pd_user.mail_sender_address'), $this->getParameter('pd_user.mail_sender_name'))
            ->setTo($user->getEmail())
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        return (bool) $mailer->send($message);
    }

    /**

     *@IsGranted("ROLE_CONTACT_IMPORT")
     * @Route("/import", name="contact_import", methods={"GET","POST"})
     */
    public function importcontact(Request $request, EventDispatcherInterface $dispatcher, \Swift_Mailer $mailer): Response
    {


        $form = $this->createForm(importContactType::class);

        // Handle Request
        $form->handleRequest($request);

        // Form Check
        if ($form->isSubmitted() && $form->isValid()) {
            // Check Super Admin & Check All Write


//move($this->getParameter('upload_dir'),'contact-31-ines.csv');
                          $x=$form->get('file_import')->getData();
            $originalFilename = pathinfo($x->getClientOriginalName(), PATHINFO_FILENAME);
            $x->move($this->getParameter('upload_dir'),$originalFilename.'.csv');



                $csv = Reader::createFromPath($this->getParameter('upload_dir').$originalFilename.'.csv')
                    ->setHeaderOffset(0);
            $csv->setDelimiter(';');
                foreach ($csv as $record) {

                    if(!empty($record)){

                        $xx=
                            $this
                                ->getDoctrine()
                                ->getRepository(Contact::class)
                                ->createQueryBuilder('u')
                                ->select('u')
                                ->WHERE (' u.email = :xx  ')
                                ->setParameter('xx',$record["email"])
                                ->getQuery()
                                ->getScalarResult();
if(!empty($xx)){

    $this->addFlash('danger', 'user exist '.$record["email"]);
}
else{

                        $contact = new Contact();
                        $userp= new Profile();
                        $user= new User();
                        $userp-> setFirstname($record["first_name"]);
                        $userp-> setLastname($record["last_name"]);
                        $userp-> setCompany('tuninfo');

                        $userp-> setLanguage('fr');

                        $userp-> setPhone($record["phone"]);
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($userp);
                        $entityManager->flush();
                        $user->setEmail($record["email"]);

                        $user->setEnabled(false);
                        $user->setFreeze(false);
                        $user->setConfirmationToken($user->createConfirmationToken());
                        $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$OGk2ODZOcHVITGI1eFFRTg$6t9JGukcHfpCHXIdcUhsSphkDqGSeI4rbJ2LZp1O5q4');
                        $user->setCreatedAt(new \DateTime('now'));

                        $entityManager = $this->getDoctrine()->getManager();

                        $user->setProfile($userp);
                        $entityManager->persist($user);
                        $entityManager->flush();

                        $contact-> setModifiedAt(new \DateTime('now'));
                        $contact-> setCreatedAt(new \DateTime('now'));
                        $contact-> setModifiedById($this->getUser()->getId());
                        $contact-> setCreatedById($this->getUser()->getId());
                        $contact-> setCreatedById($this->getUser()->getId());
                        $contact-> setUserId($user->getId());
                        $contact->setSalutationName($record["salutation_name"]);

                        $contact->setFirstName($record["first_name"]);
                        $contact->setLastName($record["last_name"]);
                        $contact->setEmail($record["email"]);
                        $contact->setAddress($record["address"]);
                       // $contact->setBirhday(new \DateTime($record["birhday"]));


                        $contact->setBirhday( \DateTime::createFromFormat('d/m/Y',$record["birhday"]));


                        $contact->setPhone($record["phone"]);



                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($contact);
                        $entityManager->flush();

                        if ($this->getParameter('pd_user.email_confirmation')) {
                            // Disable User


                            // Create Confirmation Token
                            if (empty($user->getConfirmationToken()) || null === $user->getConfirmationToken()) {
                                $user->createConfirmationToken();
                            }

                            // Send Confirmation Email
                            $emailBody = [
                                'confirmationUrl' => $this->generateUrl('security_register_confirm',
                                    ['token' => $user->getConfirmationToken()],
                                    UrlGeneratorInterface::ABSOLUTE_URL),
                            ];
                            $this->sendEmail($user, $mailer, 'Account Confirmation', $emailBody, 'confirm');
                        }


                    }
                    }
                }





                unlink($this->getParameter('upload_dir').$originalFilename.'.csv');
            // Flash Message
            $this->addFlash('success', 'changes_saved');
        }

        // Render Page
        return $this->render('contact/xxx.html.twig', [
            'page_title' => 'contact_import_title',
            'page_menu' => ContactMenu::class,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTACT_LIST")
     * @Route("/", name="contact_index", methods={"GET"})
     */
    public function index(Request $request, PaginatorInterface $paginator,\Swift_Mailer $mailer): Response
    {

        // Query
        $query = $this
            ->getDoctrine()
            ->getRepository(Contact::class)
            ->createQueryBuilder('c')
            ->leftJoin(\App\Entity\Image::class, 'i' ,'WITH' , 'i.contact_id = c.id')
            ->select('c,i')
        ;


        if ($request->get('filter')) {
            $query
                ->where('(c.email LIKE :filter) or (c.firstName LIKE :filter) or (c.lastName LIKE :filter) ' )
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


        // Render Page
        return $this->render('contact/index.html.twig', [
            'contacts' => $pagination,
            'filterForm' => $this->createContactFilterForm()->createView(),



        ]);
    }
    private function sendEmailbirthday( $user, \Swift_Mailer $mailer, $subject, $templateId): bool
    {

        $body1 =
            $this
                ->getDoctrine()
                ->getRepository(MailTemplate::class)
                ->createQueryBuilder('u')
                ->select('u.template')
                ->where('u.id =  2 ' )


                ->getQuery()
                ->execute();


        $body =  str_replace('[[fullname]]',$user["u_firstName"].' '.$user["u_lastName"],$body1[0]["template"]);

        $body =  str_replace('{CompanyName}',$this->getParameter('CompanyName'),$body);


        // Create Message
        $message = (new PdSwiftMessage())
            ->setTemplateId($templateId)
            ->setFrom($this->getParameter('pd_user.mail_sender_address'), $this->getParameter('pd_user.mail_sender_name'))
            ->setTo($user["u_email"])
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        return (bool) $mailer->send($message);
    }
    /**
     * @IsGranted("ROLE_CONTACT_SEND")
     * @Route("/send", name="contact_send", methods={"GET","POST"})
     */
    public function birthdaycamapin(Request $request, PaginatorInterface $paginator,\Swift_Mailer $mailer):  RedirectResponse
    {
        $birthday_lists =
            $this
                ->getDoctrine()
                ->getRepository(Contact::class)
                ->createQueryBuilder('u')
                ->select('u')
                ->WHERE (' MONTH ( u.birhday ) = MONTH( :now ) and DAY ( u.birhday ) = DAY ( :now )  ')
                ->setParameter('now',new \DateTime('now'))
                ->getQuery()
                ->getScalarResult();


        // Send happybirthday

        if($birthday_lists) {

            foreach ($birthday_lists as $user) {


                $this->sendEmailbirthday($user, $mailer, 'birhday',  'happy_birthday');
            }
            $this->addFlash('success', 'Birthday email was sent');
        }
else
{
    $this->addFlash('danger', 'No Birthday today');

}

        return $this->redirect($request->headers->get('referer', $this->generateUrl('admin_dashboard')));
    }
    /**
     * @IsGranted("ROLE_CONTACT_ADD")
     * @Route("/new", name="contact_new", methods={"GET","POST"})
     */
    public function new(Request $request,\Swift_Mailer $mailer): Response
    {
        $contact = new Contact();
        $userp= new Profile();
        $user= new User();


        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userp-> setFirstname($contact-> getFirstName());
            $userp-> setLastname($contact-> getLastName());
            $userp-> setCompany('tuninfo');

            $userp-> setLanguage('fr');

            $userp-> setPhone($contact-> getPhone());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userp);
            $entityManager->flush();
            $user->setEmail($contact->getEmail());

            $user->setEnabled(false);
            $user->setFreeze(false);
            $user->setConfirmationToken($user->createConfirmationToken());
            $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$OGk2ODZOcHVITGI1eFFRTg$6t9JGukcHfpCHXIdcUhsSphkDqGSeI4rbJ2LZp1O5q4');
            $user->setCreatedAt(new \DateTime('now'));

            $entityManager = $this->getDoctrine()->getManager();

            $user->setProfile($userp);
            $entityManager->persist($user);
            $entityManager->flush();


            $contact-> setModifiedAt(new \DateTime('now'));
            $contact-> setCreatedAt(new \DateTime('now'));
            $contact-> setModifiedById($this->getUser()->getId());
            $contact-> setCreatedById($this->getUser()->getId());
            $contact-> setCreatedById($this->getUser()->getId());
            $contact-> setUserId($user->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();
            if ($this->getParameter('pd_user.email_confirmation')) {
                // Disable User


                // Create Confirmation Token
                if (empty($user->getConfirmationToken()) || null === $user->getConfirmationToken()) {
                    $user->createConfirmationToken();
                }

                // Send Confirmation Email
                $emailBody = [
                    'confirmationUrl' => $this->generateUrl('security_register_confirm',
                        ['token' => $user->getConfirmationToken()],
                        UrlGeneratorInterface::ABSOLUTE_URL),
                ];
                $this->sendEmail($user, $mailer, 'Account Confirmation', $emailBody, 'confirm');
            }

            return $this->redirectToRoute('admin_contact_index');
        }

        return $this->render('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Create contact Filter Form.
     */
    private function createContactFilterForm(): FormInterface
    {
        $form = $this->get('form.factory')
            ->createNamedBuilder(null, FormType::class, null, [

                'method' => 'get',
                'allow_extra_fields' => true,
            ])
            ->add('filter', TextType::class, [
                'label' => 'search_keyword',
                'attr' => ['placeholder' => 'search_keyword_Contacts_placeholder'],
                'required' => false,
            ])
            ->getForm();

        return $form;
    }


    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTACT_EDIT")
     * @Route("/edit/{contact}", name="contact_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Contact $contact): Response
    {

        // Create Form

        $form = $this->createForm(ContactType::class, $contact);

        // Handle Request
        $form->handleRequest($request);

        // Form Check
        if ($form->isSubmitted() && $form->isValid()) {

            // Check Super Admin & Check All Write

            $contact-> setModifiedAt(new \DateTime('now'));
            $contact-> setModifiedById($this->getUser()->getId());

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();



            // Flash Message
            $this->addFlash('success', 'changes_saved');
        }

        // Render Page
        return $this->render('contact/edit.html.twig', [
            'page_title' => 'contact_edit_title',
            'page_menu' => ContactMenu::class,
            'form' => $form->createView(),
            'item' => $contact,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTACT_DELETE")
     * @Route("/delete/{contact}", name="contact_delete")
     */
    public function delete(Request $request, Contact $contact): Response
    {

        // Check All Access
        $repository = $this->getDoctrine()->getRepository(LoyaltyCards::class);
        $lc = $repository->findOneBy(['customer_id' => $contact->getId()]);

        if ($lc) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($lc);
            $em->flush();

        }
        $repository = $this->getDoctrine()->getRepository(\App\Entity\Image::class);
        $img = $repository->findOneBy(['contact_id' => $contact->getId()]);
        if ($img) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($img);
            $em->flush();

        }
        $repository = $this->getDoctrine()->getRepository(Reclamation::class);
        $re = $repository->findOneBy(['contact_id' => $contact->getId()]);
        if ($re) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($re);
            $em->flush();

        }
        $repository = $this->getDoctrine()->getRepository(LoyaltyCardsRequests::class);
        $lcr = $repository->findOneBy(['customer_id' => $contact->getId()]);
        if ($lcr) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($lcr);
            $em->flush();

        }
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy(['id' => $contact->getUserId()]);
        if ($user) {

            $repository = $this->getDoctrine()->getRepository(Profile::class);
            $userp = $repository->findOneBy(['id' => $user->getProfile()]);


            //delete user
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
            //delete profile
            $em = $this->getDoctrine()->getManager();
            $em->remove($userp);
            $em->flush();
        }

        $repository = $this->getDoctrine()->getRepository(ContactService::class);
        $contserc = $repository->findBy(['contact_id' => $contact->getId()]);
        foreach ($contserc as $cose)
        {
            if ($cose) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($cose);
                $entityManager->flush();
            }
        }

        // Remove
        $em = $this->getDoctrine()->getManager();
        $em->remove($contact);
        $em->flush();

        // Flash Message
        $this->addFlash('success', 'remove_complete');

        // Redirect back
        return $this->redirect($request->headers->get('referer', $this->generateUrl('admin_contact_index')));
    }
    /**
     * export contact.
     *
     *@IsGranted("ROLE_CONTACT_EXPORT")
     * @Route(name="export_contact", path="/contact/export/{contact}")
     */

    public function exportcontact(Request $request ,Contact $contact): Response
    {

        $em = $this->getDoctrine()->getManager();
        //  $fadis=$em->getRepository(contact::class)->findAll();
        //   $writer = $this->container->get('egyg33k.csv.writer');
        $csv = writer::createFromFileObject(new \SplTempFileObject());

        $head = $em->getClassMetadata(contact::class)->getColumnNames();
        $csv->setDelimiter(';');
        $csv->insertOne($head);
        // foreach ($fadis as $fadi)
        // {
        $csv->insertOne([$contact->getId(),$contact->getSalutationName(),$contact->getFirstName(),$contact->getLastName(),$contact->getUserId(),$contact->getEmail(),$contact->getBirhday()->format('d/m/Y'),$contact->getPhone(),$contact->getAddress(),$contact->getCreatedAt()->format('d/m/Y'),$contact->getModifiedAt()->format('d/m/Y'),$contact->getCreatedById(),$contact->getModifiedById()]);

        // }
        $csv->output('contact-'.$contact->getId().'-'.$contact->getFirstName().'.csv');
        exit;
    }

    /**
     * export contact.
     *
     *@IsGranted("ROLE_CONTACT_EXPORT")
     * @Route(name="export_contacts", path="/contact/exports")
     */

    public function export(Request $request ): Response
    {

        $em = $this->getDoctrine()->getManager();
        //  $fadis=$em->getRepository(contact::class)->findAll();
        //   $writer = $this->container->get('egyg33k.csv.writer');
        $csv = writer::createFromFileObject(new \SplTempFileObject());
        $header = ['first name', 'last name', 'email'];

        $head = $em->getClassMetadata(contact::class)->getColumnNames();
        $csv->setDelimiter(';');
        $csv->insertOne($head);
        // foreach ($fadis as $fadi)
        // {

           $repository = $this->getDoctrine()->getRepository(Contact::class);
        $campainlist  = $repository->findAll();

// Send campain promo
        if ($campainlist) {


            foreach ($campainlist as $contact) {
                $csv->insertOne([$contact->getId(),$contact->getSalutationName(),$contact->getFirstName(),$contact->getLastName(),$contact->getUserId(),$contact->getEmail(),$contact->getBirhday()->format('d/m/Y'),$contact->getPhone(),$contact->getAddress(),$contact->getCreatedAt()->format('d/m/Y'),$contact->getModifiedAt()->format('d/m/Y'),$contact->getCreatedById(),$contact->getModifiedById()]);

                }
        }


        // }
        $csv->output('all_contacts.csv');
        exit;
    }
}
