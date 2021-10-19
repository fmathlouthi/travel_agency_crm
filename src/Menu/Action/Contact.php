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
class Contact extends Menu
{
    public function createMenu(array $options = []): ItemInterface
    {


        // Create Root Menu
        $menu = $this->createRoot('contact_action_menu');

        // Add Menu Items
        $menu
            ->addChild('admin_contact_delete', 1)
            ->setLabel('delete')
            ->setRoute('admin_contact_delete', ['contact' => $options['contact']['c_id']])
            ->setRoles(['ROLE_CONTACT_DELETE'])
            ->setExtra('label_icon', 'delete')
            ->setLinkAttr([
                'class' => 'text-danger',
                'data-tooltip' => '',
                'title' => 'delete',
                'data-modal' => 'confirm',
            ])
            ->setLabelAttr(['class' => 'hidden'])


            ->addChildParent('admin_contact_edit', 1)
            ->setLabel('edit')
            ->setRoute('admin_contact_edit', ['contact' => $options['contact']['c_id']])
            ->setRoles(['ROLE_CONTACT_EDIT'])
            ->setExtra('label_icon', 'mode_edit')
            ->setLinkAttr([
                'data-tooltip' => '',
                'title' => 'Modfier',
            ])
            ->setLabelAttr(['class' => 'hidden'])


        ->addChildParent('admin_export_contact', 1)
        ->setLabel('export_contact')
        ->setRoute('admin_export_contact', ['contact' => $options['contact']['c_id']])
        ->setRoles(['ROLE_CONTACT_EXPORT'])
        ->setExtra('label_icon', 'cloud_download')
        ->setLinkAttr([
             'data-tooltip' => '',
            'title' => 'export',
        ])
        ->setLabelAttr(['class' => 'hidden']);


        return $menu;
    }
}
