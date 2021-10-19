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
use App\Entity\LoyaltyCardRequest;
use App\Entity\LoyaltyCardsRequests;
use App\Entity\MailTemplate;
use App\Entity\Taux;
use App\Entity\User;
use Doctrine\DBAL\Types\IntegerType;
use Pd\MailerBundle\SwiftMailer\PdSwiftMessage;
use Pd\WidgetBundle\Widget\WidgetInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Admin Dashboard.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class DashboardController extends AbstractController
{
    /**
     * Dashboard Index.
     *@IsGranted("ROLE_ADMIN")
     * @Route(name="dashboard", path="/")
     *
     */
    public function index(Request $request,\Swift_Mailer $mailer): Response
    {

        $query = $this->getDoctrine() ->getRepository(LoyaltyCardsRequests::class)

            ->createQueryBuilder('lcr')
            ->leftJoin(Contact::class, 'c','WITH' , 'lcr.customer_id = c.id')
            ->Select('c,lcr')
            ->WHERE (' lcr.status = 0  ')
            ->getQuery()
            ->getScalarResult();
        if ($request->get('filter')) {
            $repository = $this->getDoctrine()->getRepository(Taux::class);
            $lc = $repository->findAll();
            if($lc)
            {
            foreach ($lc as $x)
            {
                $x->setPointnuber((int)$request->get('filter'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($x);
                $em->flush();
            }
            }
            else
            {
                $lc= new    Taux();
                $lc->setPointnuber((int)$request->get('filter'));
                $entityManager = $this->getDoctrine()->getManager();

                $entityManager->persist($lc);
                $entityManager->flush();
            }
        }
        if ($request->get('template_id') ) {

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

                    $this->campain($user, $mailer, 'Promotion for you', (int)$request->get('template_id'),$request->get('descrption'),$request->get('url'));
                }
            }
        }
        $userCount = $this
            ->getDoctrine()->getRepository(Contact::class)
            ->createQueryBuilder('u')
            ->select('count(u.id)')
            ->WHERE (' MONTH ( u.birhday ) = MONTH( :now ) and DAY ( u.birhday ) = DAY ( :now )  ')
            ->setParameter('now',new \DateTime('now'))

            ->getQuery()
            ->getSingleScalarResult();
        // Set Back URL
        $this->get('session')->set('backUrl', $this->get('router')->generate('admin_dashboard'));

        // Render Page

        return $this->render('Admin/dashboard.html.twig', [

            'card_request' => $query,
            'tauxForm' => $this->createtauxForm()->createView(),
            'proForm' => $this->createProForm()->createView(),
            'xxx' => $userCount,

        ]);
    }
    private function campain( $user, \Swift_Mailer $mailer, $subject, $templateId,$des,$url): bool
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

            $body =  str_replace('[[dis]]',$des,$body);
            $body =  str_replace('[[link]]',$url,$body);
            $body =  str_replace('{CompanyName}',$this->getParameter('CompanyName'),$body);





        // Create Message
        $message = (new PdSwiftMessage())
            ->setTemplateId($templateId)
            ->setFrom($this->getParameter('pd_user.mail_sender_address'), $this->getParameter('pd_user.mail_sender_name'))
            ->setTo($user["c_email"])
            ->setSubject($subject)
            ->setBody($body, 'text/html');

        return (bool) $mailer->send($message);
    }

    private function createProForm(): FormInterface
    {


        $query2 = $this
            ->getDoctrine()
            ->getRepository(MailTemplate::class)
            ->createQueryBuilder('mt')
            ->where('mt.templateid LIKE :service or mt.templateid LIKE :promo' )
            ->setParameter('service','%service%')
            ->setParameter('promo','%promo%')
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
            ->add('descrption', TextType::class, [
                'label' => 'descrption',
                'help' => 'please insert descrption',

                'empty_data' => 'descrption',
                'attr' => ['placeholder-nt' => 'descrption'],
                'required' => false,
            ])
            ->add('url', TextType::class, [
                'label' => 'URL',
                'help' => 'please insert URL',

                'empty_data' => 'URL',
                'attr' => ['placeholder-nt' => 'URL'],
                'required' => false,
            ])
            ->getForm();

        return $form;
    }


    private function createtauxForm(): FormInterface
    {
        $form = $this->get('form.factory')
            ->createNamedBuilder(null, FormType::class, null, [

                'method' => 'get',
                'allow_extra_fields' => true,
            ])
            ->add('filter', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'taux de point/reservation',
                'attr' => ['placeholder' => 100],
                'required' => false,
            ])

            ->getForm();

        return $form;
    }

    /**
     * Change Language for Session.
     *
     * @param string $lang
     *
     * @Route(name="language", path="/language/{lang}")
     */
    public function changeLanguage(Request $request, WidgetInterface $widget, $lang): RedirectResponse
    {
        // Set Language for Session
        $request->getSession()->set('_locale', $lang);

        // Flush Widget Cache
        $widget->clearWidgetCache();

        // Return Back
        return $this->redirect($request->headers->get('referer', $this->generateUrl('admin_dashboard')));
    }


}
