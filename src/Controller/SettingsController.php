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

use App\Form\Config\ContactForm;
use App\Form\Config\CoreForm;
use App\Form\Config\EmailForm;
use App\Form\Config\GeneralForm;
use App\Form\Config\MediaForm;
use App\Form\Config\UserForm;
use App\Manager\ConfigManager;
use Pd\MailerBundle\SwiftMailer\PdSwiftMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller managing the settings.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class SettingsController extends AbstractController
{
    /**
     * General Settings.
     *
     * @IsGranted("ROLE_SETTINGS_GENERAL")
     * @Route(name="settings_general", path="/settings")
     *
     * @return RedirectResponse|Response
     */
    public function general(Request $request): Response
    {
        // Get Config Manager
        $cm = new ConfigManager($this->getDoctrine()->getManager(), $this->get('parameter_bag'), 'settings_general');

        // Create Form
        $form = $this->createForm(GeneralForm::class, $cm->getAll(), ['container' => $this->container]);

        // Handle Request
        $form->handleRequest($request);

        // Submit & Valid Form
        if ($form->isSubmitted() && $form->isValid()) {
            // Save Config
            $cm->saveConfig($form);

            // Flash Message
            $this->addFlash('success', 'changes_saved');

            // Refresh Page
            return $this->redirectToRoute('admin_settings_general');
        }

        // Render Page
        return $this->render('Admin/Settings/index.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'settings_general',
            'page_description' => 'settings_general_desc',
        ]);
    }

    /**
     * Contact Settings.
     *
     * @IsGranted("ROLE_SETTINGS_CONTACT")
     * @Route(name="settings_contact", path="/settings/contact")
     *
     * @return RedirectResponse|Response
     */
    public function contact(Request $request)
    {
        // Get Config Manager
        $cm = new ConfigManager($this->getDoctrine()->getManager(), $this->get('parameter_bag'), 'settings_contact');

        // Create Form
        $form = $this->createForm(ContactForm::class, $cm->getAll());

        // Handle Request
        $form->handleRequest($request);

        // Submit & Valid Form
        if ($form->isSubmitted() && $form->isValid()) {
            // Save Config
            $cm->saveConfig($form);

            // Flash Message
            $this->addFlash('success', 'changes_saved');

            // Refresh Page
            return $this->redirectToRoute('admin_settings_contact');
        }

        // Render Page
        return $this->render('Admin/Settings/index.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'settings_contact',
            'page_description' => 'settings_contact_desc',
        ]);
    }

    /**
     * Email Settings.
     *
     * @IsGranted("ROLE_SETTINGS_EMAIL")
     * @Route(name="settings_email", path="/settings/email")
     *
     * @return RedirectResponse|Response
     */
    public function email(Request $request)
    {
        // Get Config Manager
        $cm = new ConfigManager($this->getDoctrine()->getManager(), $this->get('parameter_bag'), 'settings_email');

        // Create Form
        $form = $this->createForm(EmailForm::class, $cm->getAll());

        // Handle Request
        $form->handleRequest($request);

        // Submit & Valid Form
        if ($form->isSubmitted() && $form->isValid()) {
            // Test Button is Clicked
            if ('submitTest' === $form->getClickedButton()->getName()) {
                $toEmail = $form->get('mail_test_address')->getData();

                // Send Mail
                if (null === $toEmail || empty($toEmail)) {
                    $this->addFlash('error', 'test_email_empty_address');
                } else {
                    // Test Message
                    $mailLogger = new \Swift_Plugins_Loggers_EchoLogger();
                    $message = (new PdSwiftMessage())
                        ->setTemplateId('pd_tester_email')
                        ->setFrom($form->get('mail_sender_address')->getData())
                        ->setTo($toEmail)
                        ->setSubject('pdAdmin Test Email')
                        ->setBody('pdAdmin Test Email!');

                    // Send Mail
                    $mail = $this->get('mailer');
                    $mail->registerPlugin(new \Swift_Plugins_LoggerPlugin($mailLogger));

                    if ($mail->send($message)) {
                        $this->addFlash('success', 'test_email_success');
                    } else {
                        $this->addFlash('error', 'test_email_error');
                    }

                    // Dump Send Log
                    $mailLogger->dump();
                }
            } else {
                // Save Config
                $cm->saveConfig($form);

                // Flash Message
                $this->addFlash('success', 'changes_saved');

                // Refresh Page
                return $this->redirectToRoute('admin_settings_email');
            }
        }

        // Render Page
        return $this->render('Admin/Settings/index.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'settings_email',
            'page_description' => 'settings_email_desc',
        ]);
    }

    /**
     * Account Settings.
     *
     * @IsGranted("ROLE_SETTINGS_USER")
     * @Route(name="settings_user", path="/settings/user")
     *
     * @return RedirectResponse|Response
     */
    public function user(Request $request)
    {
        // Get Config Manager
        $cm = new ConfigManager($this->getDoctrine()->getManager(), $this->get('parameter_bag'), 'settings_user');

        // Create Form
        $form = $this->createForm(UserForm::class, $cm->getAll(), ['router' => $this->get('router')]);

        // Handle Request
        $form->handleRequest($request);

        // Submit & Valid Form
        if ($form->isSubmitted() && $form->isValid()) {
            // Save Config
            $cm->saveConfig($form);

            // Flash Message
            $this->addFlash('success', 'changes_saved');

            // Refresh Page
            return $this->redirectToRoute('admin_settings_user');
        }

        // Render Page
        return $this->render('Admin/Settings/index.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'settings_user',
            'page_description' => 'settings_user_desc',
        ]);
    }

    /**
     * Media Settings.
     *
     * @IsGranted("ROLE_SETTINGS_MEDIA")
     * @Route(name="settings_media", path="/settings/media")
     *
     * @return RedirectResponse|Response
     */
    public function media(Request $request)
    {
        // Get Config Manager
        $cm = new ConfigManager($this->getDoctrine()->getManager(), $this->get('parameter_bag'), 'settings_media');

        // Create Form
        $form = $this->createForm(MediaForm::class, $cm->getAll());

        // Handle Request
        $form->handleRequest($request);

        // Submit & Valid Form
        if ($form->isSubmitted() && $form->isValid()) {
            // Save Config
            $cm->saveConfig($form);

            // Flash Message
            $this->addFlash('success', 'changes_saved');

            // Refresh Page
            return $this->redirectToRoute('admin_settings_media');
        }

        // Render Page
        return $this->render('Admin/Settings/index.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'settings_media',
            'page_description' => 'settings_media_desc',
        ]);
    }

    /**
     * Core Settings.
     *
     * @IsGranted("ROLE_SETTINGS_CORE")
     * @Route(name="settings_core", path="/settings/core")
     *
     * @return RedirectResponse|Response
     */
    public function core(Request $request)
    {
        // Get Config Manager
        $cm = new ConfigManager($this->getDoctrine()->getManager(), $this->get('parameter_bag'), 'settings_core');

        // Create Form
        $form = $this->createForm(CoreForm::class, $cm->getAll());

        // Handle Request
        $form->handleRequest($request);

        // Submit & Valid Form
        if ($form->isSubmitted() && $form->isValid()) {
            // Save Config
            $cm->saveConfig($form);

            // Flash Message
            $this->addFlash('success', 'changes_saved');

            // Refresh Page
            return $this->redirectToRoute('admin_settings_core');
        }

        // Render Page
        return $this->render('Admin/Settings/index.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'settings_core',
            'page_description' => 'settings_core_desc',
        ]);
    }

    /**
     * Clear Symfony Cache.
     *
     * @IsGranted("ROLE_SETTINGS_ROUTING")
     * @Route(name="settings_clearcache", path="/refresh/cache")
     */
    public function clearCache(): JsonResponse
    {
        // Create Process
        $process = Process::fromShellCommandline(sprintf('bin/console cache:clear --env=%s', $this->getParameter('kernel.environment')));

        // Run
        $process
            ->setWorkingDirectory($this->getParameter('kernel.project_dir'))
            ->run();

        if ($process->isSuccessful()) {
            return $this->json(['status' => 'successful']);
        }

        return $this->json(['status' => 'error'], 400);
    }
}
