<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\ContactService;
use App\Entity\Service;
use App\Form\ServiceeditType;
use App\Form\ServiceType;
use App\Form\ServiceimageType;
use App\Repository\ServiceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use League\Csv\Reader;
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

use Pd\UserBundle\Form\ChangePasswordType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;




/**
 * @Route("/service")
 */
class ServiceController extends AbstractController
{
    public const CUSTOM_ROLES = [
        'ROLE_SERVICE_ALLREAD',
        'ROLE_SERVICE_ALLWRITE',
        ];
    /**
     * @IsGranted("ROLE_SERVICE_LIST")
     * @Route("/", name="service_index", methods={"GET"})
     */
    public function index(ServiceRepository $serviceRepository ,Request $request, PaginatorInterface $paginator): Response
    {
        // Query
        $query = $this
            ->getDoctrine()
            ->getRepository(Service::class)
            ->createQueryBuilder('s')

        ;
        if ($request->get('filter')) {
            $query
                ->where('s.service_name LIKE :filter ' )
                ->setParameter('filter', "%{$request->get('filter')}%");
        }
        // Get Result
        $pagination = $paginator->paginate(
            $query->getQuery(),
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', $this->getParameter('list_count'))
        );

        // Set Back URL
        $this->get('session')->set('backUrl', $request->getRequestUri());

        return $this->render('service/index.html.twig', [
            'services' => $pagination,
            'filterForm' => $this->createServiceFilterForm()->createView(),

        ]);

    }
    private function sendEmail(array $user, \Swift_Mailer $mailer, $subject,Service $body4, $templateId): bool
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


        $body =  str_replace('[[name]]',$user['u_firstName'].'  '.$user['u_lastName'],$body1[0]["template"]);
        $body =  str_replace('[[confirmurl]]',$body4->getLinkservice(),$body);
        $body =  str_replace('[[desc]]',$body4->getDiscreption(),$body);

        // Create Message
        $message = (new PdSwiftMessage())
            ->setTemplateId($templateId)
            ->setFrom($this->getParameter('pd_user.mail_sender_address'), $this->getParameter('pd_user.mail_sender_name'))
            ->setTo($user['u_email'])
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        return (bool) $mailer->send($message);
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
                'attr' => ['placeholder' => 'Service name'],
                'required' => false,
            ])
            ->getForm();

        return $form;
    }
    /**
     * @IsGranted("ROLE_SERVICE_ADD")
     * @Route("/new", name="service_new", methods={"GET","POST"})
     */
    public function new(Request $request,\Swift_Mailer $mailer): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service = new Service();
            $service-> setCreateAt(new \DateTime('now'));
            $service-> setUpdateAt(new \DateTime('now'));
            $service-> setDiscreption($form->get('discreption')->getData());
            $service-> setLinkservice($form->get('linkservice')->getData());
            $service-> setServiceName($form->get('service_name')->getData());
            $x=$form->get('image')->getData();
            $originalFilename = pathinfo($x->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $x->guessExtension();
$file=$originalFilename.'-'.uniqid();
            $x->move($this->getParameter('upload_dir_image_service'),$file.'.'.$extension );
            $service->setImage($file.'.'.$extension);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($service);
            $entityManager->flush();


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($service);
            $entityManager->flush();
            $birthday_lists =
                $this
                    ->getDoctrine()
                    ->getRepository(Contact::class)
                    ->createQueryBuilder('u')
                    ->select('u')

                    ->getQuery()
                    ->getScalarResult();


            // Send happybirthday

            if($birthday_lists) {

                foreach ($birthday_lists as $user) {


                    $this->sendEmail($user, $mailer, 'New service ', $service, 'service');

                }
                $this->addFlash('success', 'new services email was sent');
            }

            return $this->redirectToRoute('admin_service_index');
        }
if(empty($service->getImage()))
{
    $xx='shinigami-laser_6.jpg';
}
else
{
    $xx=$service->getImage();
}
        return $this->render('service/new.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
            'image'=> 'images/centers/'.$xx,
        ]);
    }


    public function show(Service $service): Response
    {
        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }

    /**
     * @IsGranted("ROLE_SERVICE_EDIT")
     * @Route("/{id}/edit", name="service_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Service $service): Response
    {


        $form = $this->createForm(ServiceeditType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//            $x=$form->get('image')->getData();
//           if($x) {
//               $originalFilename = pathinfo($x->getClientOriginalName(), PATHINFO_FILENAME);
//               $extension = $x->guessExtension();
//               $file = $originalFilename . '-' . uniqid();
//               $x->move($this->getParameter('upload_dir_image_service'), $file . '.' . $extension);
//
//    $service->setImage($file . '.' . $extension);
//
//           }




            $service-> setUpdateAt(new \DateTime('now'));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_service_index');
        }

        if(empty($service->getImage()))
    {
        $xx='shinigami-laser_6.jpg';
    }
    else
    {
        $xx=$service->getImage();
    }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
            'image'=> 'images/centers/'.$xx,
        ]);
    }
    /**
     * @IsGranted("ROLE_SERVICE_EDIT")
     * @Route("/{id}/editimage", name="service_editimage", methods={"GET","POST"})
     */
    public function editimage(Request $request, Service $service): Response
    {


        $form = $this->createForm(ServiceimageType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $x=$form->get('image')->getData();
           if($x) {
               $originalFilename = pathinfo($x->getClientOriginalName(), PATHINFO_FILENAME);
               $extension = $x->guessExtension();
               $file = $originalFilename . '-' . uniqid();
               $x->move($this->getParameter('upload_dir_image_service'), $file . '.' . $extension);

    $service->setImage($file . '.' . $extension);

           }




            $service-> setUpdateAt(new \DateTime('now'));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_service_index');
        }

        if(empty($service->getImage()))
        {
            $xx='shinigami-laser_6.jpg';
        }
        else
        {
            $xx=$service->getImage();
        }

        return $this->render('service/editimage.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
            'image'=> 'images/centers/'.$xx,
        ]);
    }

    /**
     * @IsGranted("ROLE_SERVICE_DELETE")
     * @Route("/delete/{id}", name="service_delete")
     */
    public function delete(Request $request, Service $service): Response
    {

        $repository = $this->getDoctrine()->getRepository(ContactService::class);
        $contserc = $repository->findBy(['contact_id' => $service->getId()]);
        foreach ($contserc as $cose)
        {
            if ($cose) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($cose);
                $entityManager->flush();
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($service);
        $entityManager->flush();

        // Flash Message
        $this->addFlash('success', 'remove_complete');

        return $this->redirectToRoute('admin_service_index');
    }
}
