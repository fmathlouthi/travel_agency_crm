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

use App\Entity\Account\Group;
use App\Entity\Account\User;
use App\Entity\Account\Profile;
use App\Entity\Contact;
use App\Entity\ContactService;
use App\Entity\LoyaltyCards;
use App\Entity\LoyaltyCardsRequests;
use App\Entity\Reclamation;
use App\Form\Account\RolesType;
use App\Form\ContactType;
use App\Form\RegisterType;
use App\Manager\SecurityManager;
use App\Menu\AccountMenu;
use Knp\Component\Pager\PaginatorInterface;
use Pd\UserBundle\Event\UserEvent;
use Pd\UserBundle\Form\ChangePasswordType;
use Pd\UserBundle\Form\ProfileType;
use Pd\UserBundle\Model\GroupInterface;
use Pd\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;




/**
 * Controller managing the user profile.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class AccountController extends AbstractController
{
    /**
     * Security Manager Add Custom Roles.
     */
    public const CUSTOM_ROLES = [
        'ROLE_ACCOUNT_ALLREAD',
        'ROLE_ACCOUNT_ALLWRITE',
    ];

    /**
     * add new admin Account.
     *
     * @IsGranted("ROLE_ACCOUNT_ADD")
     * @Route(name="account_addadmin", path="/account/adminadd", methods={"GET","POST"})
     */

    public function newadmin(Request $request, EventDispatcherInterface $dispatcher, TranslatorInterface $translator, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {


        $userp= new Profile();
        $user= new User();


        $form = $this->createForm(RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userp-> setFirstname($form->get('firstname')->getData());
            $userp-> setLastname($form->get('lastname')->getData());
            $userp-> setCompany('tuninfo');

            $userp-> setLanguage('fr');

            $userp-> setPhone($form->get('phone')->getData());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userp);
            $entityManager->flush();
            $user->setEmail($form->get('email')->getData());

            $user->setEnabled(false);
            $user->setFreeze(false);
            $user->setConfirmationToken($user->createConfirmationToken());
            $user->setCreatedAt(new \DateTime('now'));
            $password = $encoder->encodePassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($password);
            $entityManager = $this->getDoctrine()->getManager();

            $user->setProfile($userp);
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_account_list');
        }

        return $this->render('Admin/Account/newadmin.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);








    }

    /**
     * Show all Account.
     *
     * @IsGranted("ROLE_ACCOUNT_LIST")
     * @Route(name="account_list", path="/account")
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        // Query
        $query = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.profile', 'p')
            ->addSelect('p')
        ->where (' u.roles like :supx or u.roles like :admx')
            ->setParameter('supx', '%"ROLE_SUPER_ADMIN"%')
            ->setParameter('admx', '%"ROLE_ADMIN"%');

        // Check Owner or All Access
        if (!$this->isGranted('ADMIN_ACCOUNT_ALLREAD')) {
            $query
                ->andWhere('u.id = :id')
                ->setParameter('id', $this->getUser()->getId());
        }

        // Add Filter
        if ($request->get('filter')) {
            $query
                ->where('(u.email LIKE :filter) or (p.firstname LIKE :filter) or (p.lastname LIKE :filter) or (p.phone LIKE :filter) or (p.company LIKE :filter)')
                ->setParameter('filter', "%{$request->get('filter')}%");
        }
        if ($request->get('status')) {
            $query
                ->andWhere('u.isActive = :status')
                ->setParameter('status', $request->get('filter'));
        }

        // Get Result
        $pagination = $paginator->paginate(
            $query->getQuery(),
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', $this->getParameter('list_count'))
        );

        // Set Back URL
        $this->get('session')->set('backUrl', $request->getRequestUri());

        // Render Page
        return $this->render('Admin/Account/list.html.twig', [
            'users' => $pagination,
            'filterForm' => $this->createUserFilterForm()->createView(),
        ]);
    }
    /**
     * Show all user Account.
     *
     * @IsGranted("ROLE_ACCOUNT_LIST")
     * @Route(name="user_account_list", path="/useraccount")
     */
    public function listuser(Request $request, PaginatorInterface $paginator): Response
    {
        // Query
        $query = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.profile', 'p')
            ->addSelect('p')
            ->where (' u.roles like :userx ')
            ->setParameter('userx', '%"ROLE_USER"%');

        // Check Owner or All Access


        // Add Filter
        if ($request->get('filter')) {
            $query
                ->where('(u.email LIKE :filter) or (p.firstname LIKE :filter) or (p.lastname LIKE :filter) or (p.phone LIKE :filter) or (p.company LIKE :filter)')
                ->setParameter('filter', "%{$request->get('filter')}%");
        }
        if ($request->get('status')) {
            $query
                ->andWhere('u.isActive = :status')
                ->setParameter('status', $request->get('filter'));
        }

        // Get Result
        $pagination = $paginator->paginate(
            $query->getQuery(),
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', $this->getParameter('list_count'))
        );

        // Set Back URL
        $this->get('session')->set('backUrl', $request->getRequestUri());

        // Render Page
        return $this->render('Admin/Account/listuser.html.twig', [
            'users' => $pagination,
            'filterForm' => $this->createUserFilterForm()->createView(),
        ]);
    }

    /**
     * Create User Filter Form.
     */
    private function createUserFilterForm(): FormInterface
    {
        $form = $this->get('form.factory')
            ->createNamedBuilder(null, FormType::class, null, [
                'csrf_protection' => false,
                'method' => 'get',
                'allow_extra_fields' => true,
            ])
            ->add('filter', TextType::class, [
                'label' => 'search_keyword',
                'attr' => ['placeholder' => 'search_keyword_account_placeholder'],
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'account_status',
                'choices' => [
                    'select_all' => null,
                    'deactive' => '0',
                    'active' => '1',
                ],
            ])
            ->getForm();

        return $form;
    }

    /**
     * Edit the User.
     *
     * @IsGranted("ROLE_ACCOUNT_EDIT")
     * @Route(name="account_edit", path="/account/edit/{user}")
     */
    public function edit(Request $request, User $user, ParameterBagInterface $bag): Response
    {
        // Check Read Only
        $this->checkOwner($user, 'ADMIN_ACCOUNT_ALLREAD');

        // Create Form
        $form = $this->createForm(ProfileType::class, $user, [
            'parameter_bag' => $bag,
        ]);

        // Handle Request
        $form->handleRequest($request);

        // Form Check
        if ($form->isSubmitted() && $form->isValid()) {
            // Check Super Admin & Check All Write
            $this->checkAllAccess($user);
            $this->checkOwner($user, 'ADMIN_ACCOUNT_ALLWRITE');

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // Change Site Language
            if ($this->getUser()->getId() === $user->getId()) {
                $request->getSession()->set('_locale', $form->get('profile')['language']->getData());
            }

            // Flash Message
            $this->addFlash('success', 'changes_saved');
        }

        // Render Page
        return $this->render('Admin/Account/edit.html.twig', [
            'page_title' => 'account_edit_title',
            'page_description' => sprintf('%s - %s', $user->getProfile()->getFullName(), $user->getEmail()),
            'page_menu' => AccountMenu::class,
            'form' => $form->createView(),
            'item' => $user,
        ]);
    }

    /**
     * Change User Password.
     *
     * @IsGranted("ROLE_ACCOUNT_CHANGEPASSWORD")
     * @Route(name="account_changepassword", path="/account/changepassword/{user}")
     */
    public function changePassword(Request $request, User $user, UserPasswordEncoderInterface $encoder): Response
    {
        // Check Read Only
        $this->checkOwner($user, 'ADMIN_ACCOUNT_ALLREAD');

        // Create Form
        $form = $this->createForm(ChangePasswordType::class, $user, [
            'disable_current_password' => $this->isGranted(User::ROLE_ALL_ACCESS) ||
                $this->isGranted('ADMIN_ACCOUNT_ALLWRITE'),
        ]);

        // Handle Request
        $form->handleRequest($request);

        // Form Submit & Valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Check Super Admin & Check All Write
            $this->checkAllAccess($user);
            $this->checkOwner($user, 'ADMIN_ACCOUNT_ALLWRITE');

            // Encode Password
            $password = $encoder->encodePassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($password);

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // Flash Message
            $this->addFlash('success', 'changes_saved');
        }

        // Render Page
        return $this->render('Admin/Account/edit.html.twig', [
            'page_title' => 'account_change_password_title',
            'page_description' => sprintf('%s - %s', $user->getProfile()->getFullName(), $user->getEmail()),
            'page_menu' => AccountMenu::class,
            'form' => $form->createView(),
            'item' => $user,
        ]);
    }

    /**
     * Change User Private Roles.
     *
     * @IsGranted("ROLE_ACCOUNT_ROLES")
     * @Route(name="account_roles", path="/account/role/{user}")
     */
    public function roles(Request $request, User $user, SecurityManager $security): Response
    {
        dump(array_intersect($security->getACL(), $user->getRolesUser()));

        // Set Form & Request
        $form = $this->createForm(RolesType::class, null, [
            'roles' => $security->getRoles(),
            'acl' => $security->getACL(),
            'userRoles' => $user->getRolesUser(),
        ]);
        $form->handleRequest($request);

        // Valid Form
        if ($form->isSubmitted() && $form->isValid()) {
            // Check Super Admin
            $this->checkAllAccess($user);

            // User Add Roles
            $roles = $form->get('roles')->getData();
            if ($form->has('acl')) {
                $roles = array_merge($roles, [$form->get('acl')->getData()]);
                $roles = array_merge($roles, $form->get('aclprocess')->getData());
            }
            if ($roles) {
                $user->setRoles($roles);
            }

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // View Message
            $this->addFlash('success', 'changes_saved');
        }

        // Render Page
        return $this->render('Admin/Account/edit.html.twig', [
            'page_title' => 'account_roles_title',
            'page_description' => sprintf('%s - %s', $user->getProfile()->getFullName(), $user->getEmail()),
            'page_menu' => AccountMenu::class,
            'form' => $form->createView(),
            'item' => $user,
        ]);
    }

    /**
     * Account Append Group.
     *
     * @IsGranted("ROLE_ACCOUNT_ADDGROUP")
     * @Route(name="account_addgroup", path="/account/addGroup/{user}")
     *
     * @return RedirectResponse|Response
     */
    public function addGroup(Request $request, User $user)
    {
        // Get Group Name
        $groupName = $user->getGroupNames();

        // Create Form
        $form = $this->createFormBuilder()
            ->add('group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'choice_attr' => function ($obj) use ($groupName) {
                    return \in_array($obj->getName(), $groupName, true) ? ['selected' => ''] : [];
                },
                'label' => 'account_groups',
                'multiple' => true,
                'expanded' => false,
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('Submit', SubmitType::class, [
                'label' => 'save',
            ])
            ->getForm();
        $form->handleRequest($request);

        // Form Request
        if ($form->isSubmitted() && $form->isValid()) {
            // Add user to group
            $user->getGroups()->clear();
            foreach ($form->get('group')->getData() as $group) {
                $user->addGroup($group);
            }

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // Flash Message
            $this->addFlash('success', 'changes_saved');

            // Redirect
            return $this->redirectToRoute('admin_account_addgroup', ['user' => $user->getId()]);
        }

        // Render
        return $this->render('Admin/Account/edit.html.twig', [
            'page_title' => 'account_add_group_title',
            'page_description' => sprintf('%s - %s', $user->getProfile()->getFullName(), $user->getEmail()),
            'page_menu' => AccountMenu::class,
            'form' => $form->createView(),
            'item' => $user,
        ]);
    }

    /**
     * Delete Account.
     *
     * @IsGranted("ROLE_ACCOUNT_DELETE")
     * @Route(name="account_delete", path="/accounts/delete/{user}")
     */
    public function delete(Request $request, User $user): RedirectResponse
    {
        // Check All Access
        $this->checkAllAccess($user);
        $repository = $this->getDoctrine()->getRepository(Profile::class);
        $userp = $repository->findOneBy(['id' => $user->getProfile()]);



        $repository = $this->getDoctrine()->getRepository(Contact::class);
        $product = $repository->findOneBy(['userId' => $user->getId()]);
        if ($product) {
            $repository = $this->getDoctrine()->getRepository(LoyaltyCards::class);
            $lc = $repository->findOneBy(['customer_id' => $product->getId()]);

            if ($lc) {

                $em = $this->getDoctrine()->getManager();
                $em->remove($lc);
                $em->flush();

            }
            $repository = $this->getDoctrine()->getRepository(\App\Entity\Image::class);
            $img = $repository->findOneBy(['contact_id' => $product->getId()]);
            if ($img) {

                $em = $this->getDoctrine()->getManager();
                $em->remove($img);
                $em->flush();

            }
            $repository = $this->getDoctrine()->getRepository(LoyaltyCardsRequests::class);
            $lcr = $repository->findOneBy(['customer_id' => $product->getId()]);
            if ($lcr) {

                $em = $this->getDoctrine()->getManager();
                $em->remove($lcr);
                $em->flush();

            }
            $repository = $this->getDoctrine()->getRepository(Reclamation::class);
            $re = $repository->findOneBy(['contact_id' => $product->getId()]);
            if ($re) {

                $em = $this->getDoctrine()->getManager();
                $em->remove($re);
                $em->flush();

            }
            $repository = $this->getDoctrine()->getRepository(ContactService::class);
            $contserc = $repository->findBy(['contact_id' => $product->getId()]);
            foreach ($contserc as $cose)
            {
                if ($cose) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($cose);
                    $entityManager->flush();
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        // Remove
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
//delete profile
        $em = $this->getDoctrine()->getManager();
        $em->remove($userp);
        $em->flush();
        // Flash Message
        $this->addFlash('success', 'remove_complete');

        // Redirect back
        return $this->redirect($request->headers->get('referer', $this->generateUrl('admin_account_list')));
    }

    /**
     * Activate/Deactivate Account.
     *
     * @param $status
     *
     * @IsGranted("ROLE_ACCOUNT_ACTIVATE")
     * @Route(name="account_activate", path="/account/activate/{user}/{status}")
     */
    public function activate(Request $request, User $user, $status): RedirectResponse
    {
        // Check All Access
        $this->checkAllAccess($user);

        // Activate / Deactivate
        $user->setEnabled($status);

        // Update
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // Flash Message
        $this->addFlash('success', 'changes_saved');

        // Redirect back
        return $this->redirect($request->headers->get('referer', $this->generateUrl('admin_account_list')));
    }

    /**
     * Freeze Account.
     *
     * @param $status
     *
     * @IsGranted("ROLE_ACCOUNT_FREEZE")
     * @Route(name="account_freeze", path="/account/freeze/{user}/{status}")
     */
    public function freeze(Request $request, User $user, $status): RedirectResponse
    {
        // Check All Access
        $this->checkAllAccess($user);

        // Activate / Deactivate
        $user->setFreeze((bool) $status);

        // Update
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // Flash Message
        $this->addFlash('success', 'changes_saved');

        // Redirect back
        return $this->redirect($request->headers->get('referer', $this->generateUrl('admin_account_list')));
    }

    /**
     * Check Current User All Access.
     */
    private function checkAllAccess(UserInterface $user)
    {
        if ($user->hasRole(User::ROLE_ALL_ACCESS) && !$this->getUser()->hasRole(User::ROLE_ALL_ACCESS)) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * Check Current User Read Only.
     *
     * @param $access
     */
    private function checkOwner(UserInterface $user, $access)
    {
        if (!$this->isGranted($access)) {
            if ($user->getId() !== $this->getUser()->getId()) {
                throw $this->createAccessDeniedException();
            }
        }
    }
}
