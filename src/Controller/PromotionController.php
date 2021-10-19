<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Form\PromotionimageType;
use App\Form\PromotioneditType;
use App\Repository\PromotionRepository;
use App\Repository\ServiceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Pd\MailerBundle\SwiftMailer\PdSwiftMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
 * @Route("/promotion")
 */
class PromotionController extends AbstractController
{
    /**
     * Security Manager Add Custom Roles.
     */
    public const CUSTOM_ROLES = [
        'ROLE_PROMOTION_ALLREAD',
        'ROLE_PROMOTION_ALLWRITE',

    ];
    /**
     * @IsGranted("ROLE_PROMOTION_LIST")
     * @Route("/", name="promotion_index", methods={"GET"})
     */

       public function index(PromotionRepository $promotionRepository ,Request $request, PaginatorInterface $paginator): Response
    {
        // Query
        $query = $this
            ->getDoctrine()
            ->getRepository(Promotion::class)
            ->createQueryBuilder('p')

        ;
        if ($request->get('filter')) {
            $query
                ->where('p.name LIKE :filter ' )
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

        return $this->render('promotion/index.html.twig', [
            'promotions' => $pagination,
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
                'attr' => ['placeholder' => 'Service name'],
                'required' => false,
            ])
            ->getForm();

        return $form;
    }

    private function promoemail( $user,$promotion,$file, \Swift_Mailer $mailer, $subject, $templateId): bool
    {

        $body1 =
            $this
                ->getDoctrine()
                ->getRepository(MailTemplate::class)
                ->createQueryBuilder('u')
                ->select('u.template')
                ->where('u.templateid like :tid ' )

                ->setParameter('tid',$templateId)

                ->getQuery()
                ->execute();


        $body =  str_replace('[[fullname]]',$user["c_firstName"].' '.$user["c_lastName"],$body1[0]["template"]);


        $body =  str_replace('[[des.pro]]',$promotion->getDescription(), $body);
        $body =  str_replace('[[name.pro]]',$promotion->getName(), $body);
        $body =  str_replace('[[link.pro]]',$promotion->getLinkpro(), $body);

        // Create Message
        $message = (new PdSwiftMessage())
            ->setTemplateId($templateId)
            ->setFrom($this->getParameter('pd_user.mail_sender_address'), $this->getParameter('pd_user.mail_sender_name'))
            ->setTo($user["c_email"])
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        return (bool) $mailer->send($message);
    }
    /**
     * @IsGranted("ROLE_PROMOTION_ADD")
     * @Route("/new", name="promotion_new", methods={"GET","POST"})
     */
    public function new(Request $request,\Swift_Mailer $mailer): Response
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promotion-> setUpdatedAt(new \DateTime('now'));
            $promotion-> setCreatedAt(new \DateTime('now'));
            $x=$form->get('image')->getData();
            $originalFilename = pathinfo($x->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $x->guessExtension();

            $file=$originalFilename.'-'.uniqid();

            $x->move($this->getParameter('upload_dir_image'),$file.'.'.$extension );
            $promotion->setImage($file.'.'.$extension);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($promotion);
            $entityManager->flush();


                $campainlist =
                    $this
                        ->getDoctrine()
                        ->getRepository(Contact::class)
                        ->createQueryBuilder('c')

                        ->Select('c')



                        ->getQuery()
                        ->getScalarResult();

// Send campain promo
                if ($campainlist) {

                    foreach ($campainlist as $user) {

                        $this->promoemail($user,$promotion,$file, $mailer, 'Promotion for you', 'promo');
                    }
                }

            return $this->redirectToRoute('promotion_index');
        }

        return $this->render('promotion/new.html.twig', [
            'promotion' => $promotion,
            'form' => $form->createView(),
        ]);
    }


    public function show(Promotion $promotion): Response
    {
        return $this->render('promotion/show.html.twig', [
            'promotion' => $promotion,
        ]);
    }

    /**
     * @IsGranted("ROLE_PROMOTION_EDIT")
     * @Route("/{id}/edit", name="promotion_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Promotion $promotion): Response
    {


        $form = $this->createForm(PromotioneditType::class, $promotion);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


//            $x=$form->get('image')->getData();
//            if($x) {
//                $originalFilename = pathinfo($x->getClientOriginalName(), PATHINFO_FILENAME);
//                $extension = $x->guessExtension();
//                $file = $originalFilename . '-' . uniqid();
//                $x->move($this->getParameter('upload_dir_image'), $file . '.' . $extension);
//                $promotion->setImage($file . '.' . $extension);
//            }
//
            $promotion-> setUpdatedAt(new \DateTime('now'));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('promotion_index');
        }

        return $this->render('promotion/edit.html.twig', [
            'promotion' => $promotion,
            'form' => $form->createView(),
            'image1'=> 'uploads/images/'.$promotion->getImage(),
        ]);
    }
    /**
     * @IsGranted("ROLE_PROMOTION_EDIT")
     * @Route("/{id}/editimage", name="promotion_editimage", methods={"GET","POST"})
     */
    public function editimage(Request $request, Promotion $promotion): Response
    {


        $form = $this->createForm(PromotionimageType::class, $promotion);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $x=$form->get('image')->getData();
            if($x) {
                $originalFilename = pathinfo($x->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $x->guessExtension();
                $file = $originalFilename . '-' . uniqid();
                $x->move($this->getParameter('upload_dir_image'), $file . '.' . $extension);
                $promotion->setImage($file . '.' . $extension);
            }
//
            $promotion-> setUpdatedAt(new \DateTime('now'));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('promotion_index');
        }

        return $this->render('promotion/editimage.html.twig', [
            'promotion' => $promotion,
            'form' => $form->createView(),
            'image1'=> 'uploads/images/'.$promotion->getImage(),
        ]);
    }

    /**
     * @IsGranted("ROLE_PROMOTION_DELETE")
     * @Route("/delete/{id}", name="promotion_delete")
     */
    public function delete(Request $request, Promotion $promotion): Response
    {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($promotion);
            $entityManager->flush();
            $this->addFlash('success', 'remove_complete');


        return $this->redirect($request->headers->get('referer', $this->generateUrl('admin_promotion_index')));

    }
}
