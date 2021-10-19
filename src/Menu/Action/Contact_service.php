<?php

/**
 * This file is part of the pdAdmin package.
 *
 * @package     pd-admin
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-admin
 */

namespace App\Menu\Action;

use Pd\MenuBundle\Builder\ItemInterface;
use Pd\MenuBundle\Builder\Menu;

/**
 * Contact Actions.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class Contact_service extends Menu
{
    public function createMenu(array $options = []): ItemInterface
    {

        // Create Root Menu
        $menu = $this->createRoot('contact_service_action_menu');

        // Add Menu Items
        $menu
            ->addChild('admin_contact_service_delete', 1)
            ->setLabel('delete')
            ->setRoute('admin_contact_service_delete', ['id' =>$options['contact_service']["cs_id"]])
            ->setRoles(['ROLE_CONTACTSERVICE_DELETE'])
            ->setExtra('label_icon', 'delete')
            ->setLinkAttr([
                'class' => 'text-danger',
                'data-tooltip' => '',
                'title' => 'delete',
                'data-modal' => 'confirm',
            ])
            ->setLabelAttr(['class' => 'hidden'])


            ->addChildParent('admin_contact_service_edit_score', 1)
            ->setLabel('edit score')
            ->setRoute('admin_contact_service_edit_score', ['id' => $options['contact_service']["cs_id"]])
            ->setRoles(['ROLE_CONTACTSERVICE_EDITSCORE'])
            ->setExtra('label_icon', 'touch_app')
            ->setLinkAttr([
                'data-tooltip' => '',
                'title' => 'Modfier',
            ])
            ->setLabelAttr(['class' => 'hidden']);



        return $menu;
    }
}
