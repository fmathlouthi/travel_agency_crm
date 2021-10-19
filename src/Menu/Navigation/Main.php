<?php

/**
 * This file is part of the pdAdmin package.
 *
 * @package     pd-admin
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-admin
 */

namespace App\Menu\Navigation;

use Pd\MenuBundle\Builder\ItemInterface;
use Pd\MenuBundle\Builder\Menu;

/**
 * Main Navigation.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class Main extends Menu
{
    public function createMenu(array $options = []): ItemInterface
    {
        // Create ROOT Menu
        $menu = $this->createRoot('main_menu', true);

        // Create Dashboard
        $menu->addChild('nav_dashboard', 1)
            ->setLabel('nav_dashboard')
            ->setRoute('admin_dashboard')
            ->setRoles(['ROLE_ADMIN'])
            ->setExtra('label_icon', 'home');

        // Create Account Section


        $menu
            ->addChild('nav_account', 20)
            ->setLabel('nav_account')
            ->setRoute('admin_account_list')
            ->setRoles(['ROLE_ACCOUNT_LIST'])
            ->setExtra('label_icon', 'people')
            // Account List
            ->addChild('nav_admin_account', 10)
            ->setLabel('nav_admin_account')
            ->setRoute('admin_account_list')
            ->setRoles(['ROLE_ACCOUNT_LIST'])

            // Group List
            ->addChildParent('nav_group', 30)
            ->setLabel('nav_group')
            ->setRoute('admin_account_group_list')
            ->setRoles(['ROLE_GROUP_LIST']);

        $menu
            ->addChild('nav_user_account', 20)
            ->setLabel('nav_user_account')
            ->setRoute('admin_user_account_list')
            ->setRoles(['ROLE_ACCOUNT_LIST'])

            ->setExtra('label_icon', 'how_to_reg');


       //create contact section
        $menu
            ->addChild('contacts', 40)
            ->setLabel('contacts')
            ->setRoute('admin_contact_index')
            ->setExtra('label_icon', 'account_circle')
            ->setRoles(['ROLE_CONTACT_LIST'])
         ///
           ->addChild('contacts', 10)
           ->setLabel('contacts')
           ->setRoute('admin_contact_index')
           ->setRoles(['ROLE_CONTACT_LIST'])
        // services List
           ->addChildParent('lc', 20)
           ->setLabel('loyalty_cards')
           ->setRoute('admin_loyalty_cards_index')
           ->setRoles(['ROLE_LC_LIST'])
            ->addChildParent('reclamation', 20)
            ->setLabel('reclamation')
            ->setRoute('admin_reclamation_index')
            ->setRoles(['ROLE_RECLAMATION_LIST']);

//contact service section
        $menu
            ->addChild('Contact_service', 50)
            ->setLabel('contact services')
            ->setRoute('admin_contact_service_index')

            ->setExtra('label_icon', 'local_library')
            ->setRoles(['ROLE_CONTACTSERVICE_LIST'])
            // contact services List
            ->addChild('contact_service', 10)
            ->setLabel('contact services')
            ->setRoute('admin_contact_service_index')
            ->setRoles(['ROLE_CONTACTSERVICE_LIST'])
            // services List
            ->addChildParent('services', 20)
            ->setLabel('services')
            ->setRoute('admin_service_index')
            ->setRoles(['ROLE_SERVICE_LIST'])
        // promtion List
            ->addChildParent('Promotion', 20)
            ->setLabel('Promotion')
            ->setRoute('admin_promotion_index')
            ->setRoles(['ROLE_PROMOTION_LIST']);

        // Create Settings Section
        $menu
            ->addChild('nav_config', 60)
            ->setLabel('nav_config')
            ->setRoute('admin_mail_list')
            ->setExtra('label_icon', 'settings')
            ->setRoles(['ROLE_SETTINGS'])
           ;

        return $menu;
    }
}
